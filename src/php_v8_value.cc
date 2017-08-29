/*
 * This file is part of the pinepain/php-v8 PHP extension.
 *
 * Copyright (c) 2015-2017 Bogdan Padalko <pinepain@gmail.com>
 *
 * Licensed under the MIT license: http://opensource.org/licenses/MIT
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source or visit
 * http://opensource.org/licenses/MIT
 */

#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include "php_v8_value.h"
#include "php_v8_exceptions.h"

/* begin of type listing */
#include "php_v8_date.h"
#include "php_v8_boolean_object.h"
#include "php_v8_number_object.h"
#include "php_v8_string_object.h"
#include "php_v8_symbol_object.h"
#include "php_v8_regexp.h"
#include "php_v8_function.h"
#include "php_v8_array.h"
#include "php_v8_map.h"
#include "php_v8_set.h"
#include "php_v8_object.h"

#include "php_v8_null.h"
#include "php_v8_boolean.h"
#include "php_v8_symbol.h"
#include "php_v8_string.h"
#include "php_v8_int32.h"
#include "php_v8_uint32.h"
#include "php_v8_integer.h"
#include "php_v8_number.h"
#include "php_v8_undefined.h"
/* end of type listing */

#include "php_v8_data.h"
#include "php_v8_isolate.h"
#include "php_v8_context.h"
#include "php_v8.h"

zend_class_entry *php_v8_value_class_entry;
#define this_ce php_v8_value_class_entry

static zend_object_handlers php_v8_value_object_handlers;


static void php_v8_value_weak_callback(const v8::WeakCallbackInfo<v8::Persistent<v8::Value>>& data) {
    v8::Isolate *isolate = data.GetIsolate();
    php_v8_isolate_t *php_v8_isolate = PHP_V8_ISOLATE_FETCH_REFERENCE(isolate);

    phpv8::PersistentData *persistent_data = php_v8_isolate->weak_values->get(data.GetParameter());

    if (persistent_data != nullptr) {
        // Tell v8 that we release external allocated memory
        php_v8_debug_external_mem("Free allocated external memory (value: %p): -%" PRId64 "\n", persistent_data, persistent_data->getTotalSize())
        isolate->AdjustAmountOfExternalAllocatedMemory(-persistent_data->getTotalSize());
        php_v8_isolate->weak_values->remove(data.GetParameter());
    }

    data.GetParameter()->Reset();
    delete data.GetParameter();

    // Tell v8 that we release external allocated memory
    isolate->AdjustAmountOfExternalAllocatedMemory(-1024 * 1024 * 1024);
}

static void php_v8_value_make_weak(php_v8_value_t *php_v8_value) {
    // TODO: maybe week: if it already week, if has no isolate, if no callbacks or empty callbacks
    assert(!php_v8_value->is_weak);

    php_v8_value->php_v8_isolate->weak_values->add(php_v8_value->persistent, php_v8_value->persistent_data);

    php_v8_value->is_weak = true;
    php_v8_value->persistent->SetWeak(php_v8_value->persistent, php_v8_value_weak_callback, v8::WeakCallbackType::kParameter);

    // Tell v8 that we allocated external memory
    php_v8_debug_external_mem("Allocate external memory (value: %p):  %" PRId64 "\n", php_v8_value->persistent_data, php_v8_value->persistent_data->getTotalSize())
    php_v8_value->php_v8_isolate->isolate->AdjustAmountOfExternalAllocatedMemory(php_v8_value->persistent_data->getTotalSize());
}

static HashTable * php_v8_value_gc(zval *object, zval **table, int *n) {
    PHP_V8_VALUE_FETCH_INTO(object, php_v8_value);

    php_v8_callbacks_gc(php_v8_value->persistent_data, &php_v8_value->gc_data, &php_v8_value->gc_data_count, table, n);

    if(!Z_ISUNDEF(php_v8_value->exception)) {
        *n = *n + 1;

        if (php_v8_value->gc_data_count < *n) {
            php_v8_value->gc_data = (zval *)safe_erealloc(php_v8_value->gc_data, *n, sizeof(zval), 0);
        }

        ZVAL_COPY_VALUE(&php_v8_value->gc_data[*n-1], &php_v8_value->exception);
    }

    return zend_std_get_properties(object);
}

static void php_v8_value_free(zend_object *object) {
    php_v8_value_t *php_v8_value = php_v8_value_fetch_object(object);

    // TODO: check whether we have valid isolate?
    if (php_v8_value->php_v8_isolate && php_v8_value->persistent && !php_v8_value->persistent->IsEmpty()) {
        PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);

        // TODO: in general, this makes sense only for objects
        v8::Local<v8::Value> local_value = php_v8_value_get_local(php_v8_value);

        if (local_value->IsObject()) {
            // TODO: at this point we SHOULD drop link to complete object and replace it with link to persistent handler and callbacks

            /* Here we lose reference to persistent handler and callbacks. While in most cases this should be
             * rare case, it may lead to allocated memory bloating, so it may be a good idea to store proper reference
             */
            php_v8_object_delete_self_ptr(php_v8_value, v8::Local<v8::Object>::Cast(local_value));
        }
    }

    if (!Z_ISUNDEF(php_v8_value->exception)) {
        zval_ptr_dtor(&php_v8_value->exception);
        ZVAL_UNDEF(&php_v8_value->exception);
    }

    if (php_v8_value->gc_data) {
        efree(php_v8_value->gc_data);
    }

    // TODO: can we un-make weak in case of CG(unclean_shutdown)?


    // TODO: making weak makes sense for objects only
    if (PHP_V8_IS_UP_AND_RUNNING() && php_v8_value->persistent_data && !php_v8_value->persistent_data->empty()) {
        php_v8_value_make_weak(php_v8_value); // TODO: refactor logic for make weak to include checking whether it can be weak -> maybe_make_weak
    }

    // NOTE: is weak check can be made in this way:
    //if (!php_v8_value->persistent || !php_v8_value->persistent->IsWeak()) {
    if (!php_v8_value->is_weak) {
        if (php_v8_value->persistent_data) {
            delete php_v8_value->persistent_data;
            php_v8_value->persistent_data = NULL;
        }

        if (php_v8_value->persistent) {
            if (PHP_V8_IS_UP_AND_RUNNING() && PHP_V8_ISOLATE_HAS_VALID_HANDLE(php_v8_value)) {
                php_v8_value->persistent->Reset();
            }

            delete php_v8_value->persistent;
        }
    }

    zend_object_std_dtor(&php_v8_value->std);
}

static zend_object * php_v8_value_ctor(zend_class_entry *ce) {

    php_v8_value_t *php_v8_value;

    php_v8_value = (php_v8_value_t *) ecalloc(1, sizeof(php_v8_value_t) + zend_object_properties_size(ce));

    zend_object_std_init(&php_v8_value->std, ce);
    object_properties_init(&php_v8_value->std, ce);

    php_v8_value->persistent = new v8::Persistent<v8::Value>();
    php_v8_value->persistent_data = new phpv8::PersistentData();

    php_v8_value->std.handlers = &php_v8_value_object_handlers;

    return &php_v8_value->std;
}


zend_class_entry *php_v8_get_class_entry_from_value(v8::Local<v8::Value> value) {
    assert(!value.IsEmpty());

    if (value->IsObject()) {
        // working with object

        if (value->IsFunction()) {
            return php_v8_function_class_entry;
        }

        if (value->IsArray()) {
            return php_v8_array_class_entry;
        }

        /* TODO: arguments are array, so they caught up in IsArray() case */
        /*
        if (value->IsArgumentsObject()) {
            // special case, array that has pre-set properties, see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Functions/arguments for details
            return php_v8_array_class_entry;
        }
        */

        if (value->IsDate()) {
            return php_v8_date_class_entry;
        }

        if (value->IsBooleanObject()) {
            return php_v8_boolean_object_class_entry;
        }

        if (value->IsNumberObject()) {
            return php_v8_number_object_class_entry;
        }

        if (value->IsStringObject()) {
            return php_v8_string_object_class_entry;
        }

        if (value->IsSymbolObject()) {
            return php_v8_symbol_object_class_entry;
        }

        if (value->IsRegExp()) {
            return php_v8_regexp_class_entry;
        }

        /*
        if (value->IsNativeError()) {
            // special case, native errors are always objects
            return php_v8_object_class_entry;
        }
        */

        if (value->IsMap()) {
            return php_v8_map_class_entry;
        }

        if (value->IsSet()) {
            return php_v8_set_class_entry;
        }

        // anything else will be just an object
        return php_v8_object_class_entry;
    }

    // working with scalars

    if (value->IsUndefined()) {
        return php_v8_undefined_class_entry;
    }

    if (value->IsNull()) {
        return php_v8_null_class_entry;
    }

    if (value->IsBoolean()) {
        return php_v8_boolean_class_entry;
    }

    if (value->IsString()) {
        return php_v8_string_class_entry;
    }

    if (value->IsSymbol()) {
        return php_v8_symbol_class_entry;
    }

    if (value->IsNumber()) {
        if (value->IsInt32()) {
            return php_v8_int32_class_entry;
        }

        if (value->IsUint32()) {
            return php_v8_uint32_class_entry;
        }

        return php_v8_number_class_entry;
    }

    return php_v8_value_class_entry;
}

php_v8_value_t *php_v8_create_value(zval *return_value, v8::Local<v8::Value> local_value, php_v8_isolate_t *php_v8_isolate) {
    zval isolate_zv;
    zval context_zv;
    assert(!local_value.IsEmpty());

    object_init_ex(return_value, php_v8_get_class_entry_from_value(local_value));
    PHP_V8_VALUE_FETCH_INTO(return_value, return_php_v8_value);

    ZVAL_OBJ(&isolate_zv, &php_v8_isolate->std);
    PHP_V8_VALUE_STORE_ISOLATE(return_value, &isolate_zv);
    PHP_V8_STORE_POINTER_TO_ISOLATE(return_php_v8_value, php_v8_isolate);

    if (local_value->IsObject()) {
        assert(php_v8_isolate->isolate->InContext());

        php_v8_context_t *php_v8_context = php_v8_context_get_reference(php_v8_isolate->isolate->GetEnteredContext());

        ZVAL_OBJ(&context_zv, &php_v8_context->std);
        PHP_V8_OBJECT_STORE_CONTEXT(return_value, &context_zv);
        PHP_V8_STORE_POINTER_TO_CONTEXT(return_php_v8_value, php_v8_context);

        php_v8_object_store_self_ptr(return_php_v8_value, v8::Local<v8::Object>::Cast(local_value));
    }

    return_php_v8_value->persistent->Reset(php_v8_isolate->isolate, local_value);

    return return_php_v8_value;
}

php_v8_value_t *php_v8_get_or_create_value(zval *return_value, v8::Local<v8::Value> local_value, php_v8_isolate_t *php_v8_isolate) {
    assert(!local_value.IsEmpty());

    if (local_value->IsObject()) {
        assert(php_v8_isolate->isolate->InContext());

        php_v8_value_t *data = php_v8_object_get_self_ptr(php_v8_isolate, v8::Local<v8::Object>::Cast(local_value));

        if (data) {
            ZVAL_OBJ(return_value, &data->std);
            Z_ADDREF_P(return_value);
            return data;
        }
    }

    return php_v8_create_value(return_value, local_value, php_v8_isolate);
}


static PHP_METHOD(Value, getIsolate) {
    zval rv;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);

    RETVAL_ZVAL(PHP_V8_VALUE_READ_ISOLATE(getThis()), 1, 0);
}


/* -----------------------------------------------------------------------
          v8::Value::Is* methods bindings
   ----------------------------------------------------------------------- */

#define PHP_V8_VALUE_IS_METHOD(classname, name)                             \
    PHP_METHOD(classname, is##name) {                                           \
        if (zend_parse_parameters_none() == FAILURE) {                      \
            return;                                                         \
        }                                                                   \
                                                                            \
    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);                 \
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);                              \
                                                                            \
    RETURN_BOOL(php_v8_value_get_local(php_v8_value)->Is##name());   \
}                                                                           \

static PHP_V8_VALUE_IS_METHOD(Value, Undefined)
static PHP_V8_VALUE_IS_METHOD(Value, Null)
static PHP_V8_VALUE_IS_METHOD(Value, NullOrUndefined)
static PHP_V8_VALUE_IS_METHOD(Value, True)
static PHP_V8_VALUE_IS_METHOD(Value, False)
static PHP_V8_VALUE_IS_METHOD(Value, Name)
static PHP_V8_VALUE_IS_METHOD(Value, String)
static PHP_V8_VALUE_IS_METHOD(Value, Symbol)
static PHP_V8_VALUE_IS_METHOD(Value, Function)
static PHP_V8_VALUE_IS_METHOD(Value, Array)
static PHP_V8_VALUE_IS_METHOD(Value, Object)
static PHP_V8_VALUE_IS_METHOD(Value, Boolean)
static PHP_V8_VALUE_IS_METHOD(Value, Number)
static PHP_V8_VALUE_IS_METHOD(Value, Int32)
static PHP_V8_VALUE_IS_METHOD(Value, Uint32)
static PHP_V8_VALUE_IS_METHOD(Value, Date)
static PHP_V8_VALUE_IS_METHOD(Value, ArgumentsObject)
static PHP_V8_VALUE_IS_METHOD(Value, BooleanObject)
static PHP_V8_VALUE_IS_METHOD(Value, NumberObject)
static PHP_V8_VALUE_IS_METHOD(Value, StringObject)
static PHP_V8_VALUE_IS_METHOD(Value, SymbolObject)

static PHP_METHOD(Value, isNativeError) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);

    // NativeError is always object (see v8 sources)
    if (!php_v8_value_get_local(php_v8_value)->IsObject()) {
        RETURN_FALSE;
    }

    v8::Local<v8::Object> local = php_v8_value_get_local_as<v8::Object>(php_v8_value);

    // We enter object's context, without it IsNativeError() causes segfault
    v8::Local<v8::Context> context = local->CreationContext();
    PHP_V8_CONTEXT_ENTER(context);

    RETURN_BOOL(local->IsNativeError());
}

static PHP_V8_VALUE_IS_METHOD(Value, RegExp)

static PHP_V8_VALUE_IS_METHOD(Value, AsyncFunction)
static PHP_V8_VALUE_IS_METHOD(Value, GeneratorFunction)
static PHP_V8_VALUE_IS_METHOD(Value, GeneratorObject)
static PHP_V8_VALUE_IS_METHOD(Value, Promise)
static PHP_V8_VALUE_IS_METHOD(Value, Map)
static PHP_V8_VALUE_IS_METHOD(Value, Set)
static PHP_V8_VALUE_IS_METHOD(Value, MapIterator)
static PHP_V8_VALUE_IS_METHOD(Value, SetIterator)
static PHP_V8_VALUE_IS_METHOD(Value, WeakMap)
static PHP_V8_VALUE_IS_METHOD(Value, WeakSet)
static PHP_V8_VALUE_IS_METHOD(Value, ArrayBuffer)
static PHP_V8_VALUE_IS_METHOD(Value, ArrayBufferView)
static PHP_V8_VALUE_IS_METHOD(Value, TypedArray)
static PHP_V8_VALUE_IS_METHOD(Value, Uint8Array)
static PHP_V8_VALUE_IS_METHOD(Value, Uint8ClampedArray)
static PHP_V8_VALUE_IS_METHOD(Value, Int8Array)
static PHP_V8_VALUE_IS_METHOD(Value, Uint16Array)
static PHP_V8_VALUE_IS_METHOD(Value, Int16Array)
static PHP_V8_VALUE_IS_METHOD(Value, Uint32Array)
static PHP_V8_VALUE_IS_METHOD(Value, Int32Array)
static PHP_V8_VALUE_IS_METHOD(Value, Float32Array)
static PHP_V8_VALUE_IS_METHOD(Value, Float64Array)
static PHP_V8_VALUE_IS_METHOD(Value, DataView)
static PHP_V8_VALUE_IS_METHOD(Value, SharedArrayBuffer)
static PHP_V8_VALUE_IS_METHOD(Value, Proxy)
//static PHP_V8_VALUE_IS_METHOD(Value, WebAssemblyCompiledModule) // Experimental


/* -----------------------------------------------------------------------
          Converters from v8::Value to high-level v8::Value's children
   ----------------------------------------------------------------------- */

static PHP_METHOD(Value, toBoolean) {
    zval *php_v8_context_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o", &php_v8_context_zv) == FAILURE) {
        return;
    }

    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);
    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_context);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    PHP_V8_TRY_CATCH(isolate);
    PHP_V8_INIT_ISOLATE_LIMITS_ON_CONTEXT(php_v8_context);

    v8::MaybeLocal<v8::Boolean> maybe_local = php_v8_value_get_local(php_v8_value)->ToBoolean(context);

    PHP_V8_MAYBE_CATCH(php_v8_context, try_catch);
    PHP_V8_THROW_EXCEPTION_WHEN_EMPTY(maybe_local, "Failed to convert");

    v8::Local<v8::Boolean> local_value = maybe_local.ToLocalChecked();

    php_v8_get_or_create_value(return_value, local_value, php_v8_context->php_v8_isolate);
}

static PHP_METHOD(Value, toNumber) {
    zval *php_v8_context_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o", &php_v8_context_zv) == FAILURE) {
        return;
    }

    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);
    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_context);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    PHP_V8_TRY_CATCH(isolate);
    PHP_V8_INIT_ISOLATE_LIMITS_ON_CONTEXT(php_v8_context);

    v8::MaybeLocal<v8::Number> maybe_local = php_v8_value_get_local(php_v8_value)->ToNumber(context);

    PHP_V8_MAYBE_CATCH(php_v8_context, try_catch);
    PHP_V8_THROW_EXCEPTION_WHEN_EMPTY(maybe_local, "Failed to convert");

    v8::Local<v8::Number> local_value = maybe_local.ToLocalChecked();

    php_v8_get_or_create_value(return_value, local_value, php_v8_context->php_v8_isolate);
}

static PHP_METHOD(Value, toString) {
    zval *php_v8_context_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o", &php_v8_context_zv) == FAILURE) {
        return;
    }

    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);
    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_context);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    PHP_V8_TRY_CATCH(isolate);
    PHP_V8_INIT_ISOLATE_LIMITS_ON_CONTEXT(php_v8_context);

    v8::MaybeLocal<v8::String> maybe_local = php_v8_value_get_local(php_v8_value)->ToString(context);

    PHP_V8_MAYBE_CATCH(php_v8_context, try_catch);
    PHP_V8_THROW_EXCEPTION_WHEN_EMPTY(maybe_local, "Failed to convert");

    v8::Local<v8::String> local_value = maybe_local.ToLocalChecked();

    php_v8_get_or_create_value(return_value, local_value, php_v8_context->php_v8_isolate);
}

static PHP_METHOD(Value, toDetailString) {
    zval *php_v8_context_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o", &php_v8_context_zv) == FAILURE) {
        return;
    }

    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);
    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_context);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    PHP_V8_TRY_CATCH(isolate);
    PHP_V8_INIT_ISOLATE_LIMITS_ON_CONTEXT(php_v8_context);

    v8::MaybeLocal<v8::String> maybe_local = php_v8_value_get_local(php_v8_value)->ToDetailString(context);

    PHP_V8_MAYBE_CATCH(php_v8_context, try_catch);
    PHP_V8_THROW_EXCEPTION_WHEN_EMPTY(maybe_local, "Failed to convert");

    v8::Local<v8::String> local_value = maybe_local.ToLocalChecked();

    php_v8_get_or_create_value(return_value, local_value, php_v8_context->php_v8_isolate);
}

static PHP_METHOD(Value, toObject) {
    zval *php_v8_context_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o", &php_v8_context_zv) == FAILURE) {
        return;
    }

    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);
    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_context);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    PHP_V8_TRY_CATCH(isolate);
    PHP_V8_INIT_ISOLATE_LIMITS_ON_CONTEXT(php_v8_context);

    v8::MaybeLocal<v8::Object> maybe_local = php_v8_value_get_local(php_v8_value)->ToObject(context);

    PHP_V8_MAYBE_CATCH(php_v8_context, try_catch);
    PHP_V8_THROW_EXCEPTION_WHEN_EMPTY(maybe_local, "Failed to convert");

    v8::Local<v8::Object> local_value = maybe_local.ToLocalChecked();

    php_v8_get_or_create_value(return_value, local_value, php_v8_context->php_v8_isolate);
}

static PHP_METHOD(Value, toInteger) {
    zval *php_v8_context_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o", &php_v8_context_zv) == FAILURE) {
        return;
    }

    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);
    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_context);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    PHP_V8_TRY_CATCH(isolate);
    PHP_V8_INIT_ISOLATE_LIMITS_ON_CONTEXT(php_v8_context);

    v8::MaybeLocal<v8::Integer> maybe_local = php_v8_value_get_local(php_v8_value)->ToInteger(context);

    PHP_V8_MAYBE_CATCH(php_v8_context, try_catch);
    PHP_V8_THROW_EXCEPTION_WHEN_EMPTY(maybe_local, "Failed to convert");

    v8::Local<v8::Integer> local_value = maybe_local.ToLocalChecked();

    php_v8_get_or_create_value(return_value, local_value, php_v8_context->php_v8_isolate);
}

static PHP_METHOD(Value, toUint32) {
    zval *php_v8_context_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o", &php_v8_context_zv) == FAILURE) {
        return;
    }

    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);
    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_context);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    PHP_V8_TRY_CATCH(isolate);
    PHP_V8_INIT_ISOLATE_LIMITS_ON_CONTEXT(php_v8_context);

    v8::MaybeLocal<v8::Uint32> maybe_local = php_v8_value_get_local(php_v8_value)->ToUint32(context);

    PHP_V8_MAYBE_CATCH(php_v8_context, try_catch);
    PHP_V8_THROW_EXCEPTION_WHEN_EMPTY(maybe_local, "Failed to convert");

    v8::Local<v8::Uint32> local_value = maybe_local.ToLocalChecked();

    php_v8_get_or_create_value(return_value, local_value, php_v8_context->php_v8_isolate);
}

static PHP_METHOD(Value, toInt32) {
    zval *php_v8_context_zv;


    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o", &php_v8_context_zv) == FAILURE) {
        return;
    }

    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);
    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_context);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    PHP_V8_TRY_CATCH(isolate);
    PHP_V8_INIT_ISOLATE_LIMITS_ON_CONTEXT(php_v8_context);

    v8::MaybeLocal<v8::Int32> maybe_local = php_v8_value_get_local(php_v8_value)->ToInt32(context);

    PHP_V8_MAYBE_CATCH(php_v8_context, try_catch);
    PHP_V8_THROW_EXCEPTION_WHEN_EMPTY(maybe_local, "Failed to convert");

    v8::Local<v8::Int32> local_value = maybe_local.ToLocalChecked();

    php_v8_get_or_create_value(return_value, local_value, php_v8_context->php_v8_isolate);
}

static PHP_METHOD(Value, toArrayIndex) {
    zval *php_v8_context_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o", &php_v8_context_zv) == FAILURE) {
        return;
    }

    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);
    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_context);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    PHP_V8_TRY_CATCH(isolate);
    PHP_V8_INIT_ISOLATE_LIMITS_ON_CONTEXT(php_v8_context);

    v8::MaybeLocal<v8::Uint32> maybe_local = php_v8_value_get_local(php_v8_value)->ToArrayIndex(context);

    PHP_V8_MAYBE_CATCH(php_v8_context, try_catch);
    PHP_V8_THROW_EXCEPTION_WHEN_EMPTY(maybe_local, "Failed to convert");

    v8::Local<v8::Uint32> local_value = maybe_local.ToLocalChecked();

    php_v8_get_or_create_value(return_value, local_value, php_v8_context->php_v8_isolate);
}


/* -----------------------------------------------------------------------
          Converters from v8::Value to low-level primitives
   ----------------------------------------------------------------------- */


static PHP_METHOD(Value, booleanValue) {
    zval *php_v8_context_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o", &php_v8_context_zv) == FAILURE) {
        return;
    }

    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);
    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_context);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    v8::Maybe<bool> maybe = php_v8_value_get_local(php_v8_value)->BooleanValue(context);

    if (maybe.IsNothing()) {
        PHP_V8_THROW_EXCEPTION("Failed to convert");
        return;
    }

    RETVAL_BOOL(maybe.FromJust());
}

static PHP_METHOD(Value, numberValue) {
    zval *php_v8_context_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o", &php_v8_context_zv) == FAILURE) {
        return;
    }

    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);
    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_context);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    v8::Maybe<double> maybe = php_v8_value_get_local(php_v8_value)->NumberValue(context);

    PHP_V8_THROW_EXCEPTION_WHEN_NOTHING(maybe, "Failed to convert");

    RETVAL_DOUBLE(maybe.FromJust());
}

static PHP_METHOD(Value, integerValue) {
    zval *php_v8_context_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o", &php_v8_context_zv) == FAILURE) {
        return;
    }

    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);
    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_context);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    v8::Maybe<int64_t> maybe = php_v8_value_get_local(php_v8_value)->IntegerValue(context);

    PHP_V8_THROW_EXCEPTION_WHEN_NOTHING(maybe, "Failed to convert");

    RETVAL_DOUBLE((double) maybe.FromJust());
}

static PHP_METHOD(Value, uint32Value) {
    zval *php_v8_context_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o", &php_v8_context_zv) == FAILURE) {
        return;
    }

    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);
    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_context);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    v8::Maybe<uint32_t> maybe = php_v8_value_get_local(php_v8_value)->Uint32Value(context);

    PHP_V8_THROW_EXCEPTION_WHEN_NOTHING(maybe, "Failed to convert");

    RETVAL_LONG((zend_long) maybe.FromJust());
}

static PHP_METHOD(Value, int32Value) {
    zval *php_v8_context_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o", &php_v8_context_zv) == FAILURE) {
        return;
    }

    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);
    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_context);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    v8::Maybe<int32_t> maybe = php_v8_value_get_local(php_v8_value)->Int32Value(context);

    PHP_V8_THROW_EXCEPTION_WHEN_NOTHING(maybe, "Failed to convert");

    RETVAL_LONG((zend_long) maybe.FromJust());
}

/** JS == */

static PHP_METHOD(Value, equals) {
    zval *php_v8_context_zv;
    zval *php_v8_value_that_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "oo", &php_v8_context_zv, &php_v8_value_that_zv) == FAILURE) {
        return;
    }

    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);
    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);

    PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_value_that_zv, php_v8_value_that);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_context);
    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_value_that);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    v8::Maybe<bool> maybe = php_v8_value_get_local(php_v8_value)->Equals(context,php_v8_value_get_local(php_v8_value_that));

    if (maybe.IsNothing()) {
        PHP_V8_THROW_EXCEPTION("Failed to compare");
        return;
    }

    RETVAL_BOOL(maybe.FromJust());
}

static PHP_METHOD(Value, strictEquals) {
    zval *php_v8_value_that_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o", &php_v8_value_that_zv) == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);

    PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_value_that_zv, php_v8_value_that);
    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_value_that);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);

    bool val = php_v8_value_get_local(php_v8_value)->StrictEquals(php_v8_value_get_local(php_v8_value_that));

    RETVAL_BOOL(val);
}

static PHP_METHOD(Value, sameValue) {
    zval *php_v8_value_that_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o", &php_v8_value_that_zv) == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_value_that_zv, php_v8_value_that);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_value_that);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);

    bool val = php_v8_value_get_local(php_v8_value)->SameValue(php_v8_value_get_local(php_v8_value_that));

    RETVAL_BOOL(val);
}

static PHP_METHOD(Value, typeOf) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);

    v8::Local<v8::String> local_string = php_v8_value_get_local(php_v8_value)->TypeOf(isolate);

    PHP_V8_THROW_EXCEPTION_WHEN_EMPTY(local_string, "Failed to get type of value");

    php_v8_get_or_create_value(return_value, local_string, php_v8_value->php_v8_isolate);
}

static PHP_METHOD(Value, instanceOf) {
    zval *php_v8_context_zv;
    zval *php_v8_value_object_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "oo", &php_v8_context_zv, &php_v8_value_object_zv) == FAILURE) {
        return;
    }

    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);
    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_value_object_zv, php_v8_value_object);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_context);
    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_value_object);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    PHP_V8_TRY_CATCH(isolate);
    PHP_V8_INIT_ISOLATE_LIMITS_ON_CONTEXT(php_v8_context);

    v8::Maybe<bool> maybe_res = php_v8_value_get_local(php_v8_value)->InstanceOf(context, php_v8_value_get_local_as<v8::Object>(php_v8_value_object));

    PHP_V8_MAYBE_CATCH(php_v8_context, try_catch);
    PHP_V8_THROW_EXCEPTION_WHEN_NOTHING(maybe_res, "Failed to check");

    RETURN_BOOL(maybe_res.FromJust());
}


ZEND_BEGIN_ARG_INFO_EX(arginfo_value___construct, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 1)
                ZEND_ARG_OBJ_INFO(0, isolate, V8\\Isolate, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_value_getIsolate, ZEND_RETURN_VALUE, 0, V8\\Isolate, 0)
ZEND_END_ARG_INFO()


#define PHP_V8_VALUE_IS_METHOD_ARG_INFO(method) \
    PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_value_is##method, ZEND_RETURN_VALUE, 0, _IS_BOOL, 0) \
    ZEND_END_ARG_INFO() \

PHP_V8_VALUE_IS_METHOD_ARG_INFO(Undefined)
PHP_V8_VALUE_IS_METHOD_ARG_INFO(Null)
PHP_V8_VALUE_IS_METHOD_ARG_INFO(NullOrUndefined)
PHP_V8_VALUE_IS_METHOD_ARG_INFO(True)
PHP_V8_VALUE_IS_METHOD_ARG_INFO(False)
PHP_V8_VALUE_IS_METHOD_ARG_INFO(Name)
PHP_V8_VALUE_IS_METHOD_ARG_INFO(String)
PHP_V8_VALUE_IS_METHOD_ARG_INFO(Symbol)
PHP_V8_VALUE_IS_METHOD_ARG_INFO(Function)
PHP_V8_VALUE_IS_METHOD_ARG_INFO(Array)
PHP_V8_VALUE_IS_METHOD_ARG_INFO(Object)
PHP_V8_VALUE_IS_METHOD_ARG_INFO(Boolean)
PHP_V8_VALUE_IS_METHOD_ARG_INFO(Number)
PHP_V8_VALUE_IS_METHOD_ARG_INFO(Int32)
PHP_V8_VALUE_IS_METHOD_ARG_INFO(Uint32)
PHP_V8_VALUE_IS_METHOD_ARG_INFO(Date)
PHP_V8_VALUE_IS_METHOD_ARG_INFO(ArgumentsObject)
PHP_V8_VALUE_IS_METHOD_ARG_INFO(BooleanObject)
PHP_V8_VALUE_IS_METHOD_ARG_INFO(NumberObject)
PHP_V8_VALUE_IS_METHOD_ARG_INFO(StringObject)
PHP_V8_VALUE_IS_METHOD_ARG_INFO(SymbolObject)
PHP_V8_VALUE_IS_METHOD_ARG_INFO(NativeError)
PHP_V8_VALUE_IS_METHOD_ARG_INFO(RegExp)
PHP_V8_VALUE_IS_METHOD_ARG_INFO(AsyncFunction)
PHP_V8_VALUE_IS_METHOD_ARG_INFO(GeneratorFunction)
PHP_V8_VALUE_IS_METHOD_ARG_INFO(GeneratorObject)
PHP_V8_VALUE_IS_METHOD_ARG_INFO(Promise)
PHP_V8_VALUE_IS_METHOD_ARG_INFO(Map)
PHP_V8_VALUE_IS_METHOD_ARG_INFO(Set)
PHP_V8_VALUE_IS_METHOD_ARG_INFO(MapIterator)
PHP_V8_VALUE_IS_METHOD_ARG_INFO(SetIterator)
PHP_V8_VALUE_IS_METHOD_ARG_INFO(WeakMap)
PHP_V8_VALUE_IS_METHOD_ARG_INFO(WeakSet)
PHP_V8_VALUE_IS_METHOD_ARG_INFO(ArrayBuffer)
PHP_V8_VALUE_IS_METHOD_ARG_INFO(ArrayBufferView)
PHP_V8_VALUE_IS_METHOD_ARG_INFO(TypedArray)
PHP_V8_VALUE_IS_METHOD_ARG_INFO(Uint8Array)
PHP_V8_VALUE_IS_METHOD_ARG_INFO(Uint8ClampedArray)
PHP_V8_VALUE_IS_METHOD_ARG_INFO(Int8Array)
PHP_V8_VALUE_IS_METHOD_ARG_INFO(Uint16Array)
PHP_V8_VALUE_IS_METHOD_ARG_INFO(Int16Array)
PHP_V8_VALUE_IS_METHOD_ARG_INFO(Uint32Array)
PHP_V8_VALUE_IS_METHOD_ARG_INFO(Int32Array)
PHP_V8_VALUE_IS_METHOD_ARG_INFO(Float32Array)
PHP_V8_VALUE_IS_METHOD_ARG_INFO(Float64Array)
PHP_V8_VALUE_IS_METHOD_ARG_INFO(DataView)
PHP_V8_VALUE_IS_METHOD_ARG_INFO(SharedArrayBuffer)
PHP_V8_VALUE_IS_METHOD_ARG_INFO(Proxy)
//PHP_V8_VALUE_IS_METHOD_ARG_INFO(WebAssemblyCompiledModule)  // Experimental


ZEND_BEGIN_ARG_INFO_EX(arginfo_value_toBoolean, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 1)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_value_toNumber, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 1)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_value_toString, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 1)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_value_toDetailString, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 1)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_value_toObject, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 1)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_value_toInteger, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 1)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_value_toUint32, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 1)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_value_toInt32, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 1)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_value_toArrayIndex, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 1)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
ZEND_END_ARG_INFO()


ZEND_BEGIN_ARG_INFO_EX(arginfo_value_booleanValue, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 1)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_value_numberValue, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 1)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_value_integerValue, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 1)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_value_int32Value, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 1)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_value_uint32Value, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 1)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_value_equals, ZEND_RETURN_VALUE, 2, _IS_BOOL, 2)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_OBJ_INFO(0, that, V8\\Value, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_value_strictEquals, ZEND_RETURN_VALUE, 1, _IS_BOOL, 0)
                ZEND_ARG_OBJ_INFO(0, that, V8\\Value, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_value_sameValue, ZEND_RETURN_VALUE, 1, _IS_BOOL, 0)
                ZEND_ARG_OBJ_INFO(0, that, V8\\Value, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_value_typeOf, ZEND_RETURN_VALUE, 0, V8\\StringValue, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_value_instanceOf, ZEND_RETURN_VALUE, 2, _IS_BOOL, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_OBJ_INFO(0, object, V8\\ObjectValue, 0)
ZEND_END_ARG_INFO()


static const zend_function_entry php_v8_value_methods[] = {
//        PHP_ME(Value, __construct, arginfo_value___construct, ZEND_ACC_PRIVATE | ZEND_ACC_CTOR)

        PHP_ME(Value, getIsolate, arginfo_value_getIsolate, ZEND_ACC_PUBLIC)

        PHP_ME(Value, isUndefined,                    arginfo_value_isUndefined,                   ZEND_ACC_PUBLIC)
        PHP_ME(Value, isNull,                         arginfo_value_isNull,                        ZEND_ACC_PUBLIC)
        PHP_ME(Value, isNullOrUndefined,              arginfo_value_isNullOrUndefined,             ZEND_ACC_PUBLIC)
        PHP_ME(Value, isTrue,                         arginfo_value_isTrue,                        ZEND_ACC_PUBLIC)
        PHP_ME(Value, isFalse,                        arginfo_value_isFalse,                       ZEND_ACC_PUBLIC)
        PHP_ME(Value, isName,                         arginfo_value_isName,                        ZEND_ACC_PUBLIC)
        PHP_ME(Value, isString,                       arginfo_value_isString,                      ZEND_ACC_PUBLIC)
        PHP_ME(Value, isSymbol,                       arginfo_value_isSymbol,                      ZEND_ACC_PUBLIC)
        PHP_ME(Value, isFunction,                     arginfo_value_isFunction,                    ZEND_ACC_PUBLIC)
        PHP_ME(Value, isArray,                        arginfo_value_isArray,                       ZEND_ACC_PUBLIC)
        PHP_ME(Value, isObject,                       arginfo_value_isObject,                      ZEND_ACC_PUBLIC)
        PHP_ME(Value, isBoolean,                      arginfo_value_isBoolean,                     ZEND_ACC_PUBLIC)
        PHP_ME(Value, isNumber,                       arginfo_value_isNumber,                      ZEND_ACC_PUBLIC)
        PHP_ME(Value, isInt32,                        arginfo_value_isInt32,                       ZEND_ACC_PUBLIC)
        PHP_ME(Value, isUint32,                       arginfo_value_isUint32,                      ZEND_ACC_PUBLIC)
        PHP_ME(Value, isDate,                         arginfo_value_isDate,                        ZEND_ACC_PUBLIC)
        PHP_ME(Value, isArgumentsObject,              arginfo_value_isArgumentsObject,             ZEND_ACC_PUBLIC)
        PHP_ME(Value, isBooleanObject,                arginfo_value_isBooleanObject,               ZEND_ACC_PUBLIC)
        PHP_ME(Value, isNumberObject,                 arginfo_value_isNumberObject,                ZEND_ACC_PUBLIC)
        PHP_ME(Value, isStringObject,                 arginfo_value_isStringObject,                ZEND_ACC_PUBLIC)
        PHP_ME(Value, isSymbolObject,                 arginfo_value_isSymbolObject,                ZEND_ACC_PUBLIC)
        PHP_ME(Value, isNativeError,                  arginfo_value_isNativeError,                 ZEND_ACC_PUBLIC)
        PHP_ME(Value, isRegExp,                       arginfo_value_isRegExp,                      ZEND_ACC_PUBLIC)
        PHP_ME(Value, isAsyncFunction,                arginfo_value_isAsyncFunction,               ZEND_ACC_PUBLIC)
        PHP_ME(Value, isGeneratorFunction,            arginfo_value_isGeneratorFunction,           ZEND_ACC_PUBLIC)
        PHP_ME(Value, isGeneratorObject,              arginfo_value_isGeneratorObject,             ZEND_ACC_PUBLIC)
        PHP_ME(Value, isPromise,                      arginfo_value_isPromise,                     ZEND_ACC_PUBLIC)
        PHP_ME(Value, isMap,                          arginfo_value_isMap,                         ZEND_ACC_PUBLIC)
        PHP_ME(Value, isSet,                          arginfo_value_isSet,                         ZEND_ACC_PUBLIC)
        PHP_ME(Value, isMapIterator,                  arginfo_value_isMapIterator,                 ZEND_ACC_PUBLIC)
        PHP_ME(Value, isSetIterator,                  arginfo_value_isSetIterator,                 ZEND_ACC_PUBLIC)
        PHP_ME(Value, isWeakMap,                      arginfo_value_isWeakMap,                     ZEND_ACC_PUBLIC)
        PHP_ME(Value, isWeakSet,                      arginfo_value_isWeakSet,                     ZEND_ACC_PUBLIC)
        PHP_ME(Value, isArrayBuffer,                  arginfo_value_isArrayBuffer,                 ZEND_ACC_PUBLIC)
        PHP_ME(Value, isArrayBufferView,              arginfo_value_isArrayBufferView,             ZEND_ACC_PUBLIC)
        PHP_ME(Value, isTypedArray,                   arginfo_value_isTypedArray,                  ZEND_ACC_PUBLIC)
        PHP_ME(Value, isUint8Array,                   arginfo_value_isUint8Array,                  ZEND_ACC_PUBLIC)
        PHP_ME(Value, isUint8ClampedArray,            arginfo_value_isUint8ClampedArray,           ZEND_ACC_PUBLIC)
        PHP_ME(Value, isInt8Array,                    arginfo_value_isInt8Array,                   ZEND_ACC_PUBLIC)
        PHP_ME(Value, isUint16Array,                  arginfo_value_isUint16Array,                 ZEND_ACC_PUBLIC)
        PHP_ME(Value, isInt16Array,                   arginfo_value_isInt16Array,                  ZEND_ACC_PUBLIC)
        PHP_ME(Value, isUint32Array,                  arginfo_value_isUint32Array,                 ZEND_ACC_PUBLIC)
        PHP_ME(Value, isInt32Array,                   arginfo_value_isInt32Array,                  ZEND_ACC_PUBLIC)
        PHP_ME(Value, isFloat32Array,                 arginfo_value_isFloat32Array,                ZEND_ACC_PUBLIC)
        PHP_ME(Value, isFloat64Array,                 arginfo_value_isFloat64Array,                ZEND_ACC_PUBLIC)
        PHP_ME(Value, isDataView,                     arginfo_value_isDataView,                    ZEND_ACC_PUBLIC)
        PHP_ME(Value, isSharedArrayBuffer,            arginfo_value_isSharedArrayBuffer,           ZEND_ACC_PUBLIC)
        PHP_ME(Value, isProxy,                        arginfo_value_isProxy,                       ZEND_ACC_PUBLIC)
        //PHP_ME(Value, isWebAssemblyCompiledModule,    arginfo_value_isWebAssemblyCompiledModule,   ZEND_ACC_PUBLIC) // Experimental

        PHP_ME(Value, toBoolean,          arginfo_value_toBoolean,         ZEND_ACC_PUBLIC)
        PHP_ME(Value, toNumber,           arginfo_value_toNumber,          ZEND_ACC_PUBLIC)
        PHP_ME(Value, toString,           arginfo_value_toString,          ZEND_ACC_PUBLIC)
        PHP_ME(Value, toDetailString,     arginfo_value_toDetailString,    ZEND_ACC_PUBLIC)
        PHP_ME(Value, toObject,           arginfo_value_toObject,          ZEND_ACC_PUBLIC)
        PHP_ME(Value, toInteger,          arginfo_value_toInteger,         ZEND_ACC_PUBLIC)
        PHP_ME(Value, toUint32,           arginfo_value_toUint32,          ZEND_ACC_PUBLIC)
        PHP_ME(Value, toInt32,            arginfo_value_toInt32,           ZEND_ACC_PUBLIC)
        PHP_ME(Value, toArrayIndex,       arginfo_value_toArrayIndex,      ZEND_ACC_PUBLIC)

        PHP_ME(Value, booleanValue,       arginfo_value_booleanValue,      ZEND_ACC_PUBLIC)
        PHP_ME(Value, numberValue,        arginfo_value_numberValue,       ZEND_ACC_PUBLIC)
        PHP_ME(Value, integerValue,       arginfo_value_integerValue,      ZEND_ACC_PUBLIC)
        PHP_ME(Value, int32Value,         arginfo_value_int32Value,        ZEND_ACC_PUBLIC)
        PHP_ME(Value, uint32Value,        arginfo_value_uint32Value,       ZEND_ACC_PUBLIC)

        PHP_ME(Value, equals,             arginfo_value_equals,            ZEND_ACC_PUBLIC)
        PHP_ME(Value, strictEquals,       arginfo_value_strictEquals,      ZEND_ACC_PUBLIC)
        PHP_ME(Value, sameValue,          arginfo_value_sameValue,         ZEND_ACC_PUBLIC)
        PHP_ME(Value, typeOf,             arginfo_value_typeOf,            ZEND_ACC_PUBLIC)
        PHP_ME(Value, instanceOf,         arginfo_value_instanceOf,        ZEND_ACC_PUBLIC)

        PHP_FE_END
};


PHP_MINIT_FUNCTION (php_v8_value) {
    zend_class_entry ce;
    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS, "Value", php_v8_value_methods);
    this_ce = zend_register_internal_class_ex(&ce, php_v8_data_class_entry);
    this_ce->create_object = php_v8_value_ctor;
    this_ce->ce_flags |= ZEND_ACC_EXPLICIT_ABSTRACT_CLASS;

    zend_declare_property_null(this_ce, ZEND_STRL("isolate"), ZEND_ACC_PRIVATE);

    memcpy(&php_v8_value_object_handlers, zend_get_std_object_handlers(), sizeof(zend_object_handlers));

    php_v8_value_object_handlers.offset    = XtOffsetOf(php_v8_value_t, std);
    php_v8_value_object_handlers.free_obj  = php_v8_value_free;
    php_v8_value_object_handlers.get_gc    = php_v8_value_gc;
    php_v8_value_object_handlers.clone_obj = NULL;

    return SUCCESS;
}
