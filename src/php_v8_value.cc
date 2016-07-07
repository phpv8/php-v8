/*
  +----------------------------------------------------------------------+
  | This file is part of the pinepain/php-v8 PHP extension.              |
  |                                                                      |
  | Copyright (c) 2015-2016 Bogdan Padalko <pinepain@gmail.com>          |
  |                                                                      |
  | Licensed under the MIT license: http://opensource.org/licenses/MIT   |
  |                                                                      |
  | For the full copyright and license information, please view the      |
  | LICENSE file that was distributed with this source or visit          |
  | http://opensource.org/licenses/MIT                                   |
  +----------------------------------------------------------------------+
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
#include "php_v8_object.h"

#include "php_v8_null.h"
#include "php_v8_boolean.h"
#include "php_v8_symbol.h"
#include "php_v8_string.h"
#include "php_v8_int32.h"
#include "php_v8_uint32.h"
#include "php_v8_integer.h"
#include "php_v8_number.h"
/* end of type listing */

#include "php_v8_data.h"
#include "php_v8_isolate.h"
#include "php_v8_context.h"
#include "php_v8.h"

zend_class_entry *php_v8_value_class_entry;
#define this_ce php_v8_value_class_entry

static zend_object_handlers php_v8_value_object_handlers;

v8::Local<v8::Value> php_v8_value_get_value_local(v8::Isolate *isolate, php_v8_value_t *php_v8_value) {
    return v8::Local<v8::Value>::New(isolate, *php_v8_value->persistent);
};

php_v8_value_t *php_v8_value_fetch_object(zend_object *obj) {
    return (php_v8_value_t *)((char *)obj - XtOffsetOf(php_v8_value_t, std));
}

static void php_v8_value_weak_callback(const v8::WeakCallbackInfo<v8::Persistent<v8::Value>>& data) {
    v8::Isolate *isolate = data.GetIsolate();
    php_v8_isolate_t *php_v8_isolate = PHP_V8_ISOLATE_FETCH_REFERENCE(isolate);

    php_v8_callbacks_t *callbacks = (*php_v8_isolate->weak_values)[data.GetParameter()];
    php_v8_callbacks_cleanup(callbacks);
    php_v8_isolate->weak_values->erase(data.GetParameter());

    data.GetParameter()->Reset();

    delete callbacks;
    delete data.GetParameter();

    // Tell v8 that we release external allocated memory
    isolate->AdjustAmountOfExternalAllocatedMemory(-1024 * 1024 * 1024);
}

static void php_v8_value_make_weak(php_v8_value_t *php_v8_value) {
    // TODO: maybe week: if it already week, if has no isolate, if no callbacks or empty callbacks
    assert(!php_v8_value->is_weak);

    (*php_v8_value->php_v8_isolate->weak_values)[php_v8_value->persistent] = php_v8_value->callbacks;

    php_v8_value->is_weak = true;
    php_v8_value->persistent->SetWeak(php_v8_value->persistent, php_v8_value_weak_callback, v8::WeakCallbackType::kParameter);

    php_v8_value->php_v8_isolate->isolate->AdjustAmountOfExternalAllocatedMemory(1024 * 1024 * 1024);
}

static HashTable * php_v8_value_gc(zval *object, zval **table, int *n) {
    PHP_V8_VALUE_FETCH_INTO(object, php_v8_value);

    php_v8_callbacks_gc(php_v8_value->callbacks, &php_v8_value->gc_data, &php_v8_value->gc_data_count, table, n);

    return zend_std_get_properties(object);
}

static void php_v8_value_free(zend_object *object) {
    php_v8_value_t *php_v8_value = php_v8_value_fetch_object(object);

    // TODO: check whether we have valid isolate?
    if (php_v8_value->php_v8_isolate && php_v8_value->persistent && !php_v8_value->persistent->IsEmpty()) {
        PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);

        // TODO: in general, this makes sense only for objects
        v8::Local<v8::Value> local_value = php_v8_value_get_value_local(php_v8_value->php_v8_isolate->isolate,
                                                                        php_v8_value);

        if (local_value->IsObject()) {
            // TODO: at this point we SHOULD drop link to complete object and replace it with link to persistent handler and callbacks

            /* NOTE: here we lose reference to persistent handler and callbacks. While in most cases this should be
             *       rare case, it may lead to allocated memory bloating, so it may be a good idea to store proper reference
             */
            php_v8_object_delete_self_ptr(php_v8_value->php_v8_isolate->isolate, v8::Local<v8::Object>::Cast(local_value));
        }
    }

    if (!Z_ISUNDEF(php_v8_value->this_ptr)) {
        zval_ptr_dtor(&php_v8_value->this_ptr);
    }

    if (php_v8_value->gc_data) {
        efree(php_v8_value->gc_data);
    }

    // TODO: can we un-make weak in case of CG(unclean_shutdown)?


    // TODO: making weak makes sense for objects only
    if (!CG(unclean_shutdown) && php_v8_value->callbacks && !php_v8_value->callbacks->empty()) {
        php_v8_value_make_weak(php_v8_value); // TODO: refactor logic for make weak to include checking whether it can be weak -> maybe_make_weak
    }

    // NOTE: is weak check can be made in this way:
    //if (!php_v8_value->persistent || !php_v8_value->persistent->IsWeak()) {
    if (!php_v8_value->is_weak) {
        if (php_v8_value->callbacks) {
            php_v8_callbacks_cleanup(php_v8_value->callbacks);
            delete php_v8_value->callbacks;
        }

        if (php_v8_value->persistent) {
            if (PHP_V8_ISOLATE_HAS_VALID_HANDLE(php_v8_value)) {
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
    php_v8_value->callbacks = new php_v8_callbacks_t();

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

        // anything else will be just an object
        return php_v8_object_class_entry;
    }

    // working with scalars

    if (value->IsUndefined()) {
        return php_v8_value_class_entry;
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

    /* currently we ignore detectind v8::Number sub-types */

    /*
    if (value->IsUint32()) {
        return php_v8_uint32_class_entry;
    }

    if (value->IsInt32()) {
        return php_v8_int32_class_entry;
    }
    */

    if (value->IsNumber()) {
        return php_v8_number_class_entry;
    }

    return php_v8_value_class_entry;
}

php_v8_value_t *php_v8_create_value(zval *return_value, v8::Local<v8::Value> local_value, v8::Isolate *isolate) {
    assert(!local_value.IsEmpty());

    php_v8_isolate_t *php_v8_isolate = PHP_V8_ISOLATE_FETCH_REFERENCE(isolate);

    object_init_ex(return_value, php_v8_get_class_entry_from_value(local_value));
    PHP_V8_VALUE_FETCH_INTO(return_value, return_php_v8_value);
    PHP_V8_VALUE_STORE_ISOLATE(return_value, &php_v8_isolate->this_ptr);
    PHP_V8_STORE_POINTER_TO_ISOLATE(return_php_v8_value, php_v8_isolate);

    if (local_value->IsObject()) {
        assert(isolate->InContext());

        php_v8_context_t *php_v8_context = php_v8_context_get_reference(local_value.As<v8::Object>()->CreationContext());

        PHP_V8_OBJECT_STORE_CONTEXT(return_value, &php_v8_context->this_ptr);
        PHP_V8_STORE_POINTER_TO_CONTEXT(return_php_v8_value, php_v8_context);

        ZVAL_COPY_VALUE(&return_php_v8_value->this_ptr, return_value);
        php_v8_object_store_self_ptr(isolate, v8::Local<v8::Object>::Cast(local_value), return_php_v8_value);
    }

    return_php_v8_value->persistent->Reset(isolate, local_value);

    return return_php_v8_value;
}

php_v8_value_t *php_v8_get_or_create_value(zval *return_value, v8::Local<v8::Value> local_value, v8::Isolate *isolate) {
    assert(!local_value.IsEmpty());

    if (local_value->IsObject()) {
        assert(isolate->InContext());

        php_v8_value_t *data = php_v8_object_get_self_ptr(isolate, v8::Local<v8::Object>::Cast(local_value));

        if (data) {
            ZVAL_ZVAL(return_value, &data->this_ptr, 1, 0);
            return data;
        }
    }

    return php_v8_create_value(return_value, local_value, isolate);
}


static PHP_METHOD (V8Value, __construct) {
    zval *php_v8_isolate_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o", &php_v8_isolate_zv) == FAILURE) {
        return;
    }

    PHP_V8_VALUE_CONSTRUCT(getThis(), php_v8_isolate_zv, php_v8_isolate, php_v8_value);

    php_v8_value->persistent->Reset(isolate, v8::Undefined(isolate));
}

static PHP_METHOD(V8Value, GetIsolate) {
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

// TODO: maybe write some macro for Is* methods?

static PHP_METHOD(V8Value, IsUndefined) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);

    RETURN_BOOL(php_v8_value_get_value_local(isolate, php_v8_value)->IsUndefined());
}

static PHP_METHOD(V8Value, IsNull) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);

    RETURN_BOOL(php_v8_value_get_value_local(isolate, php_v8_value)->IsNull());
}

static PHP_METHOD(V8Value, IsTrue) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);

    RETURN_BOOL(php_v8_value_get_value_local(isolate, php_v8_value)->IsTrue());
}

static PHP_METHOD(V8Value, IsFalse) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);

    RETURN_BOOL(php_v8_value_get_value_local(isolate, php_v8_value)->IsFalse());
}

static PHP_METHOD(V8Value, IsName) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);

    RETURN_BOOL(php_v8_value_get_value_local(isolate, php_v8_value)->IsName());
}

static PHP_METHOD(V8Value, IsString) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);

    RETURN_BOOL(php_v8_value_get_value_local(isolate, php_v8_value)->IsString());
}

static PHP_METHOD(V8Value, IsSymbol) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);

    RETURN_BOOL(php_v8_value_get_value_local(isolate, php_v8_value)->IsSymbol());
}

static PHP_METHOD(V8Value, IsFunction) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);

    RETURN_BOOL(php_v8_value_get_value_local(isolate, php_v8_value)->IsFunction());
}

static PHP_METHOD(V8Value, IsArray) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);

    RETURN_BOOL(php_v8_value_get_value_local(isolate, php_v8_value)->IsArray());
}

static PHP_METHOD(V8Value, IsObject) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);

    RETURN_BOOL(php_v8_value_get_value_local(isolate, php_v8_value)->IsObject());
}

static PHP_METHOD(V8Value, IsBoolean) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);

    RETURN_BOOL(php_v8_value_get_value_local(isolate, php_v8_value)->IsBoolean());
}

static PHP_METHOD(V8Value, IsNumber) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);

    RETURN_BOOL(php_v8_value_get_value_local(isolate, php_v8_value)->IsNumber());
}

static PHP_METHOD(V8Value, IsInt32) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);

    RETURN_BOOL(php_v8_value_get_value_local(isolate, php_v8_value)->IsInt32());
}

static PHP_METHOD(V8Value, IsUint32) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);

    RETURN_BOOL(php_v8_value_get_value_local(isolate, php_v8_value)->IsUint32());
}

static PHP_METHOD(V8Value, IsDate) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);

    RETURN_BOOL(php_v8_value_get_value_local(isolate, php_v8_value)->IsDate());
}

static PHP_METHOD(V8Value, IsArgumentsObject) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);

    RETURN_BOOL(php_v8_value_get_value_local(isolate, php_v8_value)->IsArgumentsObject());
}

static PHP_METHOD(V8Value, IsBooleanObject) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);

    RETURN_BOOL(php_v8_value_get_value_local(isolate, php_v8_value)->IsBooleanObject());
}

static PHP_METHOD(V8Value, IsNumberObject) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);

    RETURN_BOOL(php_v8_value_get_value_local(isolate, php_v8_value)->IsNumberObject());
}

static PHP_METHOD(V8Value, IsStringObject) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);

    RETURN_BOOL(php_v8_value_get_value_local(isolate, php_v8_value)->IsStringObject());
}

static PHP_METHOD(V8Value, IsSymbolObject) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);

    RETURN_BOOL(php_v8_value_get_value_local(isolate, php_v8_value)->IsSymbolObject());
}

static PHP_METHOD(V8Value, IsNativeError) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);

    // NativeError is always object (see v8 sources)
    if (!php_v8_value_get_value_local(isolate, php_v8_value)->IsObject()) {
        RETURN_FALSE;
    }

    v8::Local<v8::Object> local = php_v8_value_get_object_local(isolate, php_v8_value);

    // We enter object's context, without it IsNativeError() causes segfault
    v8::Local<v8::Context> context = local->CreationContext();
    PHP_V8_CONTEXT_ENTER(context);

    RETURN_BOOL(local->IsNativeError());
}

static PHP_METHOD(V8Value, IsRegExp) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);

    RETURN_BOOL(php_v8_value_get_value_local(isolate, php_v8_value)->IsRegExp());
}


// TODO: bind other methods that matters

/* -----------------------------------------------------------------------
          Converters from v8::Value to high-level v8::Value's children
   ----------------------------------------------------------------------- */

static PHP_METHOD(V8Value, ToBoolean) {
    zval *php_v8_context_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o", &php_v8_context_zv) == FAILURE) {
        return;
    }

    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);
    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_context);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    PHP_V8_TRY_CATCH(isolate);
    PHP_V8_INIT_ISOLATE_LIMITS_ON_CONTEXT(php_v8_context);

    v8::MaybeLocal<v8::Boolean> maybe_local = php_v8_value_get_value_local(isolate, php_v8_value)->ToBoolean(context);

    PHP_V8_MAYBE_CATCH(php_v8_context, try_catch);
    PHP_V8_THROW_EXCEPTION_WHEN_EMPTY(maybe_local, "Failed to convert");

    v8::Local<v8::Boolean> local_value = maybe_local.ToLocalChecked();

    php_v8_get_or_create_value(return_value, local_value, isolate);
}

static PHP_METHOD(V8Value, ToNumber) {
    zval *php_v8_context_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o", &php_v8_context_zv) == FAILURE) {
        return;
    }

    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);
    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_context);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    PHP_V8_TRY_CATCH(isolate);
    PHP_V8_INIT_ISOLATE_LIMITS_ON_CONTEXT(php_v8_context);

    v8::MaybeLocal<v8::Number> maybe_local = php_v8_value_get_value_local(isolate, php_v8_value)->ToNumber(context);

    PHP_V8_MAYBE_CATCH(php_v8_context, try_catch);
    PHP_V8_THROW_EXCEPTION_WHEN_EMPTY(maybe_local, "Failed to convert");

    v8::Local<v8::Number> local_value = maybe_local.ToLocalChecked();

    php_v8_get_or_create_value(return_value, local_value, isolate);
}

static PHP_METHOD(V8Value, ToString) {
    zval *php_v8_context_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o", &php_v8_context_zv) == FAILURE) {
        return;
    }

    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);
    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_context);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    PHP_V8_TRY_CATCH(isolate);
    PHP_V8_INIT_ISOLATE_LIMITS_ON_CONTEXT(php_v8_context);

    v8::MaybeLocal<v8::String> maybe_local = php_v8_value_get_value_local(isolate, php_v8_value)->ToString(isolate);

    PHP_V8_MAYBE_CATCH(php_v8_context, try_catch);
    PHP_V8_THROW_EXCEPTION_WHEN_EMPTY(maybe_local, "Failed to convert");

    v8::Local<v8::String> local_value = maybe_local.ToLocalChecked();

    php_v8_get_or_create_value(return_value, local_value, isolate);
}

static PHP_METHOD(V8Value, ToDetailString) {
    zval *php_v8_context_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o", &php_v8_context_zv) == FAILURE) {
        return;
    }

    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);
    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_context);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    PHP_V8_TRY_CATCH(isolate);
    PHP_V8_INIT_ISOLATE_LIMITS_ON_CONTEXT(php_v8_context);

    v8::MaybeLocal<v8::String> maybe_local = php_v8_value_get_value_local(isolate, php_v8_value)->ToDetailString(context);

    PHP_V8_MAYBE_CATCH(php_v8_context, try_catch);
    PHP_V8_THROW_EXCEPTION_WHEN_EMPTY(maybe_local, "Failed to convert");

    v8::Local<v8::String> local_value = maybe_local.ToLocalChecked();

    php_v8_get_or_create_value(return_value, local_value, isolate);
}

static PHP_METHOD(V8Value, ToObject) {
    zval *php_v8_context_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o", &php_v8_context_zv) == FAILURE) {
        return;
    }

    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);
    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_context);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    PHP_V8_TRY_CATCH(isolate);
    PHP_V8_INIT_ISOLATE_LIMITS_ON_CONTEXT(php_v8_context);

    v8::MaybeLocal<v8::Object> maybe_local = php_v8_value_get_value_local(isolate, php_v8_value)->ToObject(context);

    PHP_V8_MAYBE_CATCH(php_v8_context, try_catch);
    PHP_V8_THROW_EXCEPTION_WHEN_EMPTY(maybe_local, "Failed to convert");

    v8::Local<v8::Object> local_value = maybe_local.ToLocalChecked();

    php_v8_get_or_create_value(return_value, local_value, isolate);
}

static PHP_METHOD(V8Value, ToInteger) {
    zval *php_v8_context_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o", &php_v8_context_zv) == FAILURE) {
        return;
    }

    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);
    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_context);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    PHP_V8_TRY_CATCH(isolate);
    PHP_V8_INIT_ISOLATE_LIMITS_ON_CONTEXT(php_v8_context);

    v8::MaybeLocal<v8::Integer> maybe_local = php_v8_value_get_value_local(isolate, php_v8_value)->ToInteger(context);

    PHP_V8_MAYBE_CATCH(php_v8_context, try_catch);
    PHP_V8_THROW_EXCEPTION_WHEN_EMPTY(maybe_local, "Failed to convert");

    v8::Local<v8::Integer> local_value = maybe_local.ToLocalChecked();

    php_v8_get_or_create_value(return_value, local_value, isolate);
}

static PHP_METHOD(V8Value, ToUint32) {
    zval *php_v8_context_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o", &php_v8_context_zv) == FAILURE) {
        return;
    }

    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);
    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_context);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    PHP_V8_TRY_CATCH(isolate);
    PHP_V8_INIT_ISOLATE_LIMITS_ON_CONTEXT(php_v8_context);

    v8::MaybeLocal<v8::Uint32> maybe_local = php_v8_value_get_value_local(isolate, php_v8_value)->ToUint32(context);

    PHP_V8_MAYBE_CATCH(php_v8_context, try_catch);
    PHP_V8_THROW_EXCEPTION_WHEN_EMPTY(maybe_local, "Failed to convert");

    v8::Local<v8::Uint32> local_value = maybe_local.ToLocalChecked();

    php_v8_get_or_create_value(return_value, local_value, isolate);
}

static PHP_METHOD(V8Value, ToInt32) {
    zval *php_v8_context_zv;


    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o", &php_v8_context_zv) == FAILURE) {
        return;
    }

    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);
    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_context);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    PHP_V8_TRY_CATCH(isolate);
    PHP_V8_INIT_ISOLATE_LIMITS_ON_CONTEXT(php_v8_context);

    v8::MaybeLocal<v8::Int32> maybe_local = php_v8_value_get_value_local(isolate, php_v8_value)->ToInt32(context);

    PHP_V8_MAYBE_CATCH(php_v8_context, try_catch);
    PHP_V8_THROW_EXCEPTION_WHEN_EMPTY(maybe_local, "Failed to convert");

    v8::Local<v8::Int32> local_value = maybe_local.ToLocalChecked();

    php_v8_get_or_create_value(return_value, local_value, isolate);
}

static PHP_METHOD(V8Value, ToArrayIndex) {
    zval *php_v8_context_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o", &php_v8_context_zv) == FAILURE) {
        return;
    }

    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);
    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_context);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    PHP_V8_TRY_CATCH(isolate);
    PHP_V8_INIT_ISOLATE_LIMITS_ON_CONTEXT(php_v8_context);

    v8::MaybeLocal<v8::Uint32> maybe_local = php_v8_value_get_value_local(isolate, php_v8_value)->ToArrayIndex(context);

    PHP_V8_MAYBE_CATCH(php_v8_context, try_catch);
    PHP_V8_THROW_EXCEPTION_WHEN_EMPTY(maybe_local, "Failed to convert");

    v8::Local<v8::Uint32> local_value = maybe_local.ToLocalChecked();

    php_v8_get_or_create_value(return_value, local_value, isolate);
}


/* -----------------------------------------------------------------------
          Converters from v8::Value to low-level primitives
   ----------------------------------------------------------------------- */


static PHP_METHOD(V8Value, BooleanValue) {
    zval *php_v8_context_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o", &php_v8_context_zv) == FAILURE) {
        return;
    }

    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);
    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_context);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);
    PHP_V8_DECLARE_CONTEXT(php_v8_context);

    v8::Maybe<bool> maybe = php_v8_value_get_value_local(isolate, php_v8_value)->BooleanValue(context);

    if (maybe.IsNothing()) {
        PHP_V8_THROW_EXCEPTION("Failed to convert");
        return;
    }

    RETVAL_BOOL(maybe.FromJust());
}

static PHP_METHOD(V8Value, NumberValue) {
    zval *php_v8_context_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o", &php_v8_context_zv) == FAILURE) {
        return;
    }

    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);
    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_context);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);
    PHP_V8_DECLARE_CONTEXT(php_v8_context);

    v8::Maybe<double> maybe = php_v8_value_get_value_local(isolate, php_v8_value)->NumberValue(context);

    PHP_V8_THROW_EXCEPTION_WHEN_NOTHING(maybe, "Failed to convert");

    RETVAL_DOUBLE(maybe.FromJust());
}

static PHP_METHOD(V8Value, IntegerValue) {
    zval *php_v8_context_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o", &php_v8_context_zv) == FAILURE) {
        return;
    }

    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);
    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_context);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);
    PHP_V8_DECLARE_CONTEXT(php_v8_context);

    v8::Maybe<int64_t> maybe = php_v8_value_get_value_local(isolate, php_v8_value)->IntegerValue(context);

    PHP_V8_THROW_EXCEPTION_WHEN_NOTHING(maybe, "Failed to convert");

    RETVAL_DOUBLE((double) maybe.FromJust());
}

static PHP_METHOD(V8Value, Uint32Value) {
    zval *php_v8_context_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o", &php_v8_context_zv) == FAILURE) {
        return;
    }

    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);
    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_context);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);
    PHP_V8_DECLARE_CONTEXT(php_v8_context);

    v8::Maybe<uint32_t> maybe = php_v8_value_get_value_local(isolate, php_v8_value)->Uint32Value(context);

    PHP_V8_THROW_EXCEPTION_WHEN_NOTHING(maybe, "Failed to convert");

    RETVAL_LONG((zend_long) maybe.FromJust());
}

static PHP_METHOD(V8Value, Int32Value) {
    zval *php_v8_context_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o", &php_v8_context_zv) == FAILURE) {
        return;
    }

    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);
    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_context);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);
    PHP_V8_DECLARE_CONTEXT(php_v8_context);

    v8::Maybe<int32_t> maybe = php_v8_value_get_value_local(isolate, php_v8_value)->Int32Value(context);

    PHP_V8_THROW_EXCEPTION_WHEN_NOTHING(maybe, "Failed to convert");

    RETVAL_LONG((zend_long) maybe.FromJust());
}

/** JS == */

static PHP_METHOD(V8Value, Equals) {
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

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);

    PHP_V8_DECLARE_CONTEXT(php_v8_context); // TODO: Declare or enter?

    v8::Maybe<bool> maybe = php_v8_value_get_value_local(isolate, php_v8_value)->Equals(context,
                                                                                       php_v8_value_get_value_local(
                                                                                              isolate, php_v8_value_that));

    if (maybe.IsNothing()) {
        PHP_V8_THROW_EXCEPTION("Failed to compare");
        return;
    }

    RETVAL_BOOL(maybe.FromJust());
}

static PHP_METHOD(V8Value, StrictEquals) {
    zval *php_v8_value_that_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o", &php_v8_value_that_zv) == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);

    PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_value_that_zv, php_v8_value_that);
    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_value_that);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);

    bool val = php_v8_value_get_value_local(isolate, php_v8_value)->StrictEquals(
            php_v8_value_get_value_local(isolate, php_v8_value_that));

    RETVAL_BOOL(val);
}

static PHP_METHOD(V8Value, SameValue) {
    zval *php_v8_value_that_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o", &php_v8_value_that_zv) == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_value_that_zv, php_v8_value_that);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_value_that);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);

    bool val = php_v8_value_get_value_local(isolate, php_v8_value)->SameValue(
            php_v8_value_get_value_local(isolate, php_v8_value_that));

    RETVAL_BOOL(val);
}



ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_value___construct, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 1)
                ZEND_ARG_OBJ_INFO(0, isolate, v8\\Isolate, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_value_GetIsolate, ZEND_RETURN_VALUE, 0, IS_OBJECT, "v8\\Isolate", 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_value_IsUndefined,        ZEND_RETURN_VALUE, 0, _IS_BOOL, NULL, 0)
ZEND_END_ARG_INFO()
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_value_IsNull,             ZEND_RETURN_VALUE, 0, _IS_BOOL, NULL, 0)
ZEND_END_ARG_INFO()
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_value_IsTrue,             ZEND_RETURN_VALUE, 0, _IS_BOOL, NULL, 0)
ZEND_END_ARG_INFO()
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_value_IsFalse,            ZEND_RETURN_VALUE, 0, _IS_BOOL, NULL, 0)
ZEND_END_ARG_INFO()
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_value_IsName,             ZEND_RETURN_VALUE, 0, _IS_BOOL, NULL, 0)
ZEND_END_ARG_INFO()
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_value_IsString,           ZEND_RETURN_VALUE, 0, _IS_BOOL, NULL, 0)
ZEND_END_ARG_INFO()
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_value_IsSymbol,           ZEND_RETURN_VALUE, 0, _IS_BOOL, NULL, 0)
ZEND_END_ARG_INFO()
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_value_IsFunction,         ZEND_RETURN_VALUE, 0, _IS_BOOL, NULL, 0)
ZEND_END_ARG_INFO()
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_value_IsArray,            ZEND_RETURN_VALUE, 0, _IS_BOOL, NULL, 0)
ZEND_END_ARG_INFO()
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_value_IsObject,           ZEND_RETURN_VALUE, 0, _IS_BOOL, NULL, 0)
ZEND_END_ARG_INFO()
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_value_IsBoolean,          ZEND_RETURN_VALUE, 0, _IS_BOOL, NULL, 0)
ZEND_END_ARG_INFO()
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_value_IsNumber,           ZEND_RETURN_VALUE, 0, _IS_BOOL, NULL, 0)
ZEND_END_ARG_INFO()
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_value_IsInt32,            ZEND_RETURN_VALUE, 0, _IS_BOOL, NULL, 0)
ZEND_END_ARG_INFO()
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_value_IsUint32,           ZEND_RETURN_VALUE, 0, _IS_BOOL, NULL, 0)
ZEND_END_ARG_INFO()
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_value_IsDate,             ZEND_RETURN_VALUE, 0, _IS_BOOL, NULL, 0)
ZEND_END_ARG_INFO()
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_value_IsArgumentsObject,  ZEND_RETURN_VALUE, 0, _IS_BOOL, NULL, 0)
ZEND_END_ARG_INFO()
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_value_IsBooleanObject,    ZEND_RETURN_VALUE, 0, _IS_BOOL, NULL, 0)
ZEND_END_ARG_INFO()
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_value_IsNumberObject,     ZEND_RETURN_VALUE, 0, _IS_BOOL, NULL, 0)
ZEND_END_ARG_INFO()
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_value_IsStringObject,     ZEND_RETURN_VALUE, 0, _IS_BOOL, NULL, 0)
ZEND_END_ARG_INFO()
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_value_IsSymbolObject,     ZEND_RETURN_VALUE, 0, _IS_BOOL, NULL, 0)
ZEND_END_ARG_INFO()
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_value_IsNativeError,      ZEND_RETURN_VALUE, 0, _IS_BOOL, NULL, 0)
ZEND_END_ARG_INFO()
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_value_IsRegExp,           ZEND_RETURN_VALUE, 0, _IS_BOOL, NULL, 0)
ZEND_END_ARG_INFO()


ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_value_ToBoolean, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 1)
                ZEND_ARG_OBJ_INFO(0, context, v8\\Context, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_value_ToNumber, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 1)
                ZEND_ARG_OBJ_INFO(0, context, v8\\Context, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_value_ToString, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 1)
                ZEND_ARG_OBJ_INFO(0, context, v8\\Context, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_value_ToDetailString, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 1)
                ZEND_ARG_OBJ_INFO(0, context, v8\\Context, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_value_ToObject, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 1)
                ZEND_ARG_OBJ_INFO(0, context, v8\\Context, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_value_ToInteger, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 1)
                ZEND_ARG_OBJ_INFO(0, context, v8\\Context, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_value_ToUint32, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 1)
                ZEND_ARG_OBJ_INFO(0, context, v8\\Context, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_value_ToInt32, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 1)
                ZEND_ARG_OBJ_INFO(0, context, v8\\Context, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_value_ToArrayIndex, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 1)
                ZEND_ARG_OBJ_INFO(0, context, v8\\Context, 0)
ZEND_END_ARG_INFO()


ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_value_BooleanValue, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 1)
                ZEND_ARG_OBJ_INFO(0, context, v8\\Context, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_value_NumberValue, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 1)
                ZEND_ARG_OBJ_INFO(0, context, v8\\Context, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_value_IntegerValue, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 1)
                ZEND_ARG_OBJ_INFO(0, context, v8\\Context, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_value_Int32Value, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 1)
                ZEND_ARG_OBJ_INFO(0, context, v8\\Context, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_value_Uint32Value, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 1)
                ZEND_ARG_OBJ_INFO(0, context, v8\\Context, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_value_Equals, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 2)
                ZEND_ARG_OBJ_INFO(0, context, v8\\Context, 0)
                ZEND_ARG_OBJ_INFO(0, that, v8\\Value, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_value_StrictEquals, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 1)
                ZEND_ARG_OBJ_INFO(0, that, v8\\Value, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_value_SameValue, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 1)
                ZEND_ARG_OBJ_INFO(0, that, v8\\Value, 0)
ZEND_END_ARG_INFO()


static const zend_function_entry php_v8_value_methods[] = {
        PHP_ME(V8Value, __construct, arginfo_v8_value___construct, ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)
        PHP_ME(V8Value, GetIsolate, arginfo_v8_value_GetIsolate, ZEND_ACC_PUBLIC)

        PHP_ME(V8Value, IsUndefined,        arginfo_v8_value_IsUndefined,       ZEND_ACC_PUBLIC)
        PHP_ME(V8Value, IsNull,             arginfo_v8_value_IsNull,            ZEND_ACC_PUBLIC)
        PHP_ME(V8Value, IsTrue,             arginfo_v8_value_IsTrue,            ZEND_ACC_PUBLIC)
        PHP_ME(V8Value, IsFalse,            arginfo_v8_value_IsFalse,           ZEND_ACC_PUBLIC)
        PHP_ME(V8Value, IsName,             arginfo_v8_value_IsName,            ZEND_ACC_PUBLIC)
        PHP_ME(V8Value, IsString,           arginfo_v8_value_IsString,          ZEND_ACC_PUBLIC)
        PHP_ME(V8Value, IsSymbol,           arginfo_v8_value_IsSymbol,          ZEND_ACC_PUBLIC)
        PHP_ME(V8Value, IsFunction,         arginfo_v8_value_IsFunction,        ZEND_ACC_PUBLIC)
        PHP_ME(V8Value, IsArray,            arginfo_v8_value_IsArray,           ZEND_ACC_PUBLIC)
        PHP_ME(V8Value, IsObject,           arginfo_v8_value_IsObject,          ZEND_ACC_PUBLIC)
        PHP_ME(V8Value, IsBoolean,          arginfo_v8_value_IsBoolean,         ZEND_ACC_PUBLIC)
        PHP_ME(V8Value, IsNumber,           arginfo_v8_value_IsNumber,          ZEND_ACC_PUBLIC)
        PHP_ME(V8Value, IsInt32,            arginfo_v8_value_IsInt32,           ZEND_ACC_PUBLIC)
        PHP_ME(V8Value, IsUint32,           arginfo_v8_value_IsUint32,          ZEND_ACC_PUBLIC)
        PHP_ME(V8Value, IsDate,             arginfo_v8_value_IsDate,            ZEND_ACC_PUBLIC)
        PHP_ME(V8Value, IsArgumentsObject,  arginfo_v8_value_IsArgumentsObject, ZEND_ACC_PUBLIC)
        PHP_ME(V8Value, IsBooleanObject,    arginfo_v8_value_IsBooleanObject,   ZEND_ACC_PUBLIC)
        PHP_ME(V8Value, IsNumberObject,     arginfo_v8_value_IsNumberObject,    ZEND_ACC_PUBLIC)
        PHP_ME(V8Value, IsStringObject,     arginfo_v8_value_IsStringObject,    ZEND_ACC_PUBLIC)
        PHP_ME(V8Value, IsSymbolObject,     arginfo_v8_value_IsSymbolObject,    ZEND_ACC_PUBLIC)
        PHP_ME(V8Value, IsNativeError,      arginfo_v8_value_IsNativeError,     ZEND_ACC_PUBLIC)
        PHP_ME(V8Value, IsRegExp,           arginfo_v8_value_IsRegExp,          ZEND_ACC_PUBLIC)

        PHP_ME(V8Value, ToBoolean,          arginfo_v8_value_ToBoolean,         ZEND_ACC_PUBLIC)
        PHP_ME(V8Value, ToNumber,           arginfo_v8_value_ToNumber,          ZEND_ACC_PUBLIC)
        PHP_ME(V8Value, ToString,           arginfo_v8_value_ToString,          ZEND_ACC_PUBLIC)
        PHP_ME(V8Value, ToDetailString,     arginfo_v8_value_ToDetailString,    ZEND_ACC_PUBLIC)
        PHP_ME(V8Value, ToObject,           arginfo_v8_value_ToObject,          ZEND_ACC_PUBLIC)
        PHP_ME(V8Value, ToInteger,          arginfo_v8_value_ToInteger,         ZEND_ACC_PUBLIC)
        PHP_ME(V8Value, ToUint32,           arginfo_v8_value_ToUint32,          ZEND_ACC_PUBLIC)
        PHP_ME(V8Value, ToInt32,            arginfo_v8_value_ToInt32,           ZEND_ACC_PUBLIC)
        PHP_ME(V8Value, ToArrayIndex,       arginfo_v8_value_ToArrayIndex,      ZEND_ACC_PUBLIC)

        PHP_ME(V8Value, BooleanValue,       arginfo_v8_value_BooleanValue,      ZEND_ACC_PUBLIC)
        PHP_ME(V8Value, NumberValue,        arginfo_v8_value_NumberValue,       ZEND_ACC_PUBLIC)
        PHP_ME(V8Value, IntegerValue,       arginfo_v8_value_IntegerValue,      ZEND_ACC_PUBLIC)
        PHP_ME(V8Value, Int32Value,         arginfo_v8_value_Int32Value,        ZEND_ACC_PUBLIC)
        PHP_ME(V8Value, Uint32Value,        arginfo_v8_value_Uint32Value,       ZEND_ACC_PUBLIC)

        PHP_ME(V8Value, Equals,             arginfo_v8_value_Equals,            ZEND_ACC_PUBLIC)
        PHP_ME(V8Value, StrictEquals,       arginfo_v8_value_StrictEquals,      ZEND_ACC_PUBLIC)
        PHP_ME(V8Value, SameValue,          arginfo_v8_value_SameValue,         ZEND_ACC_PUBLIC)
        PHP_FE_END
};


PHP_MINIT_FUNCTION (php_v8_value) {
    zend_class_entry ce;
    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS, "Value", php_v8_value_methods);
    this_ce = zend_register_internal_class_ex(&ce, php_v8_data_class_entry);
    this_ce->create_object = php_v8_value_ctor;

    zend_declare_property_null(this_ce, ZEND_STRL("isolate"), ZEND_ACC_PRIVATE);

    memcpy(&php_v8_value_object_handlers, zend_get_std_object_handlers(), sizeof(zend_object_handlers));

    php_v8_value_object_handlers.offset   = XtOffsetOf(php_v8_value_t, std);
    php_v8_value_object_handlers.free_obj = php_v8_value_free;
    php_v8_value_object_handlers.get_gc   = php_v8_value_gc;

    return SUCCESS;
}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: noet sw=4 ts=4 fdm=marker
 * vim<600: noet sw=4 ts=4
 */
