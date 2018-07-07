/*
 * This file is part of the phpv8/php-v8 PHP extension.
 *
 * Copyright (c) 2015-2018 Bogdan Padalko <thepinepain@gmail.com>
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

#include "php_v8_object_template.h"
#include "php_v8_function_template.h"
#include "php_v8_named_property_handler_configuration.h"
#include "php_v8_indexed_property_handler_configuration.h"
#include "php_v8_name.h"
#include "php_v8_context.h"
#include "php_v8_value.h"
#include "php_v8_ext_mem_interface.h"
#include "php_v8_enums.h"
#include "php_v8.h"

zend_class_entry *php_v8_object_template_class_entry;
#define this_ce php_v8_object_template_class_entry

static zend_object_handlers php_v8_object_template_object_handlers;


static void php_v8_object_template_weak_callback(const v8::WeakCallbackInfo<v8::Persistent<v8::ObjectTemplate>> &data) {
    v8::Isolate *isolate = data.GetIsolate();
    php_v8_isolate_t *php_v8_isolate = PHP_V8_ISOLATE_FETCH_REFERENCE(isolate);

    phpv8::PersistentData *persistent_data = php_v8_isolate->weak_object_templates->get(data.GetParameter());

    if (persistent_data != nullptr) {
        // Tell v8 that we release external allocated memory
        php_v8_debug_external_mem("Free allocated external memory (obj tpl: %p): -%" PRId64 "\n", persistent_data, persistent_data->getTotalSize())
        isolate->AdjustAmountOfExternalAllocatedMemory(-persistent_data->getTotalSize());
        php_v8_isolate->weak_object_templates->remove(data.GetParameter());
    }

    data.GetParameter()->Reset();
    delete data.GetParameter();
}


static void php_v8_object_template_make_weak(php_v8_object_template_t *php_v8_object_template) {
    php_v8_object_template->php_v8_isolate->weak_object_templates->add(php_v8_object_template->persistent, php_v8_object_template->persistent_data);

    php_v8_object_template->is_weak = true;
    php_v8_object_template->persistent->SetWeak(php_v8_object_template->persistent, php_v8_object_template_weak_callback, v8::WeakCallbackType::kParameter);

    // Tell v8 that we allocated external memory
    php_v8_debug_external_mem("Allocate external memory (obj tpl: %p):  %" PRId64 "\n", php_v8_object_template->persistent_data, php_v8_object_template->persistent_data->getTotalSize())
    php_v8_object_template->php_v8_isolate->isolate->AdjustAmountOfExternalAllocatedMemory(php_v8_object_template->persistent_data->getTotalSize());
}

static HashTable * php_v8_object_template_gc(zval *object, zval **table, int *n) {
    PHP_V8_OBJECT_TEMPLATE_FETCH_INTO(object, php_v8_object_template);

    php_v8_callbacks_gc(php_v8_object_template->persistent_data, &php_v8_object_template->gc_data, &php_v8_object_template->gc_data_count, table, n);

    return zend_std_get_properties(object);
}

static void php_v8_object_template_free(zend_object *object) {
    php_v8_object_template_t *php_v8_object_template = php_v8_object_template_fetch_object(object);

    if (PHP_V8_IS_UP_AND_RUNNING() && php_v8_object_template->persistent_data && !php_v8_object_template->persistent_data->empty()) {
        php_v8_object_template_make_weak(php_v8_object_template);
    }

    if (!php_v8_object_template->is_weak) {
        if (php_v8_object_template->persistent_data) {
            delete php_v8_object_template->persistent_data;
            php_v8_object_template->persistent_data = NULL;
        }

        if (PHP_V8_IS_UP_AND_RUNNING() && php_v8_object_template->persistent) {
            if (PHP_V8_ISOLATE_HAS_VALID_HANDLE(php_v8_object_template)) {
                php_v8_object_template->persistent->Reset();
            }

            delete php_v8_object_template->persistent;
        }
    }

    delete php_v8_object_template->node;

    if (php_v8_object_template->gc_data) {
        efree(php_v8_object_template->gc_data);
    }

    zend_object_std_dtor(&php_v8_object_template->std);
}

static zend_object * php_v8_object_template_ctor(zend_class_entry *ce) {
    php_v8_object_template_t *php_v8_object_template;

    php_v8_object_template = (php_v8_object_template_t *) ecalloc(1, sizeof(php_v8_object_template_t) + zend_object_properties_size(ce));

    zend_object_std_init(&php_v8_object_template->std, ce);
    object_properties_init(&php_v8_object_template->std, ce);

    php_v8_object_template->persistent = new v8::Persistent<v8::ObjectTemplate>();
    php_v8_object_template->persistent_data = new phpv8::PersistentData();

    php_v8_object_template->node = new phpv8::TemplateNode();

    php_v8_object_template->std.handlers = &php_v8_object_template_object_handlers;

    return &php_v8_object_template->std;
}

static PHP_METHOD(ObjectTemplate, __construct) {
    zval *php_v8_isolate_zv;
    zval *php_v8_function_template_zv = NULL;
    v8::Local<v8::FunctionTemplate> constructor = v8::Local<v8::FunctionTemplate>();


    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o|o!", &php_v8_isolate_zv, &php_v8_function_template_zv) == FAILURE) {
        return;
    }

    PHP_V8_ISOLATE_FETCH_WITH_CHECK(php_v8_isolate_zv, php_v8_isolate);
    PHP_V8_OBJECT_TEMPLATE_FETCH_INTO(getThis(), php_v8_object_template);

    PHP_V8_TEMPLATE_STORE_ISOLATE(getThis(), php_v8_isolate_zv)
    PHP_V8_STORE_POINTER_TO_ISOLATE(php_v8_object_template, php_v8_isolate);

    PHP_V8_ENTER_ISOLATE(php_v8_isolate);

    if (php_v8_function_template_zv) {
        PHP_V8_FETCH_FUNCTION_TEMPLATE_WITH_CHECK(php_v8_function_template_zv, php_v8_function_template);
        PHP_V8_DATA_ISOLATES_CHECK(php_v8_object_template, php_v8_function_template);

        constructor = php_v8_function_template_get_local(php_v8_function_template);
    }

    v8::Local<v8::ObjectTemplate> local_obj_tpl = v8::ObjectTemplate::New(isolate, constructor);

    PHP_V8_THROW_VALUE_EXCEPTION_WHEN_EMPTY(local_obj_tpl, "Failed to create ObjectTemplate value");

    php_v8_object_template->persistent->Reset(isolate, local_obj_tpl);
}


static PHP_METHOD(ObjectTemplate, getIsolate) {
    zval rv;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_FETCH_OBJECT_TEMPLATE_WITH_CHECK(getThis(), php_v8_object_template);

    RETVAL_ZVAL(PHP_V8_TEMPLATE_READ_ISOLATE(getThis()), 1, 0);
}


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

static PHP_METHOD(ObjectTemplate, set) {
    php_v8_object_template_Set(INTERNAL_FUNCTION_PARAM_PASSTHRU);
}

static PHP_METHOD(ObjectTemplate, setAccessorProperty) {
    php_v8_object_template_SetAccessorProperty(INTERNAL_FUNCTION_PARAM_PASSTHRU);
}

static PHP_METHOD(ObjectTemplate, setNativeDataProperty) {
    php_v8_object_template_SetNativeDataProperty(INTERNAL_FUNCTION_PARAM_PASSTHRU);
}

static PHP_METHOD(ObjectTemplate, setLazyDataProperty) {
    php_v8_object_template_SetLazyDataProperty(INTERNAL_FUNCTION_PARAM_PASSTHRU);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


static PHP_METHOD(ObjectTemplate, newInstance) {
    zval *php_v8_context_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o", &php_v8_context_zv) == FAILURE) {
        return;
    }

    PHP_V8_FETCH_OBJECT_TEMPLATE_WITH_CHECK(getThis(), php_v8_object_template);
    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_object_template, php_v8_context);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_object_template);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    v8::Local<v8::ObjectTemplate> local_obj_tpl = php_v8_object_template_get_local(php_v8_object_template);

    v8::MaybeLocal<v8::Object> maybe_local_obj = local_obj_tpl->NewInstance(context);

    PHP_V8_THROW_VALUE_EXCEPTION_WHEN_EMPTY(maybe_local_obj, "Failed to create new instance from ObjectTemplate")

    v8::Local<v8::Object> local_obj = maybe_local_obj.ToLocalChecked();

    php_v8_get_or_create_value(return_value, local_obj, php_v8_context->php_v8_isolate);
}

static PHP_METHOD(ObjectTemplate, setAccessor) {
    zval *php_v8_name_zv;
    zval *php_v8_receiver_zv = NULL;

    zend_long attributes = 0;
    zend_long settings = 0;
    v8::Local<v8::AccessorSignature> signature;

    zend_fcall_info getter_fci = empty_fcall_info;
    zend_fcall_info_cache getter_fci_cache = empty_fcall_info_cache;

    zend_fcall_info setter_fci = empty_fcall_info;
    zend_fcall_info_cache setter_fci_cache = empty_fcall_info_cache;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "of|f!llo!",
                              &php_v8_name_zv,
                              &getter_fci, &getter_fci_cache,
                              &setter_fci, &setter_fci_cache,
                              &settings,
                              &attributes,
                              &php_v8_receiver_zv
    ) == FAILURE) {
        return;
    }

    PHP_V8_FETCH_OBJECT_TEMPLATE_WITH_CHECK(getThis(), php_v8_object_template);
    PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_name_zv, php_v8_name);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_object_template, php_v8_name);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_object_template);

    v8::Local<v8::ObjectTemplate> local_obj_tpl = php_v8_object_template_get_local(php_v8_object_template);

    attributes = attributes ? attributes & PHP_V8_PROPERTY_ATTRIBUTE_FLAGS : attributes;
    settings = settings ? settings & PHP_V8_ACCESS_CONTROL_FLAGS : settings;

    v8::Local<v8::Name> local_name = php_v8_value_get_local_as<v8::Name>(php_v8_name);

    PHP_V8_CONVERT_FROM_V8_STRING_TO_STRING(isolate, name, local_name);

    v8::AccessorNameGetterCallback getter;
    v8::AccessorNameSetterCallback setter = 0;
    v8::Local<v8::External> data;

    phpv8::CallbacksBucket *bucket = php_v8_object_template->persistent_data->bucket("accessor_",
                                                                                     local_name->IsSymbol(), name);
    data = v8::External::New(isolate, bucket);

    bucket->add(phpv8::CallbacksBucket::Index::Getter, getter_fci, getter_fci_cache);
    getter = php_v8_callback_accessor_name_getter;

    if (setter_fci.size) {
        bucket->add(phpv8::CallbacksBucket::Index::Setter, setter_fci, setter_fci_cache);
        setter = php_v8_callback_accessor_name_setter;
    }

    if (php_v8_receiver_zv) {
        PHP_V8_FETCH_FUNCTION_TEMPLATE_WITH_CHECK(php_v8_receiver_zv, php_v8_receiver);
        PHP_V8_DATA_ISOLATES_CHECK(php_v8_object_template, php_v8_receiver);

        signature = v8::AccessorSignature::New(isolate, php_v8_function_template_get_local(php_v8_receiver));
    }

    local_obj_tpl->SetAccessor(local_name,
                               getter,
                               setter,
                               data,
                               static_cast<v8::AccessControl>(settings),
                               static_cast<v8::PropertyAttribute>(attributes),
                               signature
    );
}

static PHP_METHOD(ObjectTemplate, setHandlerForNamedProperty) {
    zval *handler_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o", &handler_zv) == FAILURE) {
        return;
    }

    PHP_V8_FETCH_OBJECT_TEMPLATE_WITH_CHECK(getThis(), php_v8_object_template);
    PHP_V8_NAMED_PROPERTY_HANDLER_FETCH_WITH_CHECK(handler_zv, php_v8_handlers);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_object_template);

    v8::Local<v8::ObjectTemplate> local_obj_tpl = php_v8_object_template_get_local(php_v8_object_template);

    phpv8::CallbacksBucket *bucket = php_v8_object_template->persistent_data->bucket("named_handlers");
    bucket->reset(php_v8_handlers->bucket);

    v8::Local<v8::External> data = v8::External::New(isolate, bucket);

    local_obj_tpl->SetHandler(
            v8::NamedPropertyHandlerConfiguration(
                    php_v8_handlers->getter,
                    php_v8_handlers->setter,
                    php_v8_handlers->query,
                    php_v8_handlers->deleter,
                    php_v8_handlers->enumerator,
                    data,
                    static_cast<v8::PropertyHandlerFlags>(php_v8_handlers->flags)
            )
    );
}

static PHP_METHOD(ObjectTemplate, setHandlerForIndexedProperty) {
    zval *handler_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o", &handler_zv) == FAILURE) {
        return;
    }

    PHP_V8_FETCH_OBJECT_TEMPLATE_WITH_CHECK(getThis(), php_v8_object_template);
    PHP_V8_INDEXED_PROPERTY_HANDLER_FETCH_WITH_CHECK(handler_zv, php_v8_handlers);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_object_template);

    v8::Local<v8::ObjectTemplate> local_obj_tpl = php_v8_object_template_get_local(php_v8_object_template);

    phpv8::CallbacksBucket *bucket = php_v8_object_template->persistent_data->bucket("indexed_handlers");
    bucket->reset(php_v8_handlers->bucket);

    v8::Local<v8::External> data = v8::External::New(isolate, bucket);

    local_obj_tpl->SetHandler(
            v8::IndexedPropertyHandlerConfiguration(
                    php_v8_handlers->getter,
                    php_v8_handlers->setter,
                    php_v8_handlers->query,
                    php_v8_handlers->deleter,
                    php_v8_handlers->enumerator,
                    data,
                    static_cast<v8::PropertyHandlerFlags>(php_v8_handlers->flags)
            )
    );
}

static PHP_METHOD(ObjectTemplate, setCallAsFunctionHandler) {
    zend_fcall_info fci = empty_fcall_info;
    zend_fcall_info_cache fci_cache = empty_fcall_info_cache;

    v8::FunctionCallback callback = 0;
    v8::Local<v8::External> data;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "f!", &fci, &fci_cache) == FAILURE) {
        return;
    }

    PHP_V8_FETCH_OBJECT_TEMPLATE_WITH_CHECK(getThis(), php_v8_object_template);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_object_template);

    if (fci.size) {
        phpv8::CallbacksBucket *bucket = php_v8_object_template->persistent_data->bucket("callback");
        data = v8::External::New(isolate, bucket);

        bucket->add(phpv8::CallbacksBucket::Index::Callback, fci, fci_cache);

        callback = php_v8_callback_function;
    }

    v8::Local<v8::ObjectTemplate> local_template = php_v8_object_template_get_local(php_v8_object_template);

    local_template->SetCallAsFunctionHandler(callback, data);
}

static PHP_METHOD(ObjectTemplate, isImmutableProto) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_FETCH_OBJECT_TEMPLATE_WITH_CHECK(getThis(), php_v8_object_template);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_object_template);

    v8::Local<v8::ObjectTemplate> local_obj_tpl = php_v8_object_template_get_local(php_v8_object_template);

    RETURN_BOOL(static_cast<zend_bool>(local_obj_tpl->IsImmutableProto()));
}

static PHP_METHOD(ObjectTemplate, setImmutableProto) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_FETCH_OBJECT_TEMPLATE_WITH_CHECK(getThis(), php_v8_object_template);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_object_template);

    v8::Local<v8::ObjectTemplate> local_obj_tpl = php_v8_object_template_get_local(php_v8_object_template);

    local_obj_tpl->SetImmutableProto();
}


/* Non-standard, implementations of AdjustableExternalMemoryInterface::AdjustExternalAllocatedMemory */
static PHP_METHOD(ObjectTemplate, adjustExternalAllocatedMemory) {
    php_v8_ext_mem_interface_object_template_AdjustExternalAllocatedMemory(INTERNAL_FUNCTION_PARAM_PASSTHRU);
}

/* Non-standard, implementations of AdjustableExternalMemoryInterface::GetExternalAllocatedMemory */
static PHP_METHOD(ObjectTemplate, getExternalAllocatedMemory) {
    php_v8_ext_mem_interface_object_template_GetExternalAllocatedMemory(INTERNAL_FUNCTION_PARAM_PASSTHRU);
}


PHP_V8_ZEND_BEGIN_ARG_WITH_CONSTRUCTOR_INFO_EX(arginfo___construct, 1)
                ZEND_ARG_OBJ_INFO(0, isolate, V8\\Isolate, 0)
                ZEND_ARG_OBJ_INFO(0, constructor, V8\\FunctionTemplate, 1)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_getIsolate, ZEND_RETURN_VALUE, 0, V8\\Isolate, 0)
ZEND_END_ARG_INFO()

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_VOID_INFO_EX(arginfo_set, 2)
                ZEND_ARG_OBJ_INFO(0, name, V8\\NameValue, 0)
                ZEND_ARG_OBJ_INFO(0, value, V8\\Data, 0)
                ZEND_ARG_TYPE_INFO(0, attributes, IS_LONG, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_VOID_INFO_EX(arginfo_setAccessorProperty, 1)
                ZEND_ARG_OBJ_INFO(0, name, V8\\NameValue, 0)
                ZEND_ARG_OBJ_INFO(0, getter, V8\\FunctionTemplate, 0)
                ZEND_ARG_OBJ_INFO(0, setter, V8\\FunctionTemplate, 0)
                ZEND_ARG_TYPE_INFO(0, attributes, IS_LONG, 0)
                ZEND_ARG_TYPE_INFO(0, settings, IS_LONG, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_VOID_INFO_EX(arginfo_setNativeDataProperty, 2)
                ZEND_ARG_OBJ_INFO(0, name, V8\\NameValue, 0)
                ZEND_ARG_CALLABLE_INFO(0, getter, 0)
                ZEND_ARG_CALLABLE_INFO(0, setter, 1)
                ZEND_ARG_TYPE_INFO(0, attributes, IS_LONG, 0)
                ZEND_ARG_OBJ_INFO(0, receiver, V8\\FunctionTemplate, 1)
                ZEND_ARG_TYPE_INFO(0, settings, IS_LONG, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_VOID_INFO_EX(arginfo_setLazyDataProperty, 2)
                ZEND_ARG_OBJ_INFO(0, name, V8\\NameValue, 0)
                ZEND_ARG_CALLABLE_INFO(0, getter, 0)
                ZEND_ARG_TYPE_INFO(0, attributes, IS_LONG, 0)
ZEND_END_ARG_INFO()

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_newInstance, ZEND_RETURN_VALUE, 1, V8\\ObjectValue, 0)
                ZEND_ARG_OBJ_INFO(0, value, V8\\Context, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_VOID_INFO_EX(arginfo_setAccessor, 2)
                ZEND_ARG_OBJ_INFO(0, name, V8\\NameValue, 0)
                ZEND_ARG_CALLABLE_INFO(0, getter, 0)
                ZEND_ARG_CALLABLE_INFO(0, setter, 1)
                ZEND_ARG_TYPE_INFO(0, settings, IS_LONG, 0)
                ZEND_ARG_TYPE_INFO(0, attributes, IS_LONG, 0)
                ZEND_ARG_OBJ_INFO(0, receiver, V8\\FunctionTemplate, 1)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_VOID_INFO_EX(arginfo_setHandlerForNamedProperty, 1)
                ZEND_ARG_OBJ_INFO(0, configuration, V8\\NamedPropertyHandlerConfiguration, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_VOID_INFO_EX(arginfo_setHandlerForIndexedProperty, 1)
                ZEND_ARG_OBJ_INFO(0, configuration, V8\\IndexedPropertyHandlerConfiguration, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_VOID_INFO_EX(arginfo_setCallAsFunctionHandler, 1)
                ZEND_ARG_INFO(0, callback)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_isImmutableProto, ZEND_RETURN_VALUE, 0, _IS_BOOL, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_VOID_INFO_EX(arginfo_setImmutableProto, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_adjustExternalAllocatedMemory, ZEND_RETURN_VALUE, 1, IS_LONG, 0)
                ZEND_ARG_TYPE_INFO(0, change_in_bytes, IS_LONG, 0)
ZEND_END_ARG_INFO()


PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_getExternalAllocatedMemory, ZEND_RETURN_VALUE, 0, IS_LONG, 0)
ZEND_END_ARG_INFO()


static const zend_function_entry php_v8_object_template_methods[] = {
        PHP_V8_ME(ObjectTemplate, __construct,                   ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)
        PHP_V8_ME(ObjectTemplate, getIsolate,                    ZEND_ACC_PUBLIC)
        PHP_V8_ME(ObjectTemplate, set,                           ZEND_ACC_PUBLIC)
        PHP_V8_ME(ObjectTemplate, setAccessorProperty,           ZEND_ACC_PUBLIC)
        PHP_V8_ME(ObjectTemplate, setNativeDataProperty,         ZEND_ACC_PUBLIC)
        PHP_V8_ME(ObjectTemplate, setLazyDataProperty,           ZEND_ACC_PUBLIC)
        PHP_V8_ME(ObjectTemplate, newInstance,                   ZEND_ACC_PUBLIC)
        PHP_V8_ME(ObjectTemplate, setAccessor,                   ZEND_ACC_PUBLIC)
        PHP_V8_ME(ObjectTemplate, setHandlerForNamedProperty,    ZEND_ACC_PUBLIC)
        PHP_V8_ME(ObjectTemplate, setHandlerForIndexedProperty,  ZEND_ACC_PUBLIC)
        PHP_V8_ME(ObjectTemplate, setCallAsFunctionHandler,      ZEND_ACC_PUBLIC)
        PHP_V8_ME(ObjectTemplate, isImmutableProto,              ZEND_ACC_PUBLIC)
        PHP_V8_ME(ObjectTemplate, setImmutableProto,             ZEND_ACC_PUBLIC)
        PHP_V8_ME(ObjectTemplate, adjustExternalAllocatedMemory, ZEND_ACC_PUBLIC)
        PHP_V8_ME(ObjectTemplate, getExternalAllocatedMemory,    ZEND_ACC_PUBLIC)

        PHP_FE_END
};


PHP_MINIT_FUNCTION (php_v8_object_template) {
    zend_class_entry ce;

    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS, "ObjectTemplate", php_v8_object_template_methods);
    this_ce = zend_register_internal_class_ex(&ce, php_v8_template_ce);
    zend_class_implements(this_ce, 1, php_v8_ext_mem_interface_ce);
    this_ce->create_object = php_v8_object_template_ctor;

    memcpy(&php_v8_object_template_object_handlers, zend_get_std_object_handlers(), sizeof(zend_object_handlers));

    php_v8_object_template_object_handlers.offset    = XtOffsetOf(php_v8_object_template_t, std);
    php_v8_object_template_object_handlers.free_obj  = php_v8_object_template_free;
    php_v8_object_template_object_handlers.get_gc    = php_v8_object_template_gc;
    php_v8_object_template_object_handlers.clone_obj = NULL;

    return SUCCESS;
}
