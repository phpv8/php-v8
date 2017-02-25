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

#include "php_v8_function_template.h"
#include "php_v8_object_template.h"
#include "php_v8_function.h"
#include "php_v8_string.h"
#include "php_v8_object.h"
#include "php_v8_value.h"
#include "php_v8_context.h"
#include "php_v8_ext_mem_interface.h"
#include "php_v8.h"

zend_class_entry *php_v8_function_template_class_entry;
#define this_ce php_v8_function_template_class_entry

static zend_object_handlers php_v8_function_template_object_handlers;


static void php_v8_function_template_weak_callback(const v8::WeakCallbackInfo<v8::Persistent<v8::FunctionTemplate>> &data) {
    v8::Isolate *isolate = data.GetIsolate();
    php_v8_isolate_t *php_v8_isolate = PHP_V8_ISOLATE_FETCH_REFERENCE(isolate);

    phpv8::PersistentData *persistent_data = php_v8_isolate->weak_function_templates->get(data.GetParameter());

    if (persistent_data != nullptr) {
        // Tell v8 that we release external allocated memory
        php_v8_debug_external_mem("Free allocated external memory (func tpl: %p): -%" PRId64 "\n", persistent_data, persistent_data->getTotalSize())
        isolate->AdjustAmountOfExternalAllocatedMemory(-persistent_data->getTotalSize());
        php_v8_isolate->weak_function_templates->remove(data.GetParameter());
    }

    data.GetParameter()->Reset();
    delete data.GetParameter();
}

void php_v8_function_template_make_weak(php_v8_function_template_t *php_v8_function_template) {
    php_v8_function_template->php_v8_isolate->weak_function_templates->add(php_v8_function_template->persistent, php_v8_function_template->persistent_data);

    php_v8_function_template->is_weak = true;
    php_v8_function_template->persistent->SetWeak(php_v8_function_template->persistent, php_v8_function_template_weak_callback, v8::WeakCallbackType::kParameter);

    // Tell v8 that we allocated external memory
    php_v8_debug_external_mem("Allocate external memory (func tpl: %p):  %" PRId64 "\n", php_v8_function_template->persistent_data, php_v8_function_template->persistent_data->getTotalSize())
    php_v8_function_template->php_v8_isolate->isolate->AdjustAmountOfExternalAllocatedMemory(php_v8_function_template->persistent_data->getTotalSize());
}


static HashTable *php_v8_function_template_gc(zval *object, zval **table, int *n) {
    PHP_V8_FUNCTION_TEMPLATE_FETCH_INTO(object, php_v8_function_template);

    php_v8_callbacks_gc(php_v8_function_template->persistent_data, &php_v8_function_template->gc_data, &php_v8_function_template->gc_data_count, table, n);

    return zend_std_get_properties(object);
}

static void php_v8_function_template_free(zend_object *object) {
    php_v8_function_template_t *php_v8_function_template = php_v8_function_template_fetch_object(object);

    /*
     * Making it weak makes sense here while before object is active and it marked as weak, we can't guarantee when
     * weak callback will be called (or will it be called at all). But if it will be called when object is live, we may
     * run into situation when some object internal structures will be partially freed: weak callback resets v8's
     * persistent handler and cleanup callbacks. Alternatively, we can detect in weak callback that object is live and
     * unmark it as weak and do all that cleanings in free handler. What about if object will be reused after being
     * unmarked as week? Note, that the only action on weak handler callback is Reset()ing persistent handler.
     */
    if (zend_is_executing() && !CG(unclean_shutdown) && php_v8_function_template->persistent_data && !php_v8_function_template->persistent_data->empty()) {
        php_v8_function_template_make_weak(php_v8_function_template);
    }

    if (!php_v8_function_template->is_weak) {
        if (php_v8_function_template->persistent_data) {
            delete php_v8_function_template->persistent_data;
        }

        if (php_v8_function_template->persistent) {
            if (PHP_V8_ISOLATE_HAS_VALID_HANDLE(php_v8_function_template)) {
                php_v8_function_template->persistent->Reset();
            }

            delete php_v8_function_template->persistent;
        }
    }

    delete php_v8_function_template->node;

    if (php_v8_function_template->gc_data) {
        efree(php_v8_function_template->gc_data);
    }

    zend_object_std_dtor(&php_v8_function_template->std);
}

static zend_object * php_v8_function_template_ctor(zend_class_entry *ce) {
    php_v8_function_template_t *php_v8_function_template;

    php_v8_function_template = (php_v8_function_template_t *) ecalloc(1, sizeof(php_v8_function_template_t) + zend_object_properties_size(ce));

    zend_object_std_init(&php_v8_function_template->std, ce);
    object_properties_init(&php_v8_function_template->std, ce);

    php_v8_function_template->persistent = new v8::Persistent<v8::FunctionTemplate>();
    php_v8_function_template->persistent_data = new phpv8::PersistentData();

    php_v8_function_template->node = new phpv8::TemplateNode();

    php_v8_function_template->std.handlers = &php_v8_function_template_object_handlers;

    return &php_v8_function_template->std;
}


static PHP_METHOD(V8FunctionTemplate, __construct) {
    zval *php_v8_isolate_zv;

    zend_fcall_info fci = empty_fcall_info;
    zend_fcall_info_cache fci_cache = empty_fcall_info_cache;

    zend_long length = 0;

    v8::FunctionCallback callback = 0;
    v8::Local<v8::External> data;
    v8::Local<v8::Signature> signature;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o|f!l", &php_v8_isolate_zv, &fci, &fci_cache, &length) ==
        FAILURE) {
        return;
    }

    PHP_V8_CHECK_FUNCTION_LENGTH_RANGE(length, "Length is out of range");

    PHP_V8_ISOLATE_FETCH_WITH_CHECK(php_v8_isolate_zv, php_v8_isolate);
    PHP_V8_FUNCTION_TEMPLATE_FETCH_INTO(getThis(), php_v8_function_template);

    PHP_V8_TEMPLATE_STORE_ISOLATE(getThis(), php_v8_isolate_zv)
    PHP_V8_STORE_POINTER_TO_ISOLATE(php_v8_function_template, php_v8_isolate);

    PHP_V8_ENTER_ISOLATE(php_v8_isolate);

    if (fci.size) {
        phpv8::CallbacksBucket *bucket= php_v8_function_template->persistent_data->bucket("callback");
        data = v8::External::New(isolate, bucket);

        bucket->add(0, fci, fci_cache);

        callback = php_v8_callback_function;
    }

    v8::Local<v8::FunctionTemplate> local_template = v8::FunctionTemplate::New(isolate, callback, data, signature, static_cast<int>(length));

    PHP_V8_THROW_VALUE_EXCEPTION_WHEN_EMPTY(local_template, "Failed to create FunctionTemplate value");

    php_v8_function_template->persistent->Reset(isolate, local_template);
}


static PHP_METHOD(V8FunctionTemplate, GetIsolate) {
    zval rv;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_FETCH_FUNCTION_TEMPLATE_WITH_CHECK(getThis(), php_v8_function_template);

    RETVAL_ZVAL(PHP_V8_TEMPLATE_READ_ISOLATE(getThis()), 1, 0);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

static PHP_METHOD(V8FunctionTemplate, Set) {
    php_v8_function_template_Set(INTERNAL_FUNCTION_PARAM_PASSTHRU);
}

static PHP_METHOD(V8FunctionTemplate, SetAccessorProperty) {
    php_v8_function_template_SetAccessorProperty(INTERNAL_FUNCTION_PARAM_PASSTHRU);
}

static PHP_METHOD(V8FunctionTemplate, SetNativeDataProperty) {
    php_v8_function_template_SetNativeDataProperty(INTERNAL_FUNCTION_PARAM_PASSTHRU);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

static PHP_METHOD(V8FunctionTemplate, GetFunction) {
    zval *php_v8_context_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o", &php_v8_context_zv) == FAILURE) {
        return;
    }

    PHP_V8_FETCH_FUNCTION_TEMPLATE_WITH_CHECK(getThis(), php_v8_function_template);
    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_function_template, php_v8_context);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_function_template);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    v8::Local<v8::FunctionTemplate> local_function_tpl = php_v8_function_template_get_local(php_v8_function_template);
    v8::MaybeLocal<v8::Function> maybe_local_function = local_function_tpl->GetFunction(context);

    PHP_V8_THROW_VALUE_EXCEPTION_WHEN_EMPTY(maybe_local_function, "Failed to get function instance");

    v8::Local<v8::Function> local_function = maybe_local_function.ToLocalChecked();

    php_v8_get_or_create_value(return_value, local_function, php_v8_context->php_v8_isolate);
}

static PHP_METHOD(V8FunctionTemplate, SetCallHandler) {
    zend_fcall_info fci = empty_fcall_info;
    zend_fcall_info_cache fci_cache = empty_fcall_info_cache;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "f", &fci, &fci_cache) == FAILURE) {
        return;
    }

    if (!fci.size) {
        return;
    }

    PHP_V8_FETCH_FUNCTION_TEMPLATE_WITH_CHECK(getThis(), php_v8_function_template);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_function_template);

    phpv8::CallbacksBucket *bucket= php_v8_function_template->persistent_data->bucket("callback");
    bucket->add(0, fci, fci_cache);

    v8::Local<v8::FunctionTemplate> local_template = php_v8_function_template_get_local(php_v8_function_template);

    local_template->SetCallHandler(php_v8_callback_function, v8::External::New(isolate, bucket));
}

static PHP_METHOD(V8FunctionTemplate, SetLength) {
    zend_long length;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "l", &length) == FAILURE) {
        return;
    }

    PHP_V8_CHECK_FUNCTION_LENGTH_RANGE(length, "Length is out of range");

    PHP_V8_FETCH_FUNCTION_TEMPLATE_WITH_CHECK(getThis(), php_v8_function_template);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_function_template);

    v8::Local<v8::FunctionTemplate> local_template = php_v8_function_template_get_local(php_v8_function_template);

    local_template->SetLength(static_cast<int>(length));
}

static PHP_METHOD(V8FunctionTemplate, InstanceTemplate) {
    zval rv;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_FETCH_FUNCTION_TEMPLATE_WITH_CHECK(getThis(), php_v8_function_template);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_function_template);

    v8::Local<v8::FunctionTemplate> local_function_tpl = php_v8_function_template_get_local(php_v8_function_template);

    v8::Local<v8::ObjectTemplate> local_obj_tpl = local_function_tpl->InstanceTemplate();

    PHP_V8_THROW_VALUE_EXCEPTION_WHEN_EMPTY(local_obj_tpl, "Failed to create instance template");

    // TODO: if zval already exists for this InstanceTemplate - return it (and inc pointers count by 1)

    PHP_V8_OBJECT_TEMPLATE_CREATE_FROM_TEMPLATE(return_value, return_php_v8_object_template, getThis(), php_v8_function_template);
    return_php_v8_object_template->persistent->Reset(isolate, local_obj_tpl);
}

static PHP_METHOD(V8FunctionTemplate, Inherit) {
    zval *parent_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o", &parent_zv) == FAILURE) {
        return;
    }

    PHP_V8_FETCH_FUNCTION_TEMPLATE_WITH_CHECK(getThis(), php_v8_function_template);
    PHP_V8_FETCH_FUNCTION_TEMPLATE_WITH_CHECK(parent_zv, php_v8_function_template_parent);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_function_template);

    v8::Local<v8::FunctionTemplate> local_template = php_v8_function_template_get_local(php_v8_function_template);
    v8::Local<v8::FunctionTemplate> local_template_parent = php_v8_function_template_get_local(php_v8_function_template_parent);

    local_template->Inherit(local_template_parent);
}

static PHP_METHOD(V8FunctionTemplate, PrototypeTemplate) {
    zval rv;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_FETCH_FUNCTION_TEMPLATE_WITH_CHECK(getThis(), php_v8_function_template);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_function_template);

    v8::Local<v8::FunctionTemplate> local_function_tpl = php_v8_function_template_get_local(php_v8_function_template);

    v8::Local<v8::ObjectTemplate> local_obj_tpl = local_function_tpl->PrototypeTemplate();

    PHP_V8_THROW_VALUE_EXCEPTION_WHEN_EMPTY(local_obj_tpl, "Failed to get prototype");

    // TODO: if zval already exists for this PrototypeTemplate - return it (and inc pointers count by 1)

    PHP_V8_OBJECT_TEMPLATE_CREATE_FROM_TEMPLATE(return_value, return_php_v8_object_template, getThis(), php_v8_function_template);
    return_php_v8_object_template->persistent->Reset(isolate, local_obj_tpl);
}

static PHP_METHOD(V8FunctionTemplate, SetClassName) {
    zval *php_v8_string_zv = NULL;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o", &php_v8_string_zv) == FAILURE) {
        return;
    }

    PHP_V8_FETCH_FUNCTION_TEMPLATE_WITH_CHECK(getThis(), php_v8_function_template);
    PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_string_zv, php_v8_string);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_function_template, php_v8_string);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_function_template);

    v8::Local<v8::FunctionTemplate> local_function_tpl = php_v8_function_template_get_local(php_v8_function_template);
    v8::Local<v8::String> local_name = php_v8_value_get_local_as<v8::String>(php_v8_string);

    local_function_tpl->SetClassName(local_name);
}

static PHP_METHOD(V8FunctionTemplate, SetAcceptAnyReceiver) {
    zend_bool value;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "b", &value) == FAILURE) {
        return;
    }

    PHP_V8_FETCH_FUNCTION_TEMPLATE_WITH_CHECK(getThis(), php_v8_function_template);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_function_template);


    v8::Local<v8::FunctionTemplate> local_template = php_v8_function_template_get_local(php_v8_function_template);

    local_template->SetAcceptAnyReceiver(static_cast<bool>(value));
}

static PHP_METHOD(V8FunctionTemplate, SetHiddenPrototype) {
    zend_bool value;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "b", &value) == FAILURE) {
        return;
    }

    PHP_V8_FETCH_FUNCTION_TEMPLATE_WITH_CHECK(getThis(), php_v8_function_template);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_function_template);

    v8::Local<v8::FunctionTemplate> local_template = php_v8_function_template_get_local(php_v8_function_template);

    local_template->SetHiddenPrototype(static_cast<bool>(value));
}

static PHP_METHOD(V8FunctionTemplate, ReadOnlyPrototype) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_FETCH_FUNCTION_TEMPLATE_WITH_CHECK(getThis(), php_v8_function_template);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_function_template);

    v8::Local<v8::FunctionTemplate> local_function_tpl = php_v8_function_template_get_local(php_v8_function_template);

    local_function_tpl->ReadOnlyPrototype();
}

static PHP_METHOD(V8FunctionTemplate, RemovePrototype) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_FETCH_FUNCTION_TEMPLATE_WITH_CHECK(getThis(), php_v8_function_template);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_function_template);

    v8::Local<v8::FunctionTemplate> local_function_tpl = php_v8_function_template_get_local(php_v8_function_template);

    local_function_tpl->RemovePrototype();
}

static PHP_METHOD(V8FunctionTemplate, HasInstance) {
    zval *object_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o", &object_zv) == FAILURE) {
        return;
    }

    PHP_V8_FETCH_FUNCTION_TEMPLATE_WITH_CHECK(getThis(), php_v8_function_template);
    PHP_V8_VALUE_FETCH_WITH_CHECK(object_zv, php_v8_object);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_function_template);

    v8::Local<v8::FunctionTemplate> local_template = php_v8_function_template_get_local(php_v8_function_template);
    v8::Local<v8::Object> local_obj = php_v8_value_get_local_as<v8::Object>(php_v8_object);

    RETURN_BOOL(local_template->HasInstance(local_obj));
}

/* Non-standard, implementations of AdjustableExternalMemoryInterface::AdjustExternalAllocatedMemory */
static PHP_METHOD(V8FunctionTemplate, AdjustExternalAllocatedMemory) {
    php_v8_ext_mem_interface_function_template_AdjustExternalAllocatedMemory(INTERNAL_FUNCTION_PARAM_PASSTHRU);
}

/* Non-standard, implementations of AdjustableExternalMemoryInterface::GetExternalAllocatedMemory */
static PHP_METHOD(V8FunctionTemplate, GetExternalAllocatedMemory) {
    php_v8_ext_mem_interface_function_template_GetExternalAllocatedMemory(INTERNAL_FUNCTION_PARAM_PASSTHRU);
}


ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_function_template___construct, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 1)
                ZEND_ARG_OBJ_INFO(0, isolate, V8\\Isolate, 0)
                ZEND_ARG_CALLABLE_INFO(0, callback, 1)
                ZEND_ARG_TYPE_INFO(0, length, IS_LONG, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_v8_function_template_GetIsolate, ZEND_RETURN_VALUE, 0, V8\\Isolate, 0)
ZEND_END_ARG_INFO()

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

ZEND_BEGIN_ARG_INFO_EX(arginfo_php_v8_function_template_Set, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 2)
                ZEND_ARG_OBJ_INFO(0, name, V8\\NameValue, 0)
                ZEND_ARG_OBJ_INFO(0, value, V8\\Data, 0)
                ZEND_ARG_TYPE_INFO(0, attributes, IS_LONG, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_php_v8_function_template_SetAccessorProperty, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 1)
                ZEND_ARG_OBJ_INFO(0, name, V8\\NameValue, 0)
                ZEND_ARG_OBJ_INFO(0, getter, V8\\FunctionTemplate, 0)
                ZEND_ARG_OBJ_INFO(0, setter, V8\\FunctionTemplate, 0)
                ZEND_ARG_TYPE_INFO(0, attributes, IS_LONG, 0)
                ZEND_ARG_TYPE_INFO(0, settings, IS_LONG, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_php_v8_function_template_SetNativeDataProperty, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 2)
                ZEND_ARG_OBJ_INFO(0, name, V8\\NameValue, 0)
                ZEND_ARG_CALLABLE_INFO(0, getter, 0)
                ZEND_ARG_CALLABLE_INFO(0, setter, 1)
                ZEND_ARG_TYPE_INFO(0, attributes, IS_LONG, 0)
                ZEND_ARG_TYPE_INFO(0, settings, IS_LONG, 0)
ZEND_END_ARG_INFO()

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_php_v8_function_template_GetFunction, ZEND_RETURN_VALUE, 1, V8\\FunctionObject, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
ZEND_END_ARG_INFO()

// void method
ZEND_BEGIN_ARG_INFO_EX(arginfo_php_v8_function_template_SetCallHandler, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 1)
                ZEND_ARG_CALLABLE_INFO(0, callback, 0)
ZEND_END_ARG_INFO()

// void method
ZEND_BEGIN_ARG_INFO_EX(arginfo_php_v8_function_template_SetLength, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 1)
                ZEND_ARG_TYPE_INFO(0, length, IS_LONG, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_v8_function_template_InstanceTemplate, ZEND_RETURN_VALUE, 0, V8\\ObjectTemplate, 0)
ZEND_END_ARG_INFO()

// void method
ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_function_template_Inherit, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 1)
                ZEND_ARG_OBJ_INFO(0, parent, V8\\FunctionTemplate, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_v8_function_template_PrototypeTemplate, ZEND_RETURN_VALUE, 0, V8\\ObjectTemplate, 0)
ZEND_END_ARG_INFO()

// void method
ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_function_template_SetClassName, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 1)
                ZEND_ARG_OBJ_INFO(0, name, V8\\StringValue, 0)
ZEND_END_ARG_INFO()

// void method
ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_function_template_SetAcceptAnyReceiver, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 1)
                ZEND_ARG_TYPE_INFO(0, value, _IS_BOOL, 0)
ZEND_END_ARG_INFO()

// void method
ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_function_template_SetHiddenPrototype, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 1)
                ZEND_ARG_TYPE_INFO(0, value, _IS_BOOL, 0)
ZEND_END_ARG_INFO()

// void method
ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_function_template_ReadOnlyPrototype, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 0)
ZEND_END_ARG_INFO()

// void method
ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_function_template_RemovePrototype, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_function_template_HasInstance, ZEND_RETURN_VALUE, 1, _IS_BOOL, 0)
                ZEND_ARG_OBJ_INFO(0, object, V8\\ObjectValue, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_function_template_AdjustExternalAllocatedMemory, ZEND_RETURN_VALUE, 1, IS_LONG, 0)
                ZEND_ARG_TYPE_INFO(0, change_in_bytes, IS_LONG, 0)
ZEND_END_ARG_INFO()


PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_function_template_GetExternalAllocatedMemory, ZEND_RETURN_VALUE, 0, IS_LONG, 0)
ZEND_END_ARG_INFO()


static const zend_function_entry php_v8_function_template_methods[] = {
        PHP_ME(V8FunctionTemplate, __construct,             arginfo_v8_function_template___construct, ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)
        PHP_ME(V8FunctionTemplate, GetIsolate,              arginfo_v8_function_template_GetIsolate, ZEND_ACC_PUBLIC)

        PHP_ME(V8FunctionTemplate, Set,                     arginfo_php_v8_function_template_Set, ZEND_ACC_PUBLIC)
        PHP_ME(V8FunctionTemplate, SetAccessorProperty,     arginfo_php_v8_function_template_SetAccessorProperty, ZEND_ACC_PUBLIC)
        PHP_ME(V8FunctionTemplate, SetNativeDataProperty,   arginfo_php_v8_function_template_SetNativeDataProperty, ZEND_ACC_PUBLIC)

        PHP_ME(V8FunctionTemplate, GetFunction,             arginfo_php_v8_function_template_GetFunction,       ZEND_ACC_PUBLIC)
        PHP_ME(V8FunctionTemplate, SetCallHandler,          arginfo_php_v8_function_template_SetCallHandler,    ZEND_ACC_PUBLIC)
        PHP_ME(V8FunctionTemplate, SetLength,               arginfo_php_v8_function_template_SetLength,         ZEND_ACC_PUBLIC)
        PHP_ME(V8FunctionTemplate, InstanceTemplate,        arginfo_v8_function_template_InstanceTemplate,      ZEND_ACC_PUBLIC)
        PHP_ME(V8FunctionTemplate, Inherit,                 arginfo_v8_function_template_Inherit,               ZEND_ACC_PUBLIC)
        PHP_ME(V8FunctionTemplate, PrototypeTemplate,       arginfo_v8_function_template_PrototypeTemplate,     ZEND_ACC_PUBLIC)
        PHP_ME(V8FunctionTemplate, SetClassName,            arginfo_v8_function_template_SetClassName,          ZEND_ACC_PUBLIC)
        PHP_ME(V8FunctionTemplate, SetAcceptAnyReceiver,    arginfo_v8_function_template_SetAcceptAnyReceiver,  ZEND_ACC_PUBLIC)
        PHP_ME(V8FunctionTemplate, SetHiddenPrototype,      arginfo_v8_function_template_SetHiddenPrototype,    ZEND_ACC_PUBLIC)
        PHP_ME(V8FunctionTemplate, ReadOnlyPrototype,       arginfo_v8_function_template_ReadOnlyPrototype,     ZEND_ACC_PUBLIC)
        PHP_ME(V8FunctionTemplate, RemovePrototype,         arginfo_v8_function_template_RemovePrototype,       ZEND_ACC_PUBLIC)
        PHP_ME(V8FunctionTemplate, HasInstance,             arginfo_v8_function_template_HasInstance,           ZEND_ACC_PUBLIC)

        PHP_ME(V8FunctionTemplate, AdjustExternalAllocatedMemory,   arginfo_v8_function_template_AdjustExternalAllocatedMemory, ZEND_ACC_PUBLIC)
        PHP_ME(V8FunctionTemplate, GetExternalAllocatedMemory,      arginfo_v8_function_template_GetExternalAllocatedMemory, ZEND_ACC_PUBLIC)

        PHP_FE_END
};


PHP_MINIT_FUNCTION (php_v8_function_template) {
    zend_class_entry ce;

    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS, "FunctionTemplate", php_v8_function_template_methods);
    this_ce = zend_register_internal_class_ex(&ce, php_v8_template_ce);
    zend_class_implements(this_ce, 1, php_v8_ext_mem_interface_ce);
    this_ce->create_object = php_v8_function_template_ctor;

    memcpy(&php_v8_function_template_object_handlers, zend_get_std_object_handlers(), sizeof(zend_object_handlers));

    php_v8_function_template_object_handlers.offset    = XtOffsetOf(php_v8_function_template_t, std);
    php_v8_function_template_object_handlers.free_obj  = php_v8_function_template_free;
    php_v8_function_template_object_handlers.get_gc    = php_v8_function_template_gc;
    php_v8_function_template_object_handlers.clone_obj = NULL;

    return SUCCESS;
}
