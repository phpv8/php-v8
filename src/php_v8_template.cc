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

#include "php_v8_template.h"
#include "php_v8_object_template.h"
#include "php_v8_function_template.h"
#include "php_v8_name.h"
#include "php_v8_data.h"
#include "php_v8_value.h"
#include "php_v8_enums.h"
#include "php_v8.h"

zend_class_entry *php_v8_template_ce;
#define this_ce php_v8_template_ce


template<class T, typename N> void php_v8_template_Set(v8::Isolate *isolate, v8::Local<T> local_template, N* php_v8_template, INTERNAL_FUNCTION_PARAMETERS);
template<class T, typename N> void php_v8_template_SetAccessorProperty(v8::Isolate *isolate, v8::Local<T> local_template, N* php_v8_template, INTERNAL_FUNCTION_PARAMETERS);
template<class T, typename N> void php_v8_template_SetNativeDataProperty(v8::Isolate *isolate, v8::Local<T> local_template, N* php_v8_template, INTERNAL_FUNCTION_PARAMETERS);


void php_v8_object_template_Set(INTERNAL_FUNCTION_PARAMETERS) {

    PHP_V8_FETCH_OBJECT_TEMPLATE_WITH_CHECK(getThis(), php_v8_template);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_template);

    v8::Local<v8::ObjectTemplate> local_template = php_v8_object_template_get_local(php_v8_template);

    php_v8_template_Set(isolate, local_template, php_v8_template, INTERNAL_FUNCTION_PARAM_PASSTHRU);
}

void php_v8_function_template_Set(INTERNAL_FUNCTION_PARAMETERS)
{
    PHP_V8_FETCH_FUNCTION_TEMPLATE_WITH_CHECK(getThis(), php_v8_template);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_template);

    v8::Local<v8::FunctionTemplate> local_template = php_v8_function_template_get_local(php_v8_template);

    php_v8_template_Set(isolate, local_template, php_v8_template, INTERNAL_FUNCTION_PARAM_PASSTHRU);
}

void php_v8_object_template_SetAccessorProperty(INTERNAL_FUNCTION_PARAMETERS) {

    PHP_V8_FETCH_OBJECT_TEMPLATE_WITH_CHECK(getThis(), php_v8_template);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_template);

    v8::Local<v8::ObjectTemplate> local_template = php_v8_object_template_get_local(php_v8_template);

    php_v8_template_SetAccessorProperty(isolate, local_template, php_v8_template, INTERNAL_FUNCTION_PARAM_PASSTHRU);
}

void php_v8_function_template_SetAccessorProperty(INTERNAL_FUNCTION_PARAMETERS)
{
    PHP_V8_FETCH_FUNCTION_TEMPLATE_WITH_CHECK(getThis(), php_v8_template);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_template);

    v8::Local<v8::FunctionTemplate> local_template = php_v8_function_template_get_local(php_v8_template);

    php_v8_template_SetAccessorProperty(isolate, local_template, php_v8_template, INTERNAL_FUNCTION_PARAM_PASSTHRU);
}


void php_v8_object_template_SetNativeDataProperty(INTERNAL_FUNCTION_PARAMETERS) {

    PHP_V8_FETCH_OBJECT_TEMPLATE_WITH_CHECK(getThis(), php_v8_template);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_template);

    v8::Local<v8::ObjectTemplate> local_template = php_v8_object_template_get_local(php_v8_template);

    php_v8_template_SetNativeDataProperty(isolate, local_template, php_v8_template, INTERNAL_FUNCTION_PARAM_PASSTHRU);
}

void php_v8_function_template_SetNativeDataProperty(INTERNAL_FUNCTION_PARAMETERS)
{
    PHP_V8_FETCH_FUNCTION_TEMPLATE_WITH_CHECK(getThis(), php_v8_template);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_template);

    v8::Local<v8::FunctionTemplate> local_template = php_v8_function_template_get_local(php_v8_template);

    php_v8_template_SetNativeDataProperty(isolate, local_template, php_v8_template, INTERNAL_FUNCTION_PARAM_PASSTHRU);
}

template<typename M, typename N>
static inline bool php_v8_template_node_set(M *parent, N *child) {
    if (parent->node->isSelf(child->node)) {
        PHP_V8_THROW_EXCEPTION("Can't set: recursion detected");
        return false;
    }

    if (parent->node->isParent(child->node)) {
        PHP_V8_THROW_EXCEPTION("Can't set: recursion detected");
        return false;
    }

    parent->node->addChild(child->node);
    return true;
}

template<class T, typename N>
void php_v8_template_Set(v8::Isolate *isolate, v8::Local<T> local_template, N* php_v8_template, INTERNAL_FUNCTION_PARAMETERS) {
    zval *php_v8_value_zv;
    zval *php_v8_name_zv;

    zend_long attributes = v8::PropertyAttribute::None;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "oo|l", &php_v8_name_zv, &php_v8_value_zv, &attributes) ==
        FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_name_zv, php_v8_name);
    PHP_V8_DATA_ISOLATES_CHECK(php_v8_template, php_v8_name);

    attributes = attributes ? attributes & PHP_V8_PROPERTY_ATTRIBUTE_FLAGS : attributes;

    v8::Local<v8::Name> local_name = php_v8_value_get_local_as<v8::Name>(php_v8_name);

    if (instanceof_function(Z_OBJCE_P(php_v8_value_zv), php_v8_value_class_entry)) {
        // set persistent
        PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_value_zv, php_v8_value_to_set);

        PHP_V8_DATA_ISOLATES_CHECK(php_v8_template, php_v8_value_to_set);

        v8::Local<v8::Value> local_value = php_v8_value_get_local(php_v8_value_to_set);

        if (local_value->IsObject()) {
            int arg_position = 3;
            zend_string *ce_name = zend_get_executed_scope()->name;

            zend_throw_error(zend_ce_type_error,
                             "Argument %d passed to %s::%s() should be instance of \\V8\\PrimitiveValue or \\V8\\Template, instance of %s given",
                             arg_position, ZSTR_VAL(ce_name), get_active_function_name(), ZSTR_VAL(Z_OBJCE_P(php_v8_value_zv)->name));
            return;
        }

        local_template->Set(local_name, local_value, static_cast<v8::PropertyAttribute>(attributes));
    } else if (instanceof_function(Z_OBJCE_P(php_v8_value_zv), php_v8_object_template_class_entry)) {
        // set object template
        PHP_V8_FETCH_OBJECT_TEMPLATE_WITH_CHECK(php_v8_value_zv, php_v8_object_template_to_set);

        PHP_V8_DATA_ISOLATES_CHECK(php_v8_template, php_v8_object_template_to_set);

        if (php_v8_template_node_set(php_v8_template, php_v8_object_template_to_set)) {
            local_template->Set(local_name, php_v8_object_template_get_local(php_v8_object_template_to_set), static_cast<v8::PropertyAttribute>(attributes));
        }

    } else if (instanceof_function(Z_OBJCE_P(php_v8_value_zv), php_v8_function_template_class_entry)) {
        // set function template
        PHP_V8_FETCH_FUNCTION_TEMPLATE_WITH_CHECK(php_v8_value_zv, php_v8_function_template_to_set);

        PHP_V8_DATA_ISOLATES_CHECK(php_v8_template, php_v8_function_template_to_set);

        if (php_v8_template_node_set(php_v8_template, php_v8_function_template_to_set)) {
            local_template->Set(local_name, php_v8_function_template_get_local(php_v8_function_template_to_set), static_cast<v8::PropertyAttribute>(attributes));
        }
    } else {
        // should never get here
        PHP_V8_THROW_EXCEPTION("Unknown type to set");
    }
}

template<class T, typename N>
void php_v8_template_SetAccessorProperty(v8::Isolate *isolate, v8::Local<T> local_template, N* php_v8_template, INTERNAL_FUNCTION_PARAMETERS) {
    zval *getter_zv;
    zval *setter_zv;
    zval *php_v8_name_zv;

    zend_long attributes = v8::PropertyAttribute::None;
    zend_long settings = v8::AccessControl::DEFAULT;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o|ooll", &php_v8_name_zv, &getter_zv, &setter_zv, &attributes, &settings) ==
        FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_name_zv, php_v8_name);
    PHP_V8_DATA_ISOLATES_CHECK(php_v8_template, php_v8_name);

    attributes = attributes ? attributes & PHP_V8_PROPERTY_ATTRIBUTE_FLAGS : attributes;
    settings = settings ? settings & PHP_V8_ACCESS_CONTROL_FLAGS : settings;

    v8::Local<v8::Name> local_name = php_v8_value_get_local_as<v8::Name>(php_v8_name);


    v8::Local<v8::FunctionTemplate> getter;
    v8::Local<v8::FunctionTemplate> setter;

    if (Z_TYPE_P(getter_zv) != IS_NULL) {

        PHP_V8_FETCH_FUNCTION_TEMPLATE_WITH_CHECK(getter_zv, php_v8_getter);

        PHP_V8_DATA_ISOLATES_CHECK(php_v8_template, php_v8_getter);

        getter = php_v8_function_template_get_local(php_v8_getter);
    }

    if (Z_TYPE_P(setter_zv) != IS_NULL) {

        PHP_V8_FETCH_FUNCTION_TEMPLATE_WITH_CHECK(setter_zv, php_v8_setter);

        PHP_V8_DATA_ISOLATES_CHECK(php_v8_template, php_v8_setter);

        setter = php_v8_function_template_get_local(php_v8_setter);
    }

    local_template->SetAccessorProperty(local_name, getter, setter, static_cast<v8::PropertyAttribute>(attributes), static_cast<v8::AccessControl>(settings));
}

template<class T, typename N>
void php_v8_template_SetNativeDataProperty(v8::Isolate *isolate, v8::Local<T> local_template, N* php_v8_template, INTERNAL_FUNCTION_PARAMETERS) {
    zval *php_v8_name_zv;
    zval *php_v8_receiver_zv = NULL;

    zend_fcall_info getter_fci = empty_fcall_info;
    zend_fcall_info_cache getter_fci_cache = empty_fcall_info_cache;

    zend_fcall_info setter_fci = empty_fcall_info;
    zend_fcall_info_cache setter_fci_cache = empty_fcall_info_cache;

    v8::AccessorNameGetterCallback getter;
    v8::AccessorNameSetterCallback setter = 0;

    zend_long attributes = v8::PropertyAttribute::None;
    v8::Local<v8::AccessorSignature> signature;
    zend_long settings = v8::AccessControl::DEFAULT;

    v8::Local<v8::External> data;

    if (zend_parse_parameters(ZEND_NUM_ARGS(),
                              "of|f!lo!l",
                              &php_v8_name_zv, &getter_fci, &getter_fci_cache, &setter_fci, &setter_fci_cache,
                              &attributes, &php_v8_receiver_zv, &settings
                             ) == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_name_zv, php_v8_name);
    PHP_V8_DATA_ISOLATES_CHECK(php_v8_template, php_v8_name);

    attributes = attributes ? attributes & PHP_V8_PROPERTY_ATTRIBUTE_FLAGS : attributes;
    settings = settings ? settings & PHP_V8_ACCESS_CONTROL_FLAGS : settings;

    v8::Local<v8::Name> local_name = php_v8_value_get_local_as<v8::Name>(php_v8_name);

    PHP_V8_CONVERT_FROM_V8_STRING_TO_STRING(name, local_name);

    phpv8::CallbacksBucket *bucket = php_v8_template->persistent_data->bucket("native_data_property_", local_name->IsSymbol(), name);
    data = v8::External::New(isolate, bucket);

    bucket->add(phpv8::CallbacksBucket::Index::Getter, getter_fci, getter_fci_cache);
    getter = php_v8_callback_accessor_name_getter;

    if (setter_fci.size) {
        bucket->add(phpv8::CallbacksBucket::Index::Setter, setter_fci, setter_fci_cache);
        setter = php_v8_callback_accessor_name_setter;
    }

    if (php_v8_receiver_zv) {
        PHP_V8_FETCH_FUNCTION_TEMPLATE_WITH_CHECK(php_v8_receiver_zv, php_v8_receiver);
        PHP_V8_DATA_ISOLATES_CHECK(php_v8_template, php_v8_receiver);

        signature = v8::AccessorSignature::New(isolate, php_v8_function_template_get_local(php_v8_receiver));
    }

    local_template->SetNativeDataProperty(local_name,
                                          getter,
                                          setter,
                                          data,
                                          static_cast<v8::PropertyAttribute>(attributes),
                                          signature,
                                          static_cast<v8::AccessControl>(settings));
}


PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_v8_template_GetIsolate, ZEND_RETURN_VALUE, 0, V8\\Isolate, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_php_v8_template_Set, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 2)
                ZEND_ARG_OBJ_INFO(0, name, V8\\NameValue, 0)
                ZEND_ARG_OBJ_INFO(0, value, V8\\Data, 0)
                ZEND_ARG_TYPE_INFO(0, attributes, IS_LONG, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_php_v8_template_SetAccessorProperty, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 1)
                ZEND_ARG_OBJ_INFO(0, name, V8\\NameValue, 0)
                ZEND_ARG_OBJ_INFO(0, getter, V8\\FunctionTemplate, 0)
                ZEND_ARG_OBJ_INFO(0, setter, V8\\FunctionTemplate, 0)
                ZEND_ARG_TYPE_INFO(0, attributes, IS_LONG, 0)
                ZEND_ARG_TYPE_INFO(0, settings, IS_LONG, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_php_v8_template_SetNativeDataProperty, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 2)
                ZEND_ARG_OBJ_INFO(0, name, V8\\NameValue, 0)
                ZEND_ARG_CALLABLE_INFO(0, getter, 0)
                ZEND_ARG_CALLABLE_INFO(0, setter, 1)
                ZEND_ARG_TYPE_INFO(0, attributes, IS_LONG, 0)
                ZEND_ARG_OBJ_INFO(0, receiver, V8\\FunctionTemplate, 1)
                ZEND_ARG_TYPE_INFO(0, settings, IS_LONG, 0)
ZEND_END_ARG_INFO()

static const zend_function_entry php_v8_template_methods[] = {
        PHP_ABSTRACT_ME(V8Template, GetIsolate, arginfo_v8_template_GetIsolate)
        PHP_ABSTRACT_ME(V8Template, Set, arginfo_php_v8_template_Set)
        PHP_ABSTRACT_ME(V8Template, SetAccessorProperty, arginfo_php_v8_template_SetAccessorProperty)
        PHP_ABSTRACT_ME(V8Template, SetNativeDataProperty, arginfo_php_v8_template_SetNativeDataProperty)

        PHP_FE_END
};

PHP_MINIT_FUNCTION (php_v8_template) {
    zend_class_entry ce;

    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS, "Template", php_v8_template_methods);
    this_ce = zend_register_internal_class_ex(&ce, php_v8_data_class_entry);

    zend_declare_property_null(this_ce, ZEND_STRL("isolate"), ZEND_ACC_PRIVATE);

    return SUCCESS;
}
