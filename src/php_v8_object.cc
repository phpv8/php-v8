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

#include "php_v8_object.h"
#include "php_v8_integrity_level.h"
#include "php_v8_exceptions.h"
#include "php_v8_function_template.h"
#include "php_v8_function.h"
#include "php_v8_property_attribute.h"
#include "php_v8_access_control.h"
#include "php_v8_string.h"
#include "php_v8_uint32.h"
#include "php_v8_name.h"
#include "php_v8_value.h"
#include "php_v8_context.h"
#include "php_v8_ext_mem_interface.h"
#include "php_v8.h"


zend_class_entry *php_v8_object_class_entry;
#define this_ce php_v8_object_class_entry



bool php_v8_object_delete_self_ptr(php_v8_value_t *php_v8_value, v8::Local<v8::Object> local_object) {
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);
    PHP_V8_CONTEXT_ENTER(local_object->CreationContext());

    v8::Local<v8::Private> key = php_v8_isolate_get_key_local(php_v8_value->php_v8_isolate);
    assert(!key.IsEmpty());

    v8::Maybe<bool> maybe_res = local_object->DeletePrivate(local_object->CreationContext(), key);

    // TODO: in obj free handle we may want not to throw any exceptions
    if (maybe_res.IsNothing()) {
        PHP_V8_THROW_EXCEPTION("Internal error: Failed to delete hidden persistent");
        return false;
    }

    assert(maybe_res.FromJust());
    return maybe_res.FromMaybe(false);
}

bool php_v8_object_store_self_ptr(php_v8_value_t *php_v8_value, v8::Local<v8::Object> local_object)
{
    assert(NULL != v8::Isolate::GetCurrent());
    assert(v8::Isolate::GetCurrent()->InContext());

    v8::Local<v8::Private> key = php_v8_isolate_get_key_local(php_v8_value->php_v8_isolate);
    assert(!key.IsEmpty());

    v8::Local<v8::External> this_embedded = v8::External::New(php_v8_value->php_v8_isolate->isolate, php_v8_value);

    v8::Maybe<bool> maybe_res = local_object->SetPrivate(local_object->CreationContext(), key, this_embedded);

    if (maybe_res.IsNothing()) {
        PHP_V8_THROW_EXCEPTION("Internal error: Failed to delete hidden persistent");
        return false;
    }

    assert(maybe_res.FromJust());

    return maybe_res.FromMaybe(false);
}

php_v8_value_t * php_v8_object_get_self_ptr(php_v8_isolate_t *php_v8_isolate, v8::Local<v8::Object> local_object)
{
    assert(NULL != v8::Isolate::GetCurrent());
    assert(v8::Isolate::GetCurrent()->InContext());

    v8::Local<v8::Private> key = php_v8_isolate_get_key_local(php_v8_isolate);
    assert(!key.IsEmpty());

    v8::MaybeLocal<v8::Value> maybe_local_value = local_object->GetPrivate(local_object->CreationContext(), key);

    if (maybe_local_value.IsEmpty()) {
        return NULL;
    }

    v8::Local<v8::Value> local_value = maybe_local_value.ToLocalChecked();

    // for some reason this check fails, but value IS external
    //assert(local_value->IsExternal());

    return static_cast<php_v8_value_t *>(local_value.As<v8::External>()->Value());
}


static PHP_METHOD(V8Object, __construct) {
    zval rv;
    zval *php_v8_context_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o", &php_v8_context_zv) == FAILURE) {
        return;
    }

    PHP_V8_OBJECT_CONSTRUCT(getThis(), php_v8_context_zv, php_v8_context, php_v8_value);

    v8::Local<v8::Object> local_object = v8::Object::New(isolate);

    PHP_V8_THROW_VALUE_EXCEPTION_WHEN_EMPTY(local_object, "Failed to create Object value");

    php_v8_object_store_self_ptr(php_v8_value, local_object);

    php_v8_value->persistent->Reset(isolate, local_object);
}

static PHP_METHOD(V8Object, GetContext) {
    zval rv;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);

    RETURN_ZVAL(PHP_V8_OBJECT_READ_CONTEXT(getThis()), 1, 0);
}

static PHP_METHOD(V8Object, Set) {
    zval *php_v8_context_zv;
    zval *php_v8_key_or_index_zv;
    zval *php_v8_value_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "ooo", &php_v8_context_zv, &php_v8_key_or_index_zv, &php_v8_value_zv) == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_key_or_index_zv, php_v8_key_or_index);
    PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_value_zv, php_v8_value_value_to_set);
    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_context);
    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_key_or_index);
    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_value_value_to_set);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    v8::Local<v8::Object> local_obj = php_v8_value_get_local_as<v8::Object>(php_v8_value);
    v8::Local<v8::Value> local_key_or_index = php_v8_value_get_local(php_v8_key_or_index);
    v8::Local<v8::Value> local_value_to_set = php_v8_value_get_local(php_v8_value_value_to_set);

    PHP_V8_TRY_CATCH(isolate);
    PHP_V8_INIT_ISOLATE_LIMITS_ON_OBJECT_VALUE(php_v8_value);

    v8::Maybe<bool> maybe_res = local_obj->Set(context, local_key_or_index, local_value_to_set);

    PHP_V8_MAYBE_CATCH(php_v8_context, try_catch);
    PHP_V8_THROW_EXCEPTION_WHEN_NOTHING(maybe_res, "Failed to set");

    RETURN_BOOL(maybe_res.FromJust());
}

static PHP_METHOD(V8Object, CreateDataProperty) {
    zval *php_v8_context_zv;
    zval *php_v8_key_or_index_zv;
    zval *php_v8_value_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "ooo", &php_v8_context_zv, &php_v8_key_or_index_zv, &php_v8_value_zv) == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_key_or_index_zv, php_v8_key_or_index);
    PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_value_zv, php_v8_value_value_to_set);
    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_context);
    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_key_or_index);
    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_value_value_to_set);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    v8::Local<v8::Object> local_obj = php_v8_value_get_local_as<v8::Object>(php_v8_value);
    v8::Local<v8::Name> local_key_or_index = php_v8_value_get_local_as<v8::Name>(php_v8_key_or_index);
    v8::Local<v8::Value> local_value_to_set = php_v8_value_get_local(php_v8_value_value_to_set);

    PHP_V8_TRY_CATCH(isolate);
    PHP_V8_INIT_ISOLATE_LIMITS_ON_OBJECT_VALUE(php_v8_value);

    v8::Maybe<bool> maybe_res = local_obj->CreateDataProperty(context, local_key_or_index, local_value_to_set);

    PHP_V8_MAYBE_CATCH(php_v8_context, try_catch);
    PHP_V8_THROW_EXCEPTION_WHEN_NOTHING(maybe_res, "Failed to create data property");

    RETURN_BOOL(maybe_res.FromJust());
}

static PHP_METHOD(V8Object, DefineOwnProperty) {
    zval *php_v8_context_zv;
    zval *php_v8_value_zv;
    zval *php_v8_key_zv;

    zend_long attributes = 0;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "ooo|l", &php_v8_context_zv, &php_v8_key_zv, &php_v8_value_zv, &attributes) == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_key_zv, php_v8_key);

    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);
    PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_value_zv, php_v8_value_value_to_set);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_context);
    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_key);
    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_value_value_to_set);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    v8::Local<v8::Value> local_value_to_set = php_v8_value_get_local(php_v8_value_value_to_set);
    v8::Local<v8::Object> local_obj = php_v8_value_get_local_as<v8::Object>(php_v8_value);

    v8::Local<v8::Name> local_name = php_v8_value_get_local_as<v8::Name>(php_v8_key);

    attributes = attributes ? attributes & PHP_V8_PROPERTY_ATTRIBUTE_FLAGS : attributes;

    PHP_V8_TRY_CATCH(isolate);
    PHP_V8_INIT_ISOLATE_LIMITS_ON_OBJECT_VALUE(php_v8_value);

    v8::Maybe<bool> maybe = local_obj->DefineOwnProperty(context,
                                                         local_name,
                                                         local_value_to_set,
                                                         static_cast<v8::PropertyAttribute>(attributes));

    PHP_V8_MAYBE_CATCH(php_v8_context, try_catch);
    PHP_V8_THROW_EXCEPTION_WHEN_NOTHING(maybe, "Failed to define own property");

    RETURN_BOOL(maybe.FromJust())
}

static PHP_METHOD(V8Object, Get) {
    zval *php_v8_context_zv;
    zval *php_v8_key_or_index_zv;
    v8::MaybeLocal<v8::Value> maybe_local;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "oo", &php_v8_context_zv, &php_v8_key_or_index_zv) == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_key_or_index_zv, php_v8_key_or_index);
    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_context);
    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_key_or_index);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    v8::Local<v8::Object> local_obj = php_v8_value_get_local_as<v8::Object>(php_v8_value);
    v8::Local<v8::Value> local_key_or_index = php_v8_value_get_local(php_v8_key_or_index);

    PHP_V8_TRY_CATCH(isolate);
    PHP_V8_INIT_ISOLATE_LIMITS_ON_OBJECT_VALUE(php_v8_value);

    maybe_local = local_obj->Get(context, local_key_or_index);

    PHP_V8_MAYBE_CATCH(php_v8_context, try_catch);
    PHP_V8_THROW_EXCEPTION_WHEN_EMPTY(maybe_local, "Failed to get");

    v8::Local<v8::Value> local_value =  maybe_local.ToLocalChecked();

    php_v8_get_or_create_value(return_value, local_value, php_v8_value->php_v8_isolate);
}

static PHP_METHOD(V8Object, GetPropertyAttributes) {
    zval *php_v8_context_zv;
    zval *php_v8_string_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "oo", &php_v8_context_zv, &php_v8_string_zv) == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_string_zv, php_v8_string);
    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_context)
    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_string)

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    v8::Local<v8::Object> local_obj = php_v8_value_get_local_as<v8::Object>(php_v8_value);
    v8::Local<v8::String> local_string = php_v8_value_get_local_as<v8::String>(php_v8_string);

    PHP_V8_TRY_CATCH(isolate);
    PHP_V8_INIT_ISOLATE_LIMITS_ON_OBJECT_VALUE(php_v8_value);

    v8::Maybe<v8::PropertyAttribute> maybe = local_obj->GetPropertyAttributes(context, local_string);

    PHP_V8_MAYBE_CATCH(php_v8_context, try_catch);
    PHP_V8_THROW_EXCEPTION_WHEN_NOTHING(maybe, "Failed to get property attributes");

    RETURN_LONG(static_cast<zend_long>(maybe.FromJust()));
}

static PHP_METHOD(V8Object, GetOwnPropertyDescriptor) {
    zval *php_v8_context_zv;
    zval *php_v8_string_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "oo", &php_v8_context_zv, &php_v8_string_zv) == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_string_zv, php_v8_string);
    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_context)
    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_string)

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    v8::Local<v8::Object> local_obj = php_v8_value_get_local_as<v8::Object>(php_v8_value);
    v8::Local<v8::String> local_string = php_v8_value_get_local_as<v8::String>(php_v8_string);

    PHP_V8_TRY_CATCH(isolate);
    PHP_V8_INIT_ISOLATE_LIMITS_ON_OBJECT_VALUE(php_v8_value);

    v8::MaybeLocal<v8::Value> maybe_local = local_obj->GetOwnPropertyDescriptor(context, local_string);

    PHP_V8_MAYBE_CATCH(php_v8_context, try_catch);
    PHP_V8_THROW_EXCEPTION_WHEN_EMPTY(maybe_local, "Failed to get property descriptor");

    v8::Local<v8::Value> local_value = maybe_local.ToLocalChecked();

    php_v8_get_or_create_value(return_value, local_value, php_v8_value->php_v8_isolate);
}

static PHP_METHOD(V8Object, Has) {
    zval *php_v8_context_zv;
    zval *php_v8_key_or_index_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "oo", &php_v8_context_zv, &php_v8_key_or_index_zv) == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_key_or_index_zv, php_v8_key_or_index);
    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_context);
    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_key_or_index);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    v8::Local<v8::Object> local_obj = php_v8_value_get_local_as<v8::Object>(php_v8_value);
    v8::Local<v8::Value> local_key_or_index = php_v8_value_get_local(php_v8_key_or_index);

    PHP_V8_TRY_CATCH(isolate);
    PHP_V8_INIT_ISOLATE_LIMITS_ON_OBJECT_VALUE(php_v8_value);

    v8::Maybe<bool> maybe_res = local_obj->Has(context, local_key_or_index);

    PHP_V8_MAYBE_CATCH(php_v8_context, try_catch);
    PHP_V8_THROW_EXCEPTION_WHEN_NOTHING(maybe_res, "Failed to check");

    RETURN_BOOL(maybe_res.FromJust());
}

static PHP_METHOD(V8Object, Delete) {
    zval *php_v8_context_zv;
    zval *php_v8_key_or_index_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "oo", &php_v8_context_zv, &php_v8_key_or_index_zv) == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_key_or_index_zv, php_v8_key_or_index);
    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_context);
    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_key_or_index);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    v8::Local<v8::Object> local_obj = php_v8_value_get_local_as<v8::Object>(php_v8_value);
    v8::Local<v8::Value> local_key_or_index = php_v8_value_get_local(php_v8_key_or_index);

    PHP_V8_TRY_CATCH(isolate);
    PHP_V8_INIT_ISOLATE_LIMITS_ON_OBJECT_VALUE(php_v8_value);

    v8::Maybe<bool> maybe_res = local_obj->Delete(context, local_key_or_index);

    PHP_V8_MAYBE_CATCH(php_v8_context, try_catch);
    PHP_V8_THROW_EXCEPTION_WHEN_NOTHING(maybe_res, "Failed to delete");

    RETURN_BOOL(maybe_res.FromJust());
}

static PHP_METHOD(V8Object, SetAccessor) {
    zval *context_zv;
    zval *php_v8_name_zv;

    zend_long attributes = 0;
    zend_long settings = 0;

    zend_fcall_info getter_fci = empty_fcall_info;
    zend_fcall_info_cache getter_fci_cache = empty_fcall_info_cache;

    zend_fcall_info setter_fci = empty_fcall_info;
    zend_fcall_info_cache setter_fci_cache = empty_fcall_info_cache;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "oof|f!ll",
                              &context_zv,
                              &php_v8_name_zv,
                              &getter_fci, &getter_fci_cache,
                              &setter_fci, &setter_fci_cache,
                              &settings,
                              &attributes
                             ) == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_name_zv, php_v8_name);
    PHP_V8_CONTEXT_FETCH_WITH_CHECK(context_zv, php_v8_context);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_context)
    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_name)

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    v8::Local<v8::Object> local_object = php_v8_value_get_local_as<v8::Object>(php_v8_value);
    v8::Local<v8::Name> local_name = php_v8_value_get_local_as<v8::Name>(php_v8_name);

    PHP_V8_CONVERT_FROM_V8_STRING_TO_STRING(name, local_name);

    attributes = attributes ? attributes & PHP_V8_PROPERTY_ATTRIBUTE_FLAGS : attributes;
    settings = settings ? settings & PHP_V8_ACCESS_CONTROL_FLAGS : settings;


    v8::AccessorNameGetterCallback getter;
    v8::AccessorNameSetterCallback setter = 0;
    v8::Local<v8::External> data;


    phpv8::CallbacksBucket *bucket = php_v8_value->persistent_data->bucket("accessor_", local_name->IsSymbol(), name);
    data = v8::External::New(isolate, bucket);

    bucket->add(0, getter_fci, getter_fci_cache);
    getter = php_v8_callback_accessor_name_getter;

    if (setter_fci.size) {
        bucket->add(1, setter_fci, setter_fci_cache);
        setter = php_v8_callback_accessor_name_setter;
    }

    v8::Maybe<bool> maybe_res = local_object->SetAccessor(context,
                                                          local_name,
                                                          getter,
                                                          setter,
                                                          data,
                                                          static_cast<v8::AccessControl>(settings),
                                                          static_cast<v8::PropertyAttribute>(attributes)
    );

    PHP_V8_THROW_EXCEPTION_WHEN_NOTHING(maybe_res, "Failed to set");

    RETURN_BOOL(maybe_res.FromJust());
}

static PHP_METHOD(V8Object, SetAccessorProperty) {
    zval *php_v8_name_zv;
    zval *getter_zv;
    zval *setter_zv;

    zend_long attributes = 0;
    zend_long settings = 0;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "oo|oll", &php_v8_name_zv, &getter_zv, &setter_zv, &attributes, &settings) == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_name_zv, php_v8_name);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_name)

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);
    PHP_V8_ENTER_STORED_CONTEXT(php_v8_value);

    v8::Local<v8::Name> local_name = php_v8_value_get_local_as<v8::Name>(php_v8_name);

    attributes = attributes ? attributes & PHP_V8_PROPERTY_ATTRIBUTE_FLAGS : attributes;
    settings = settings ? settings & PHP_V8_ACCESS_CONTROL_FLAGS : settings;

    v8::Local<v8::Function> getter;
    v8::Local<v8::Function> setter;

    PHP_V8_VALUE_FETCH_WITH_CHECK(getter_zv, php_v8_getter);
    PHP_V8_DATA_ISOLATES_CHECK(php_v8_getter, php_v8_value);

    getter = php_v8_value_get_local_as<v8::Function>(php_v8_getter);

    if (Z_TYPE_P(setter_zv) != IS_NULL) {
        PHP_V8_VALUE_FETCH_WITH_CHECK(setter_zv, php_v8_setter);
        PHP_V8_DATA_ISOLATES_CHECK(php_v8_setter, php_v8_value);

        setter = php_v8_value_get_local_as<v8::Function>(php_v8_setter);
    }

    v8::Local<v8::Object> local_object = php_v8_value_get_local_as<v8::Object>(php_v8_value);

    local_object->SetAccessorProperty(local_name, getter, setter, static_cast<v8::PropertyAttribute>(attributes), static_cast<v8::AccessControl>(settings));
}

/* NOTE: we skip functionality for private properties for now */

static PHP_METHOD(V8Object, GetPropertyNames) {
    zval *context_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o", &context_zv) == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_CONTEXT_FETCH_WITH_CHECK(context_zv, php_v8_context);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_context);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    v8::Local<v8::Object> local_object = php_v8_value_get_local_as<v8::Object>(php_v8_value);

    PHP_V8_TRY_CATCH(isolate);
    PHP_V8_INIT_ISOLATE_LIMITS_ON_OBJECT_VALUE(php_v8_value);

    v8::MaybeLocal<v8::Array> maybe_local_array = local_object->GetPropertyNames(context);

    PHP_V8_MAYBE_CATCH(php_v8_context, try_catch);
    PHP_V8_THROW_EXCEPTION_WHEN_EMPTY(maybe_local_array, "Failed to get property names")

    v8::Local<v8::Array> local_array = maybe_local_array.ToLocalChecked();

    php_v8_get_or_create_value(return_value, local_array, php_v8_value->php_v8_isolate);
}

static PHP_METHOD(V8Object, GetOwnPropertyNames) {
    zval *context_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o", &context_zv) == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_CONTEXT_FETCH_WITH_CHECK(context_zv, php_v8_context);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_context);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    v8::Local<v8::Object> local_object = php_v8_value_get_local_as<v8::Object>(php_v8_value);

    PHP_V8_TRY_CATCH(isolate);
    PHP_V8_INIT_ISOLATE_LIMITS_ON_OBJECT_VALUE(php_v8_value);

    v8::MaybeLocal<v8::Array> maybe_local_array = local_object->GetOwnPropertyNames(context);

    PHP_V8_MAYBE_CATCH(php_v8_context, try_catch);
    PHP_V8_THROW_EXCEPTION_WHEN_EMPTY(maybe_local_array, "Failed to get own property names")

    v8::Local<v8::Array> local_array = maybe_local_array.ToLocalChecked();

    php_v8_get_or_create_value(return_value, local_array, php_v8_value->php_v8_isolate);
}

static PHP_METHOD(V8Object, GetPrototype) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);
    PHP_V8_ENTER_STORED_CONTEXT(php_v8_value);

    v8::Local<v8::Object> local_object = php_v8_value_get_local_as<v8::Object>(php_v8_value);

    PHP_V8_TRY_CATCH(isolate);
    PHP_V8_INIT_ISOLATE_LIMITS_ON_OBJECT_VALUE(php_v8_value);

    v8::Local<v8::Value> local_prototype = local_object->GetPrototype();

    PHP_V8_MAYBE_CATCH(php_v8_value->php_v8_context, try_catch);
    PHP_V8_THROW_EXCEPTION_WHEN_EMPTY(local_prototype, "Failed to get prototype");

    php_v8_get_or_create_value(return_value, local_prototype, php_v8_value->php_v8_isolate);
}

static PHP_METHOD(V8Object, SetPrototype) {
    zval *context_zv;
    zval *value_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "oo", &context_zv, &value_zv) == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_CONTEXT_FETCH_WITH_CHECK(context_zv, php_v8_context);
    PHP_V8_VALUE_FETCH_WITH_CHECK(value_zv, php_v8_prototype);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_context);
    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_prototype);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    v8::Local<v8::Value> local_prototype = php_v8_value_get_local(php_v8_prototype);

    v8::Local<v8::Object> local_object = php_v8_value_get_local_as<v8::Object>(php_v8_value);

    PHP_V8_TRY_CATCH(isolate);
    PHP_V8_INIT_ISOLATE_LIMITS_ON_OBJECT_VALUE(php_v8_value);

    v8::Maybe<bool> maybe_res = local_object->SetPrototype(context, local_prototype);

    PHP_V8_MAYBE_CATCH(php_v8_context, try_catch);
    PHP_V8_THROW_EXCEPTION_WHEN_NOTHING(maybe_res, "Failed to set prototype")

    RETURN_BOOL(maybe_res.FromJust());
}

static PHP_METHOD(V8Object, FindInstanceInPrototypeChain) {
    zval *function_template_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o", &function_template_zv) == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_FETCH_FUNCTION_TEMPLATE_WITH_CHECK(function_template_zv, php_v8_function_template);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_function_template)

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);
    PHP_V8_ENTER_STORED_CONTEXT(php_v8_value);

    v8::Local<v8::FunctionTemplate> local_function_template = php_v8_function_template_get_local(php_v8_function_template);
    v8::Local<v8::Object> local_object = php_v8_value_get_local_as<v8::Object>(php_v8_value);

    PHP_V8_TRY_CATCH(isolate);
    PHP_V8_INIT_ISOLATE_LIMITS_ON_OBJECT_VALUE(php_v8_value);

    v8::Local<v8::Object> local_found = local_object->FindInstanceInPrototypeChain(local_function_template);

    PHP_V8_MAYBE_CATCH(php_v8_value->php_v8_context, try_catch);
    PHP_V8_THROW_EXCEPTION_WHEN_EMPTY(local_found, "Failed to find prototype")

    php_v8_get_or_create_value(return_value, local_found, php_v8_value->php_v8_isolate);
}

static PHP_METHOD(V8Object, ObjectProtoToString) {
    zval *context_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o", &context_zv) == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_CONTEXT_FETCH_WITH_CHECK(context_zv, php_v8_context);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_context)

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    v8::Local<v8::Object> local_object = php_v8_value_get_local_as<v8::Object>(php_v8_value);

    PHP_V8_TRY_CATCH(isolate);
    PHP_V8_INIT_ISOLATE_LIMITS_ON_OBJECT_VALUE(php_v8_value);

    v8::MaybeLocal<v8::String> maybe_local_string = local_object->ObjectProtoToString(context);

    PHP_V8_MAYBE_CATCH(php_v8_context, try_catch);
    PHP_V8_THROW_EXCEPTION_WHEN_EMPTY(maybe_local_string, "Failed to get")

    v8::Local<v8::String> local_string = maybe_local_string.ToLocalChecked();

    php_v8_get_or_create_value(return_value, local_string, php_v8_value->php_v8_isolate);
}

static PHP_METHOD(V8Object, GetConstructorName) {

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_ISOLATE(php_v8_value->php_v8_isolate);

    v8::Local<v8::Object> local_object = php_v8_value_get_local_as<v8::Object>(php_v8_value);

    php_v8_get_or_create_value(return_value, local_object->GetConstructorName(), php_v8_value->php_v8_isolate);
}

static PHP_METHOD(V8Object, SetIntegrityLevel) {
    zval *php_v8_context_zv;
    zend_long level;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "ol", &php_v8_context_zv, &level) == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_context)

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    v8::Local<v8::Object> local_obj = php_v8_value_get_local_as<v8::Object>(php_v8_value);

    level = level ? level & PHP_V8_INTEGRITY_LEVEL_FLAGS : level;

    PHP_V8_TRY_CATCH(isolate);
    PHP_V8_INIT_ISOLATE_LIMITS_ON_OBJECT_VALUE(php_v8_value);

    v8::Maybe<bool> maybe_res = local_obj->SetIntegrityLevel(context, static_cast<v8::IntegrityLevel>(level));

    PHP_V8_MAYBE_CATCH(php_v8_context, try_catch);
    PHP_V8_THROW_EXCEPTION_WHEN_NOTHING(maybe_res, "Failed to set integrity level");

    RETURN_BOOL(maybe_res.FromJust());
}

static PHP_METHOD(V8Object, HasOwnProperty) {
    zval *php_v8_context_zv;
    zval *php_v8_name_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "oo", &php_v8_context_zv, &php_v8_name_zv) == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_name_zv, php_v8_name);
    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_context)
    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_name)

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    v8::Local<v8::Object> local_object = php_v8_value_get_local_as<v8::Object>(php_v8_value);
    v8::Local<v8::Name> local_name = php_v8_value_get_local_as<v8::Name>(php_v8_name);

    PHP_V8_TRY_CATCH(isolate);
    PHP_V8_INIT_ISOLATE_LIMITS_ON_OBJECT_VALUE(php_v8_value);

    v8::Maybe<bool> maybe_res = local_object->HasOwnProperty(context, local_name);

    PHP_V8_MAYBE_CATCH(php_v8_context, try_catch);
    PHP_V8_THROW_EXCEPTION_WHEN_NOTHING(maybe_res, "Failed to perform check");

    RETURN_BOOL(maybe_res.FromJust());
}

static PHP_METHOD(V8Object, HasRealNamedProperty) {
    zval *php_v8_context_zv;
    zval *php_v8_name_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "oo", &php_v8_context_zv, &php_v8_name_zv) == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_name_zv, php_v8_name);
    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_context)
    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_name)

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    v8::Local<v8::Object> local_object = php_v8_value_get_local_as<v8::Object>(php_v8_value);
    v8::Local<v8::Name> local_name = php_v8_value_get_local_as<v8::Name>(php_v8_name);

    PHP_V8_TRY_CATCH(isolate);
    PHP_V8_INIT_ISOLATE_LIMITS_ON_OBJECT_VALUE(php_v8_value);

    v8::Maybe<bool> maybe_res = local_object->HasRealNamedProperty(context, local_name);

    PHP_V8_MAYBE_CATCH(php_v8_context, try_catch);
    PHP_V8_THROW_EXCEPTION_WHEN_NOTHING(maybe_res, "Failed to perform check");

    RETURN_BOOL(maybe_res.FromJust());
}

static PHP_METHOD(V8Object, HasRealIndexedProperty) {
    zval *php_v8_context_zv;
    zend_long index;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "ol", &php_v8_context_zv, &index) == FAILURE) {
        return;
    }

    PHP_V8_CHECK_UINT32_RANGE(index, "Index value to set is out of range, should be valid uint32_t");

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_context)

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    v8::Local<v8::Object> local_obj = php_v8_value_get_local_as<v8::Object>(php_v8_value);

    PHP_V8_TRY_CATCH(isolate);
    PHP_V8_INIT_ISOLATE_LIMITS_ON_OBJECT_VALUE(php_v8_value);

    v8::Maybe<bool> maybe_res = local_obj->HasRealIndexedProperty(context, (uint32_t) index);

    PHP_V8_MAYBE_CATCH(php_v8_context, try_catch);
    PHP_V8_THROW_EXCEPTION_WHEN_NOTHING(maybe_res, "Failed to perform check");

    RETURN_BOOL(maybe_res.FromJust());
}

static PHP_METHOD(V8Object, HasRealNamedCallbackProperty) {
    zval *php_v8_context_zv;
    zval *php_v8_name_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "oo", &php_v8_context_zv, &php_v8_name_zv) == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_name_zv, php_v8_name);
    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_context)
    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_name)

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    v8::Local<v8::Object> local_object = php_v8_value_get_local_as<v8::Object>(php_v8_value);
    v8::Local<v8::Name> local_name = php_v8_value_get_local_as<v8::Name>(php_v8_name);

    PHP_V8_TRY_CATCH(isolate);
    PHP_V8_INIT_ISOLATE_LIMITS_ON_OBJECT_VALUE(php_v8_value);

    v8::Maybe<bool> maybe_res = local_object->HasRealNamedCallbackProperty(context, local_name);

    PHP_V8_MAYBE_CATCH(php_v8_context, try_catch);
    PHP_V8_THROW_EXCEPTION_WHEN_NOTHING(maybe_res, "Failed to perform check");

    RETURN_BOOL(maybe_res.FromJust());
}

static PHP_METHOD(V8Object, GetRealNamedPropertyInPrototypeChain) {
    zval *php_v8_context_zv;
    zval *php_v8_name_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "oo", &php_v8_context_zv, &php_v8_name_zv) == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_name_zv, php_v8_name);
    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_context)
    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_name)

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    v8::Local<v8::Object> local_object = php_v8_value_get_local_as<v8::Object>(php_v8_value);
    v8::Local<v8::Name> local_name = php_v8_value_get_local_as<v8::Name>(php_v8_name);

    PHP_V8_TRY_CATCH(isolate);
    PHP_V8_INIT_ISOLATE_LIMITS_ON_OBJECT_VALUE(php_v8_value);

    v8::MaybeLocal<v8::Value> maybe_res = local_object->GetRealNamedPropertyInPrototypeChain(context, local_name);

    PHP_V8_MAYBE_CATCH(php_v8_context, try_catch);
    PHP_V8_THROW_EXCEPTION_WHEN_EMPTY(maybe_res, "No real property was located in the prototype chain");

    v8::Local<v8::Value> local_value = maybe_res.ToLocalChecked();

    php_v8_get_or_create_value(return_value, local_value, php_v8_value->php_v8_isolate);
}

static PHP_METHOD(V8Object, GetRealNamedPropertyAttributesInPrototypeChain) {
    zval *php_v8_context_zv;
    zval *php_v8_name_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "oo", &php_v8_context_zv, &php_v8_name_zv) == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_name_zv, php_v8_name);
    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_context)
    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_name)

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    v8::Local<v8::Object> local_object = php_v8_value_get_local_as<v8::Object>(php_v8_value);
    v8::Local<v8::Name> local_name = php_v8_value_get_local_as<v8::Name>(php_v8_name);

    PHP_V8_TRY_CATCH(isolate);
    PHP_V8_INIT_ISOLATE_LIMITS_ON_OBJECT_VALUE(php_v8_value);

    v8::Maybe<v8::PropertyAttribute> maybe_res = local_object->GetRealNamedPropertyAttributesInPrototypeChain(context, local_name);

    PHP_V8_MAYBE_CATCH(php_v8_context, try_catch);
    PHP_V8_THROW_EXCEPTION_WHEN_NOTHING(maybe_res, "Failed to get");

    RETURN_LONG((zend_long) maybe_res.FromJust());
}

static PHP_METHOD(V8Object, GetRealNamedProperty) {
    zval *php_v8_context_zv;
    zval *php_v8_name_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "oo", &php_v8_context_zv, &php_v8_name_zv) == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_name_zv, php_v8_name);
    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_context)
    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_name)

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    v8::Local<v8::Object> local_object = php_v8_value_get_local_as<v8::Object>(php_v8_value);
    v8::Local<v8::Name> local_name = php_v8_value_get_local_as<v8::Name>(php_v8_name);

    v8::MaybeLocal<v8::Value> maybe_res = local_object->GetRealNamedProperty(context, local_name);

    PHP_V8_THROW_EXCEPTION_WHEN_EMPTY(maybe_res, "No real property was located on the object or in the prototype chain");

    v8::Local<v8::Value> local_value = maybe_res.ToLocalChecked();

    php_v8_get_or_create_value(return_value, local_value, php_v8_value->php_v8_isolate);
}

static PHP_METHOD(V8Object, GetRealNamedPropertyAttributes) {
    zval *php_v8_context_zv;
    zval *php_v8_name_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "oo", &php_v8_context_zv, &php_v8_name_zv) == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_name_zv, php_v8_name);
    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_context)
    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_name)

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    v8::Local<v8::Object> local_object = php_v8_value_get_local_as<v8::Object>(php_v8_value);
    v8::Local<v8::Name> local_name = php_v8_value_get_local_as<v8::Name>(php_v8_name);

    PHP_V8_TRY_CATCH(isolate);
    PHP_V8_INIT_ISOLATE_LIMITS_ON_OBJECT_VALUE(php_v8_value);

    v8::Maybe<v8::PropertyAttribute> maybe_res = local_object->GetRealNamedPropertyAttributes(context, local_name);

    PHP_V8_MAYBE_CATCH(php_v8_context, try_catch);
    PHP_V8_THROW_EXCEPTION_WHEN_NOTHING(maybe_res, "Failed to get property attribute");

    RETURN_LONG((zend_long) maybe_res.FromJust());
}

static PHP_METHOD(V8Object, HasNamedLookupInterceptor) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_ISOLATE(php_v8_value->php_v8_isolate);

    v8::Local<v8::Value> local_value = php_v8_value_get_local(php_v8_value);
    v8::Local<v8::Object> local_object = v8::Local<v8::Object>::Cast(local_value);

    RETURN_BOOL(local_object->HasNamedLookupInterceptor());
}

static PHP_METHOD(V8Object, HasIndexedLookupInterceptor) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_ISOLATE(php_v8_value->php_v8_isolate);

    v8::Local<v8::Value> local_value = php_v8_value_get_local(php_v8_value);
    v8::Local<v8::Object> local_object = v8::Local<v8::Object>::Cast(local_value);

    RETURN_BOOL(local_object->HasIndexedLookupInterceptor());
}

static PHP_METHOD(V8Object, GetIdentityHash)
{
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);
    PHP_V8_ENTER_STORED_CONTEXT(php_v8_value);

    v8::Local<v8::Object> local_object = php_v8_value_get_local_as<v8::Object>(php_v8_value);

    RETVAL_LONG(local_object->GetIdentityHash());
}

static PHP_METHOD(V8Object, Clone) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);
    PHP_V8_ENTER_STORED_CONTEXT(php_v8_value);

    v8::Local<v8::Object> local_object = php_v8_value_get_local_as<v8::Object>(php_v8_value);

    PHP_V8_TRY_CATCH(isolate);
    PHP_V8_INIT_ISOLATE_LIMITS_ON_OBJECT_VALUE(php_v8_value);

    v8::Local<v8::Object> local_cloned_object= local_object->Clone();

    PHP_V8_MAYBE_CATCH(php_v8_value->php_v8_context, try_catch);
    PHP_V8_THROW_EXCEPTION_WHEN_EMPTY(local_cloned_object, "Object cloning failed");

    php_v8_get_or_create_value(return_value, local_cloned_object, php_v8_value->php_v8_isolate);
}

static PHP_METHOD(V8Object, IsCallable) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_ISOLATE(php_v8_value->php_v8_isolate);

    v8::Local<v8::Value> local_value = php_v8_value_get_local(php_v8_value);
    v8::Local<v8::Object> local_object = v8::Local<v8::Object>::Cast(local_value);

    RETURN_BOOL(local_object->IsCallable());
}


static PHP_METHOD(V8Object, IsConstructor) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_ISOLATE(php_v8_value->php_v8_isolate);

    v8::Local<v8::Value> local_value = php_v8_value_get_local(php_v8_value);
    v8::Local<v8::Object> local_object = v8::Local<v8::Object>::Cast(local_value);

    RETURN_BOOL(local_object->IsConstructor());
}

static PHP_METHOD(V8Object, CallAsFunction) {
    zval *php_v8_context_zv;
    zval *php_v8_recv_zv;
    zval* arguments_zv = NULL;

    int argc = 0;
    v8::Local<v8::Value> *argv = NULL;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "oo|a", &php_v8_context_zv, &php_v8_recv_zv, &arguments_zv) == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_recv_zv, php_v8_value_recv);

    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_context);
    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_value_recv);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    if (!php_v8_function_unpack_args(arguments_zv, 3, isolate, &argc, &argv)) {
        return;
    }

    v8::Local<v8::Value> local_recv = php_v8_value_get_local(php_v8_value_recv);
    v8::Local<v8::Object> local_object = php_v8_value_get_local_as<v8::Object>(php_v8_value);

    PHP_V8_TRY_CATCH(isolate);
    PHP_V8_INIT_ISOLATE_LIMITS_ON_CONTEXT(php_v8_context);

    v8::MaybeLocal<v8::Value> maybe_local_res = local_object->CallAsFunction(context, local_recv, argc, argv);

    if (argv) {
        efree(argv);
    }

    PHP_V8_MAYBE_CATCH(php_v8_context, try_catch);
    PHP_V8_THROW_VALUE_EXCEPTION_WHEN_EMPTY(maybe_local_res, "Failed to call");

    v8::Local<v8::Value> local_res = maybe_local_res.ToLocalChecked();

    php_v8_get_or_create_value(return_value, local_res, php_v8_value->php_v8_isolate);
}

static PHP_METHOD(V8Object, CallAsConstructor) {
    zval *php_v8_context_zv;
    zval* arguments_zv = NULL;

    int argc = 0;
    v8::Local<v8::Value> *argv = NULL;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o|a", &php_v8_context_zv, &arguments_zv) == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_context, php_v8_value);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    if (!php_v8_function_unpack_args(arguments_zv, 2, isolate, &argc, &argv)) {
        return;
    }

    v8::Local<v8::Object> local_object = php_v8_value_get_local_as<v8::Object>(php_v8_value);

    PHP_V8_TRY_CATCH(isolate);
    PHP_V8_INIT_ISOLATE_LIMITS_ON_CONTEXT(php_v8_context);

    v8::MaybeLocal<v8::Value> maybe_local_res = local_object->CallAsConstructor(context, argc, argv);

    if (argv) {
        efree(argv);
    }

    PHP_V8_MAYBE_CATCH(php_v8_context, try_catch);
    PHP_V8_THROW_VALUE_EXCEPTION_WHEN_EMPTY(maybe_local_res,  "Failed to call");

    v8::Local<v8::Value> local_res = maybe_local_res.ToLocalChecked();

    php_v8_get_or_create_value(return_value, local_res, php_v8_value->php_v8_isolate);
}

// Not supported yet
//static PHP_METHOD(V8Object, Cast) {
//}

/* Non-standard, implementations of AdjustableExternalMemoryInterface::AdjustExternalAllocatedMemory */
static PHP_METHOD(V8Object, AdjustExternalAllocatedMemory) {
    php_v8_ext_mem_interface_value_AdjustExternalAllocatedMemory(INTERNAL_FUNCTION_PARAM_PASSTHRU);
}

/* Non-standard, implementations of AdjustableExternalMemoryInterface::GetExternalAllocatedMemory */
static PHP_METHOD(V8Object, GetExternalAllocatedMemory) {
    php_v8_ext_mem_interface_value_GetExternalAllocatedMemory(INTERNAL_FUNCTION_PARAM_PASSTHRU);
}


ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_object___construct, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 1)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_v8_object_GetContext, ZEND_RETURN_VALUE, 0, V8\\Context, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_object_Set, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 3)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_OBJ_INFO(0, key, V8\\Value, 0)
                ZEND_ARG_OBJ_INFO(0, value, V8\\Value, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_object_CreateDataProperty, ZEND_RETURN_VALUE, 3, _IS_BOOL, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_OBJ_INFO(0, key, V8\\NameValue, 0)
                ZEND_ARG_OBJ_INFO(0, value, V8\\Value, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_object_DefineOwnProperty, ZEND_RETURN_VALUE, 3, _IS_BOOL, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_OBJ_INFO(0, key, V8\\NameValue, 0)
                ZEND_ARG_OBJ_INFO(0, value, V8\\Value, 0)
                ZEND_ARG_INFO(0, attributes)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_v8_object_Get, ZEND_RETURN_VALUE, 2, V8\\Value, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_OBJ_INFO(0, key, V8\\Value, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_object_GetPropertyAttributes, ZEND_RETURN_VALUE, 2, IS_LONG, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_OBJ_INFO(0, key, V8\\StringValue, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_v8_object_GetOwnPropertyDescriptor, ZEND_RETURN_VALUE, 2, V8\\Value, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_OBJ_INFO(0, key, V8\\StringValue, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_object_Has, ZEND_RETURN_VALUE, 2, _IS_BOOL, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_OBJ_INFO(0, key, V8\\Value, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_object_Delete, ZEND_RETURN_VALUE, 2, _IS_BOOL, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_OBJ_INFO(0, key, V8\\Value, 0)
ZEND_END_ARG_INFO()

// bool
PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_object_SetAccessor, ZEND_RETURN_VALUE, 3, _IS_BOOL, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_OBJ_INFO(0, name, V8\\NameValue, 0)
                ZEND_ARG_CALLABLE_INFO(0, getter, 0)
                ZEND_ARG_CALLABLE_INFO(0, setter, 1)
                ZEND_ARG_TYPE_INFO(0, settings, IS_LONG, 0)
                ZEND_ARG_TYPE_INFO(0, attributes, IS_LONG, 0)
ZEND_END_ARG_INFO()

//void method
ZEND_BEGIN_ARG_INFO_EX(arginfo_php_v8_object_SetAccessorProperty, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 2)
                ZEND_ARG_OBJ_INFO(0, name, V8\\NameValue, 0)
                ZEND_ARG_OBJ_INFO(0, getter, V8\\FunctionObject, 0)
                ZEND_ARG_OBJ_INFO(0, setter, V8\\FunctionObject, 0)
                ZEND_ARG_TYPE_INFO(0, attributes, IS_LONG, 0)
                ZEND_ARG_TYPE_INFO(0, settings, IS_LONG, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_php_v8_object_GetPropertyNames, ZEND_RETURN_VALUE, 1, V8\\ArrayObject, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_php_v8_object_GetOwnPropertyNames, ZEND_RETURN_VALUE, 1, V8\\ArrayObject, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_php_v8_object_GetPrototype, ZEND_RETURN_VALUE, 0, V8\\Value, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_php_v8_object_SetPrototype, ZEND_RETURN_VALUE, 2, _IS_BOOL, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_OBJ_INFO(0, prototype, V8\\Value, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_php_v8_object_FindInstanceInPrototypeChain, ZEND_RETURN_VALUE, 1, V8\\ObjectValue, 0)
                ZEND_ARG_OBJ_INFO(0, tmpl, V8\\FunctionTemplate, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_php_v8_object_ObjectProtoToString, ZEND_RETURN_VALUE, 1, V8\\StringValue, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_v8_object_GetConstructorName, ZEND_RETURN_VALUE, 0, V8\\StringValue, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_object_SetIntegrityLevel, ZEND_RETURN_VALUE, 2, _IS_BOOL, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_TYPE_INFO(0, level, IS_LONG, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_object_HasOwnProperty, ZEND_RETURN_VALUE, 2, _IS_BOOL, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_INFO(0, key)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_object_HasRealNamedProperty, ZEND_RETURN_VALUE, 2, _IS_BOOL, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_INFO(0, key)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_object_HasRealIndexedProperty, ZEND_RETURN_VALUE, 2, _IS_BOOL, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_INFO(0, index)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_object_HasRealNamedCallbackProperty, ZEND_RETURN_VALUE, 2, _IS_BOOL, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_INFO(0, key)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_v8_object_GetRealNamedPropertyInPrototypeChain, ZEND_RETURN_VALUE, 2, V8\\Value, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_INFO(0, key)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_object_GetRealNamedPropertyAttributesInPrototypeChain, ZEND_RETURN_VALUE, 2, IS_LONG, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_INFO(0, key)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_v8_object_GetRealNamedProperty, ZEND_RETURN_VALUE, 2, V8\\Value, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_INFO(0, key)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_object_GetRealNamedPropertyAttributes, ZEND_RETURN_VALUE, 2, IS_LONG, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_INFO(0, key)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_object_HasNamedLookupInterceptor, ZEND_RETURN_VALUE, 0, _IS_BOOL, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_object_HasIndexedLookupInterceptor, ZEND_RETURN_VALUE, 0, _IS_BOOL, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_object_GetIdentityHash, ZEND_RETURN_VALUE, 0, IS_LONG, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_v8_object_Clone, ZEND_RETURN_VALUE, 0, V8\\ObjectValue, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_object_IsCallable, ZEND_RETURN_VALUE, 0, _IS_BOOL, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_object_IsConstructor, ZEND_RETURN_VALUE, 0, _IS_BOOL, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_v8_object_CallAsFunction, ZEND_RETURN_VALUE, 2, V8\\Value, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_OBJ_INFO(0, recv, V8\\Value, 0)
                ZEND_ARG_ARRAY_INFO(0, arguments, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_v8_object_CallAsConstructor, ZEND_RETURN_VALUE, 1, V8\\Value, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_ARRAY_INFO(0, arguments, 0)
ZEND_END_ARG_INFO()

// static methods

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_object_AdjustExternalAllocatedMemory, ZEND_RETURN_VALUE, 1, IS_LONG, 0)
                ZEND_ARG_TYPE_INFO(0, change_in_bytes, IS_LONG, 0)
ZEND_END_ARG_INFO()


PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_object_GetExternalAllocatedMemory, ZEND_RETURN_VALUE, 0, IS_LONG, 0)
ZEND_END_ARG_INFO()


static const zend_function_entry php_v8_object_methods[] = {
        PHP_ME(V8Object, __construct, arginfo_v8_object___construct, ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)
        PHP_ME(V8Object, GetContext, arginfo_v8_object_GetContext, ZEND_ACC_PUBLIC)
        PHP_ME(V8Object, Set, arginfo_v8_object_Set, ZEND_ACC_PUBLIC)
        PHP_ME(V8Object, CreateDataProperty, arginfo_v8_object_CreateDataProperty, ZEND_ACC_PUBLIC)
        PHP_ME(V8Object, DefineOwnProperty, arginfo_v8_object_DefineOwnProperty, ZEND_ACC_PUBLIC)
        PHP_ME(V8Object, Get, arginfo_v8_object_Get, ZEND_ACC_PUBLIC)
        PHP_ME(V8Object, GetPropertyAttributes, arginfo_v8_object_GetPropertyAttributes, ZEND_ACC_PUBLIC)
        PHP_ME(V8Object, GetOwnPropertyDescriptor, arginfo_v8_object_GetOwnPropertyDescriptor, ZEND_ACC_PUBLIC)
        PHP_ME(V8Object, Has, arginfo_v8_object_Has, ZEND_ACC_PUBLIC)
        PHP_ME(V8Object, Delete, arginfo_v8_object_Delete, ZEND_ACC_PUBLIC)
        PHP_ME(V8Object, SetAccessor, arginfo_v8_object_SetAccessor, ZEND_ACC_PUBLIC)
        PHP_ME(V8Object, SetAccessorProperty, arginfo_php_v8_object_SetAccessorProperty, ZEND_ACC_PUBLIC)
        PHP_ME(V8Object, GetPropertyNames, arginfo_php_v8_object_GetPropertyNames, ZEND_ACC_PUBLIC)
        PHP_ME(V8Object, GetOwnPropertyNames, arginfo_php_v8_object_GetOwnPropertyNames, ZEND_ACC_PUBLIC)
        PHP_ME(V8Object, GetPrototype, arginfo_php_v8_object_GetPrototype, ZEND_ACC_PUBLIC)
        PHP_ME(V8Object, SetPrototype, arginfo_php_v8_object_SetPrototype, ZEND_ACC_PUBLIC)
        PHP_ME(V8Object, FindInstanceInPrototypeChain, arginfo_php_v8_object_FindInstanceInPrototypeChain, ZEND_ACC_PUBLIC)
        PHP_ME(V8Object, ObjectProtoToString, arginfo_php_v8_object_ObjectProtoToString, ZEND_ACC_PUBLIC)
        PHP_ME(V8Object, GetConstructorName, arginfo_v8_object_GetConstructorName, ZEND_ACC_PUBLIC)
        PHP_ME(V8Object, SetIntegrityLevel, arginfo_v8_object_SetIntegrityLevel, ZEND_ACC_PUBLIC)

        PHP_ME(V8Object, HasOwnProperty, arginfo_v8_object_HasOwnProperty, ZEND_ACC_PUBLIC)
        PHP_ME(V8Object, HasRealNamedProperty, arginfo_v8_object_HasRealNamedProperty, ZEND_ACC_PUBLIC)
        PHP_ME(V8Object, HasRealIndexedProperty, arginfo_v8_object_HasRealIndexedProperty, ZEND_ACC_PUBLIC)
        PHP_ME(V8Object, HasRealNamedCallbackProperty, arginfo_v8_object_HasRealNamedCallbackProperty, ZEND_ACC_PUBLIC)

        PHP_ME(V8Object, GetRealNamedPropertyInPrototypeChain, arginfo_v8_object_GetRealNamedPropertyInPrototypeChain, ZEND_ACC_PUBLIC)
        PHP_ME(V8Object, GetRealNamedPropertyAttributesInPrototypeChain, arginfo_v8_object_GetRealNamedPropertyAttributesInPrototypeChain, ZEND_ACC_PUBLIC)
        PHP_ME(V8Object, GetRealNamedProperty, arginfo_v8_object_GetRealNamedProperty, ZEND_ACC_PUBLIC)
        PHP_ME(V8Object, GetRealNamedPropertyAttributes, arginfo_v8_object_GetRealNamedPropertyAttributes, ZEND_ACC_PUBLIC)

        PHP_ME(V8Object, HasNamedLookupInterceptor, arginfo_v8_object_HasNamedLookupInterceptor, ZEND_ACC_PUBLIC)
        PHP_ME(V8Object, HasIndexedLookupInterceptor, arginfo_v8_object_HasIndexedLookupInterceptor, ZEND_ACC_PUBLIC)
        PHP_ME(V8Object, GetIdentityHash, arginfo_v8_object_GetIdentityHash, ZEND_ACC_PUBLIC)
        PHP_ME(V8Object, Clone, arginfo_v8_object_Clone, ZEND_ACC_PUBLIC)

        PHP_ME(V8Object, IsCallable, arginfo_v8_object_IsCallable, ZEND_ACC_PUBLIC)
        PHP_ME(V8Object, IsConstructor, arginfo_v8_object_IsConstructor, ZEND_ACC_PUBLIC)
        PHP_ME(V8Object, CallAsFunction, arginfo_v8_object_CallAsFunction, ZEND_ACC_PUBLIC)
        PHP_ME(V8Object, CallAsConstructor, arginfo_v8_object_CallAsConstructor, ZEND_ACC_PUBLIC)

        // NOTE: Not supported yet
        //PHP_ME(V8Object, Cast, arginfo_v8_object_Cast, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)

        PHP_ME(V8Object, AdjustExternalAllocatedMemory, arginfo_v8_object_AdjustExternalAllocatedMemory, ZEND_ACC_PUBLIC)
        PHP_ME(V8Object, GetExternalAllocatedMemory, arginfo_v8_object_GetExternalAllocatedMemory, ZEND_ACC_PUBLIC)

        PHP_FE_END
};


PHP_MINIT_FUNCTION(php_v8_object) {
    zend_class_entry ce;
    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS, "ObjectValue", php_v8_object_methods);
    this_ce = zend_register_internal_class_ex(&ce, php_v8_value_class_entry);
    zend_class_implements(this_ce, 1, php_v8_ext_mem_interface_ce);

    zend_declare_property_null(this_ce, ZEND_STRL("context"), ZEND_ACC_PRIVATE);

    return SUCCESS;
}
