/*
 * This file is part of the pinepain/php-v8 PHP extension.
 *
 * Copyright (c) 2015-2018 Bogdan Padalko <pinepain@gmail.com>
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
#include "php_v8_exceptions.h"
#include "php_v8_function_template.h"
#include "php_v8_function.h"
#include "php_v8_string.h"
#include "php_v8_uint32.h"
#include "php_v8_name.h"
#include "php_v8_value.h"
#include "php_v8_context.h"
#include "php_v8_ext_mem_interface.h"
#include "php_v8_enums.h"
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


static PHP_METHOD(Object, __construct) {
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

static PHP_METHOD(Object, getContext) {
    zval rv;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);

    RETURN_ZVAL(PHP_V8_OBJECT_READ_CONTEXT(getThis()), 1, 0);
}

static PHP_METHOD(Object, set) {
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

static PHP_METHOD(Object, createDataProperty) {
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

static PHP_METHOD(Object, defineOwnProperty) {
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

static PHP_METHOD(Object, get) {
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

static PHP_METHOD(Object, getPropertyAttributes) {
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

static PHP_METHOD(Object, getOwnPropertyDescriptor) {
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

static PHP_METHOD(Object, has) {
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

static PHP_METHOD(Object, delete) {
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

static PHP_METHOD(Object, setAccessor) {
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

    PHP_V8_CONVERT_FROM_V8_STRING_TO_STRING(isolate, name, local_name);

    attributes = attributes ? attributes & PHP_V8_PROPERTY_ATTRIBUTE_FLAGS : attributes;
    settings = settings ? settings & PHP_V8_ACCESS_CONTROL_FLAGS : settings;


    v8::AccessorNameGetterCallback getter;
    v8::AccessorNameSetterCallback setter = 0;
    v8::Local<v8::External> data;


    phpv8::CallbacksBucket *bucket = php_v8_value->persistent_data->bucket("accessor_", local_name->IsSymbol(), name);
    data = v8::External::New(isolate, bucket);

    bucket->add(phpv8::CallbacksBucket::Index::Getter, getter_fci, getter_fci_cache);
    getter = php_v8_callback_accessor_name_getter;

    if (setter_fci.size) {
        bucket->add(phpv8::CallbacksBucket::Index::Setter, setter_fci, setter_fci_cache);
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

static PHP_METHOD(Object, setAccessorProperty) {
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


///**
// * Sets a native data property like Template::SetNativeDataProperty, but
// * this method sets on this object directly.
// */
//V8_WARN_UNUSED_RESULT Maybe<bool> SetNativeDataProperty(
//        Local<Context> context, Local<Name> name,
//        AccessorNameGetterCallback getter,
//        AccessorNameSetterCallback setter = nullptr,
//        Local<Value> data = Local<Value>(), PropertyAttribute attributes = None);

static PHP_METHOD(Object, setNativeDataProperty) {
    zval *context_zv;
    zval *php_v8_name_zv;

    zend_long attributes = 0;

    zend_fcall_info getter_fci = empty_fcall_info;
    zend_fcall_info_cache getter_fci_cache = empty_fcall_info_cache;

    zend_fcall_info setter_fci = empty_fcall_info;
    zend_fcall_info_cache setter_fci_cache = empty_fcall_info_cache;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "oof|f!l",
                              &context_zv,
                              &php_v8_name_zv,
                              &getter_fci, &getter_fci_cache,
                              &setter_fci, &setter_fci_cache,
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

    PHP_V8_CONVERT_FROM_V8_STRING_TO_STRING(isolate, name, local_name);

    attributes = attributes ? attributes & PHP_V8_PROPERTY_ATTRIBUTE_FLAGS : attributes;

    v8::AccessorNameGetterCallback getter;
    v8::AccessorNameSetterCallback setter = 0;
    v8::Local<v8::External> data;

    phpv8::CallbacksBucket *bucket = php_v8_value->persistent_data->bucket("native_data_property_", local_name->IsSymbol(), name);
    data = v8::External::New(isolate, bucket);

    bucket->add(phpv8::CallbacksBucket::Index::Getter, getter_fci, getter_fci_cache);
    getter = php_v8_callback_accessor_name_getter;

    if (setter_fci.size) {
        bucket->add(phpv8::CallbacksBucket::Index::Setter, setter_fci, setter_fci_cache);
        setter = php_v8_callback_accessor_name_setter;
    }

    v8::Maybe<bool> maybe_res = local_object->SetNativeDataProperty(context,
                                                                    local_name,
                                                                    getter,
                                                                    setter,
                                                                    data,
                                                                    static_cast<v8::PropertyAttribute>(attributes)
                                                                   );

    PHP_V8_THROW_EXCEPTION_WHEN_NOTHING(maybe_res, "Failed to set native data property");

    RETURN_BOOL(maybe_res.FromJust());
}


/* NOTE: we skip functionality for private properties for now */

static PHP_METHOD(Object, getPropertyNames) {
    zval *context_zv;
    zend_long mode            = static_cast<zend_long>(v8::KeyCollectionMode::kOwnOnly);
    zend_long property_filter = static_cast<zend_long>(v8::PropertyFilter::ALL_PROPERTIES);
    zend_long index_filter    = static_cast<zend_long>(v8::IndexFilter::kIncludeIndices);
    zend_bool convert_to_strings  = '\0';

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o|lllb",
                              &context_zv, &mode, &property_filter, &index_filter, &convert_to_strings) == FAILURE) {
        return;
    }

    mode = mode ? mode & PHP_V8_KEY_COLLECTION_MODE_FLAGS : mode;
    property_filter = property_filter ? property_filter & PHP_V8_PROPERTY_FILTER_FLAGS : property_filter;
    index_filter = index_filter ? index_filter & PHP_V8_INDEX_FILTER_FLAGS : index_filter;
    v8::KeyConversionMode key_conversion = convert_to_strings ? v8::KeyConversionMode::kConvertToString : v8::KeyConversionMode::kKeepNumbers;

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_CONTEXT_FETCH_WITH_CHECK(context_zv, php_v8_context);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_context);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    v8::Local<v8::Object> local_object = php_v8_value_get_local_as<v8::Object>(php_v8_value);

    PHP_V8_TRY_CATCH(isolate);
    PHP_V8_INIT_ISOLATE_LIMITS_ON_OBJECT_VALUE(php_v8_value);

    v8::MaybeLocal<v8::Array> maybe_local_array = local_object->GetPropertyNames(context,
                                                                                 static_cast<v8::KeyCollectionMode>(mode),
                                                                                 static_cast<v8::PropertyFilter >(property_filter),
                                                                                 static_cast<v8::IndexFilter>(index_filter),
                                                                                 key_conversion);

    PHP_V8_MAYBE_CATCH(php_v8_context, try_catch);
    PHP_V8_THROW_EXCEPTION_WHEN_EMPTY(maybe_local_array, "Failed to get property names")

    v8::Local<v8::Array> local_array = maybe_local_array.ToLocalChecked();

    php_v8_get_or_create_value(return_value, local_array, php_v8_value->php_v8_isolate);
}

static PHP_METHOD(Object, getOwnPropertyNames) {
    zval *context_zv;
    zend_long filter = static_cast<zend_long>(v8::PropertyFilter::ALL_PROPERTIES);
    zend_bool convert_to_strings  = '\0';

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o|lb", &context_zv, &filter, &convert_to_strings) == FAILURE) {
        return;
    }
    filter = filter ? filter & PHP_V8_PROPERTY_FILTER_FLAGS : filter;
    v8::KeyConversionMode key_conversion = convert_to_strings ? v8::KeyConversionMode::kConvertToString : v8::KeyConversionMode::kKeepNumbers;

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_CONTEXT_FETCH_WITH_CHECK(context_zv, php_v8_context);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_context);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    v8::Local<v8::Object> local_object = php_v8_value_get_local_as<v8::Object>(php_v8_value);

    PHP_V8_TRY_CATCH(isolate);
    PHP_V8_INIT_ISOLATE_LIMITS_ON_OBJECT_VALUE(php_v8_value);

    v8::MaybeLocal<v8::Array> maybe_local_array = local_object->GetOwnPropertyNames(context, static_cast<v8::PropertyFilter >(filter), key_conversion);

    PHP_V8_MAYBE_CATCH(php_v8_context, try_catch);
    PHP_V8_THROW_EXCEPTION_WHEN_EMPTY(maybe_local_array, "Failed to get own property names")

    v8::Local<v8::Array> local_array = maybe_local_array.ToLocalChecked();

    php_v8_get_or_create_value(return_value, local_array, php_v8_value->php_v8_isolate);
}

static PHP_METHOD(Object, getPrototype) {
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

static PHP_METHOD(Object, setPrototype) {
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

static PHP_METHOD(Object, findInstanceInPrototypeChain) {
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

static PHP_METHOD(Object, objectProtoToString) {
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

static PHP_METHOD(Object, getConstructorName) {

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_ISOLATE(php_v8_value->php_v8_isolate);

    v8::Local<v8::Object> local_object = php_v8_value_get_local_as<v8::Object>(php_v8_value);

    php_v8_get_or_create_value(return_value, local_object->GetConstructorName(), php_v8_value->php_v8_isolate);
}

static PHP_METHOD(Object, setIntegrityLevel) {
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

static PHP_METHOD(Object, hasOwnProperty) {
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

static PHP_METHOD(Object, hasRealNamedProperty) {
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

static PHP_METHOD(Object, hasRealIndexedProperty) {
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

static PHP_METHOD(Object, hasRealNamedCallbackProperty) {
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

static PHP_METHOD(Object, getRealNamedPropertyInPrototypeChain) {
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

static PHP_METHOD(Object, getRealNamedPropertyAttributesInPrototypeChain) {
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

static PHP_METHOD(Object, getRealNamedProperty) {
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

static PHP_METHOD(Object, getRealNamedPropertyAttributes) {
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

static PHP_METHOD(Object, hasNamedLookupInterceptor) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_ISOLATE(php_v8_value->php_v8_isolate);

    v8::Local<v8::Value> local_value = php_v8_value_get_local(php_v8_value);
    v8::Local<v8::Object> local_object = v8::Local<v8::Object>::Cast(local_value);

    RETURN_BOOL(local_object->HasNamedLookupInterceptor());
}

static PHP_METHOD(Object, hasIndexedLookupInterceptor) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_ISOLATE(php_v8_value->php_v8_isolate);

    v8::Local<v8::Value> local_value = php_v8_value_get_local(php_v8_value);
    v8::Local<v8::Object> local_object = v8::Local<v8::Object>::Cast(local_value);

    RETURN_BOOL(local_object->HasIndexedLookupInterceptor());
}

static PHP_METHOD(Object, getIdentityHash)
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

static PHP_METHOD(Object, clone) {
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

static PHP_METHOD(Object, isCallable) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_ISOLATE(php_v8_value->php_v8_isolate);

    v8::Local<v8::Value> local_value = php_v8_value_get_local(php_v8_value);
    v8::Local<v8::Object> local_object = v8::Local<v8::Object>::Cast(local_value);

    RETURN_BOOL(local_object->IsCallable());
}


static PHP_METHOD(Object, isConstructor) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_ISOLATE(php_v8_value->php_v8_isolate);

    v8::Local<v8::Value> local_value = php_v8_value_get_local(php_v8_value);
    v8::Local<v8::Object> local_object = v8::Local<v8::Object>::Cast(local_value);

    RETURN_BOOL(local_object->IsConstructor());
}

static PHP_METHOD(Object, callAsFunction) {
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

static PHP_METHOD(Object, callAsConstructor) {
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

/* Non-standard, implementations of AdjustableExternalMemoryInterface::AdjustExternalAllocatedMemory */
static PHP_METHOD(Object, adjustExternalAllocatedMemory) {
    php_v8_ext_mem_interface_value_AdjustExternalAllocatedMemory(INTERNAL_FUNCTION_PARAM_PASSTHRU);
}

/* Non-standard, implementations of AdjustableExternalMemoryInterface::GetExternalAllocatedMemory */
static PHP_METHOD(Object, getExternalAllocatedMemory) {
    php_v8_ext_mem_interface_value_GetExternalAllocatedMemory(INTERNAL_FUNCTION_PARAM_PASSTHRU);
}


PHP_V8_ZEND_BEGIN_ARG_WITH_CONSTRUCTOR_INFO_EX(arginfo___construct, 1)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_getContext, ZEND_RETURN_VALUE, 0, V8\\Context, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_VOID_INFO_EX(arginfo_set, 3)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_OBJ_INFO(0, key, V8\\Value, 0)
                ZEND_ARG_OBJ_INFO(0, value, V8\\Value, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_createDataProperty, ZEND_RETURN_VALUE, 3, _IS_BOOL, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_OBJ_INFO(0, key, V8\\NameValue, 0)
                ZEND_ARG_OBJ_INFO(0, value, V8\\Value, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_defineOwnProperty, ZEND_RETURN_VALUE, 3, _IS_BOOL, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_OBJ_INFO(0, key, V8\\NameValue, 0)
                ZEND_ARG_OBJ_INFO(0, value, V8\\Value, 0)
                ZEND_ARG_INFO(0, attributes)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_get, ZEND_RETURN_VALUE, 2, V8\\Value, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_OBJ_INFO(0, key, V8\\Value, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_getPropertyAttributes, ZEND_RETURN_VALUE, 2, IS_LONG, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_OBJ_INFO(0, key, V8\\StringValue, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_getOwnPropertyDescriptor, ZEND_RETURN_VALUE, 2, V8\\Value, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_OBJ_INFO(0, key, V8\\StringValue, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_has, ZEND_RETURN_VALUE, 2, _IS_BOOL, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_OBJ_INFO(0, key, V8\\Value, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_delete, ZEND_RETURN_VALUE, 2, _IS_BOOL, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_OBJ_INFO(0, key, V8\\Value, 0)
ZEND_END_ARG_INFO()

// bool
PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_setAccessor, ZEND_RETURN_VALUE, 3, _IS_BOOL, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_OBJ_INFO(0, name, V8\\NameValue, 0)
                ZEND_ARG_CALLABLE_INFO(0, getter, 0)
                ZEND_ARG_CALLABLE_INFO(0, setter, 1)
                ZEND_ARG_TYPE_INFO(0, settings, IS_LONG, 0)
                ZEND_ARG_TYPE_INFO(0, attributes, IS_LONG, 0)
ZEND_END_ARG_INFO()

//void method
PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_VOID_INFO_EX(arginfo_setAccessorProperty, 2)
                ZEND_ARG_OBJ_INFO(0, name, V8\\NameValue, 0)
                ZEND_ARG_OBJ_INFO(0, getter, V8\\FunctionObject, 0)
                ZEND_ARG_OBJ_INFO(0, setter, V8\\FunctionObject, 0)
                ZEND_ARG_TYPE_INFO(0, attributes, IS_LONG, 0)
                ZEND_ARG_TYPE_INFO(0, settings, IS_LONG, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_setNativeDataProperty, ZEND_RETURN_VALUE, 3, _IS_BOOL, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_OBJ_INFO(0, name, V8\\NameValue, 0)
                ZEND_ARG_CALLABLE_INFO(0, getter, 0)
                ZEND_ARG_CALLABLE_INFO(0, setter, 1)
                ZEND_ARG_TYPE_INFO(0, attributes, IS_LONG, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_getPropertyNames, ZEND_RETURN_VALUE, 1, V8\\ArrayObject, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_TYPE_INFO(0, mode, IS_LONG, 0)
                ZEND_ARG_TYPE_INFO(0, property_filter, IS_LONG, 0)
                ZEND_ARG_TYPE_INFO(0, index_filter, IS_LONG, 0)
                ZEND_ARG_TYPE_INFO(0, convert_to_strings, _IS_BOOL, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_getOwnPropertyNames, ZEND_RETURN_VALUE, 1, V8\\ArrayObject, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_TYPE_INFO(0, filter, IS_LONG, 0)
                ZEND_ARG_TYPE_INFO(0, convert_to_strings, _IS_BOOL, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_getPrototype, ZEND_RETURN_VALUE, 0, V8\\Value, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_setPrototype, ZEND_RETURN_VALUE, 2, _IS_BOOL, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_OBJ_INFO(0, prototype, V8\\Value, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_findInstanceInPrototypeChain, ZEND_RETURN_VALUE, 1, V8\\ObjectValue, 0)
                ZEND_ARG_OBJ_INFO(0, tmpl, V8\\FunctionTemplate, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_objectProtoToString, ZEND_RETURN_VALUE, 1, V8\\StringValue, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_getConstructorName, ZEND_RETURN_VALUE, 0, V8\\StringValue, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_setIntegrityLevel, ZEND_RETURN_VALUE, 2, _IS_BOOL, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_TYPE_INFO(0, level, IS_LONG, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_hasOwnProperty, ZEND_RETURN_VALUE, 2, _IS_BOOL, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_OBJ_INFO(0, key, V8\\NameValue, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_hasRealNamedProperty, ZEND_RETURN_VALUE, 2, _IS_BOOL, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_OBJ_INFO(0, key, V8\\NameValue, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_hasRealIndexedProperty, ZEND_RETURN_VALUE, 2, _IS_BOOL, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_TYPE_INFO(0, index, IS_LONG, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_hasRealNamedCallbackProperty, ZEND_RETURN_VALUE, 2, _IS_BOOL, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_OBJ_INFO(0, key, V8\\NameValue, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_getRealNamedPropertyInPrototypeChain, ZEND_RETURN_VALUE, 2, V8\\Value, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_OBJ_INFO(0, key, V8\\NameValue, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_getRealNamedPropertyAttributesInPrototypeChain, ZEND_RETURN_VALUE, 2, IS_LONG, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_OBJ_INFO(0, key, V8\\NameValue, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_getRealNamedProperty, ZEND_RETURN_VALUE, 2, V8\\Value, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_OBJ_INFO(0, key, V8\\NameValue, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_getRealNamedPropertyAttributes, ZEND_RETURN_VALUE, 2, IS_LONG, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_OBJ_INFO(0, key, V8\\NameValue, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_hasNamedLookupInterceptor, ZEND_RETURN_VALUE, 0, _IS_BOOL, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_hasIndexedLookupInterceptor, ZEND_RETURN_VALUE, 0, _IS_BOOL, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_getIdentityHash, ZEND_RETURN_VALUE, 0, IS_LONG, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_clone, ZEND_RETURN_VALUE, 0, V8\\ObjectValue, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_isCallable, ZEND_RETURN_VALUE, 0, _IS_BOOL, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_isConstructor, ZEND_RETURN_VALUE, 0, _IS_BOOL, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_callAsFunction, ZEND_RETURN_VALUE, 2, V8\\Value, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_OBJ_INFO(0, recv, V8\\Value, 0)
                ZEND_ARG_ARRAY_INFO(0, arguments, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_callAsConstructor, ZEND_RETURN_VALUE, 1, V8\\Value, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_ARRAY_INFO(0, arguments, 0)
ZEND_END_ARG_INFO()

// static methods

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_adjustExternalAllocatedMemory, ZEND_RETURN_VALUE, 1, IS_LONG, 0)
                ZEND_ARG_TYPE_INFO(0, change_in_bytes, IS_LONG, 0)
ZEND_END_ARG_INFO()


PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_getExternalAllocatedMemory, ZEND_RETURN_VALUE, 0, IS_LONG, 0)
ZEND_END_ARG_INFO()


static const zend_function_entry php_v8_object_methods[] = {
        PHP_V8_ME(Object, __construct,                  ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)
        PHP_V8_ME(Object, getContext,                   ZEND_ACC_PUBLIC)
        PHP_V8_ME(Object, set,                          ZEND_ACC_PUBLIC)
        PHP_V8_ME(Object, createDataProperty,           ZEND_ACC_PUBLIC)
        PHP_V8_ME(Object, defineOwnProperty,            ZEND_ACC_PUBLIC)
        PHP_V8_ME(Object, get,                          ZEND_ACC_PUBLIC)
        PHP_V8_ME(Object, getPropertyAttributes,        ZEND_ACC_PUBLIC)
        PHP_V8_ME(Object, getOwnPropertyDescriptor,     ZEND_ACC_PUBLIC)
        PHP_V8_ME(Object, has,                          ZEND_ACC_PUBLIC)
        PHP_V8_ME(Object, delete,                       ZEND_ACC_PUBLIC)
        PHP_V8_ME(Object, setAccessor,                  ZEND_ACC_PUBLIC)
        PHP_V8_ME(Object, setAccessorProperty,          ZEND_ACC_PUBLIC)
        PHP_V8_ME(Object, setNativeDataProperty,        ZEND_ACC_PUBLIC)
        PHP_V8_ME(Object, getPropertyNames,             ZEND_ACC_PUBLIC)
        PHP_V8_ME(Object, getOwnPropertyNames,          ZEND_ACC_PUBLIC)
        PHP_V8_ME(Object, getPrototype,                 ZEND_ACC_PUBLIC)
        PHP_V8_ME(Object, setPrototype,                 ZEND_ACC_PUBLIC)
        PHP_V8_ME(Object, findInstanceInPrototypeChain, ZEND_ACC_PUBLIC)
        PHP_V8_ME(Object, objectProtoToString,          ZEND_ACC_PUBLIC)
        PHP_V8_ME(Object, getConstructorName,           ZEND_ACC_PUBLIC)
        PHP_V8_ME(Object, setIntegrityLevel,            ZEND_ACC_PUBLIC)
        PHP_V8_ME(Object, hasOwnProperty,               ZEND_ACC_PUBLIC)
        PHP_V8_ME(Object, hasRealNamedProperty,         ZEND_ACC_PUBLIC)
        PHP_V8_ME(Object, hasRealIndexedProperty,       ZEND_ACC_PUBLIC)
        PHP_V8_ME(Object, hasRealNamedCallbackProperty, ZEND_ACC_PUBLIC)

        PHP_V8_ME(Object, getRealNamedPropertyInPrototypeChain,           ZEND_ACC_PUBLIC)
        PHP_V8_ME(Object, getRealNamedPropertyAttributesInPrototypeChain, ZEND_ACC_PUBLIC)
        PHP_V8_ME(Object, getRealNamedProperty,                           ZEND_ACC_PUBLIC)
        PHP_V8_ME(Object, getRealNamedPropertyAttributes,                 ZEND_ACC_PUBLIC)

        PHP_V8_ME(Object, hasNamedLookupInterceptor,   ZEND_ACC_PUBLIC)
        PHP_V8_ME(Object, hasIndexedLookupInterceptor, ZEND_ACC_PUBLIC)
        PHP_V8_ME(Object, getIdentityHash,             ZEND_ACC_PUBLIC)
        PHP_V8_ME(Object, clone,                       ZEND_ACC_PUBLIC)

        PHP_V8_ME(Object, isCallable,        ZEND_ACC_PUBLIC)
        PHP_V8_ME(Object, isConstructor,     ZEND_ACC_PUBLIC)
        PHP_V8_ME(Object, callAsFunction,    ZEND_ACC_PUBLIC)
        PHP_V8_ME(Object, callAsConstructor, ZEND_ACC_PUBLIC)

        PHP_V8_ME(Object, adjustExternalAllocatedMemory, ZEND_ACC_PUBLIC)
        PHP_V8_ME(Object, getExternalAllocatedMemory,    ZEND_ACC_PUBLIC)

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
