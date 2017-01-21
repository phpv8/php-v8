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

#include "php_v8_map.h"
#include "php_v8_object.h"
#include "php_v8_context.h"
#include "php_v8_value.h"
#include "php_v8.h"

zend_class_entry *php_v8_map_class_entry;
#define this_ce php_v8_map_class_entry

v8::Local<v8::Map> php_v8_value_get_map_local(v8::Isolate *isolate, php_v8_value_t *php_v8_value) {
    return v8::Local<v8::Map>::Cast(php_v8_value_get_value_local(isolate, php_v8_value));
};

static PHP_METHOD(V8Map, __construct) {
    zval rv;
    zval *php_v8_context_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o", &php_v8_context_zv) == FAILURE) {
        return;
    }

    PHP_V8_OBJECT_CONSTRUCT(getThis(), php_v8_context_zv, php_v8_context, php_v8_value);

    v8::Local<v8::Map> local_map = v8::Map::New(isolate);

    PHP_V8_THROW_VALUE_EXCEPTION_WHEN_EMPTY(local_map, "Failed to create Map value");

    ZVAL_COPY_VALUE(&php_v8_value->this_ptr, getThis());
    php_v8_object_store_self_ptr(isolate, local_map, php_v8_value);

    php_v8_value->persistent->Reset(isolate, local_map);
}

static PHP_METHOD(V8Map, Size) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);
    PHP_V8_ENTER_STORED_CONTEXT(php_v8_value);

    RETURN_DOUBLE(php_v8_value_get_map_local(isolate, php_v8_value)->Size());
}

static PHP_METHOD(V8Map, Clear) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);
    PHP_V8_ENTER_STORED_CONTEXT(php_v8_value);

    php_v8_value_get_map_local(isolate, php_v8_value)->Clear();
}

static PHP_METHOD(V8Map, Get) {
    zval *php_v8_context_zv;
    zval *php_v8_key_zv;
    v8::MaybeLocal<v8::Value> maybe_local;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "oo", &php_v8_context_zv, &php_v8_key_zv) == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_key_zv, php_v8_key);
    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_context);
    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_key);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    v8::Local<v8::Map> local_map = php_v8_value_get_map_local(isolate, php_v8_value);
    v8::Local<v8::Value> local_key = php_v8_value_get_value_local(isolate, php_v8_key);

    PHP_V8_TRY_CATCH(isolate);
    PHP_V8_INIT_ISOLATE_LIMITS_ON_OBJECT_VALUE(php_v8_value);

    maybe_local = local_map->Get(context, local_key);

    PHP_V8_MAYBE_CATCH(php_v8_context, try_catch);
    PHP_V8_THROW_EXCEPTION_WHEN_EMPTY(maybe_local, "Failed to get");

    v8::Local<v8::Value> local_value =  maybe_local.ToLocalChecked();

    php_v8_get_or_create_value(return_value, local_value, isolate);
}


static PHP_METHOD(V8Map, Set) {
    zval *php_v8_context_zv;
    zval *php_v8_key_zv;
    zval *php_v8_value_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "ooo", &php_v8_context_zv, &php_v8_key_zv, &php_v8_value_zv) == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_key_zv, php_v8_key);
    PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_value_zv, php_v8_value_value_to_set);
    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_context);
    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_key);
    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_value_value_to_set);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    v8::Local<v8::Map> local_map = php_v8_value_get_map_local(isolate, php_v8_value);
    v8::Local<v8::Value> local_key = php_v8_value_get_value_local(isolate, php_v8_key);
    v8::Local<v8::Value> local_value_to_set = php_v8_value_get_value_local(isolate, php_v8_value_value_to_set);

    PHP_V8_TRY_CATCH(isolate);
    PHP_V8_INIT_ISOLATE_LIMITS_ON_OBJECT_VALUE(php_v8_value);

    v8::MaybeLocal<v8::Map> maybe_local_res = local_map->Set(context, local_key, local_value_to_set);

    PHP_V8_MAYBE_CATCH(php_v8_context, try_catch);
    PHP_V8_THROW_EXCEPTION_WHEN_EMPTY(maybe_local_res, "Failed to set");

    RETVAL_ZVAL(&php_v8_value->this_ptr, 1, 0);
}


static PHP_METHOD(V8Map, Has) {
    zval *php_v8_context_zv;
    zval *php_v8_key_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "oo", &php_v8_context_zv, &php_v8_key_zv) == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_key_zv, php_v8_key);
    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_context);
    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_key);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    v8::Local<v8::Map> local_map = php_v8_value_get_map_local(isolate, php_v8_value);
    v8::Local<v8::Value> local_key = php_v8_value_get_value_local(isolate, php_v8_key);

    PHP_V8_TRY_CATCH(isolate);
    PHP_V8_INIT_ISOLATE_LIMITS_ON_OBJECT_VALUE(php_v8_value);

    v8::Maybe<bool> maybe_res = local_map->Has(context, local_key);

    PHP_V8_MAYBE_CATCH(php_v8_context, try_catch);
    PHP_V8_THROW_EXCEPTION_WHEN_NOTHING(maybe_res, "Failed to check");

    RETURN_BOOL(maybe_res.FromJust());
}

static PHP_METHOD(V8Map, Delete) {
    zval *php_v8_context_zv;
    zval *php_v8_key_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "oo", &php_v8_context_zv, &php_v8_key_zv) == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_key_zv, php_v8_key);
    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_context);
    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_key);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    v8::Local<v8::Map> local_map = php_v8_value_get_map_local(isolate, php_v8_value);
    v8::Local<v8::Value> local_key = php_v8_value_get_value_local(isolate, php_v8_key);

    PHP_V8_TRY_CATCH(isolate);
    PHP_V8_INIT_ISOLATE_LIMITS_ON_OBJECT_VALUE(php_v8_value);

    v8::Maybe<bool> maybe_res = local_map->Delete(context, local_key);

    PHP_V8_MAYBE_CATCH(php_v8_context, try_catch);
    PHP_V8_THROW_EXCEPTION_WHEN_NOTHING(maybe_res, "Failed to delete");

    RETURN_BOOL(maybe_res.FromJust());
}

static PHP_METHOD(V8Map, AsArray) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);
    PHP_V8_ENTER_STORED_CONTEXT(php_v8_value);

    PHP_V8_TRY_CATCH(isolate);
    PHP_V8_INIT_ISOLATE_LIMITS_ON_OBJECT_VALUE(php_v8_value);

    v8::Local<v8::Array> local_array = php_v8_value_get_map_local(isolate, php_v8_value)->AsArray();

    PHP_V8_MAYBE_CATCH(php_v8_value->php_v8_context, try_catch);
    PHP_V8_THROW_EXCEPTION_WHEN_EMPTY(local_array, "Failed to get property names")

    php_v8_get_or_create_value(return_value, local_array, isolate);
}


ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_map___construct, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 1)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_map_Size, ZEND_RETURN_VALUE, 0, IS_DOUBLE, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_map_Clear, ZEND_RETURN_VALUE, ZEND_RETURN_VALUE, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_v8_map_Get, ZEND_RETURN_VALUE, 2, V8\\Value, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_OBJ_INFO(0, key, V8\\Value, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_v8_map_Set, ZEND_RETURN_VALUE, 3, V8\\MapObject, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_OBJ_INFO(0, key, V8\\Value, 0)
                ZEND_ARG_OBJ_INFO(0, value, V8\\Value, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_map_Has, ZEND_RETURN_VALUE, 2, _IS_BOOL, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_OBJ_INFO(0, key, V8\\Value, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_map_Delete, ZEND_RETURN_VALUE, 2, _IS_BOOL, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_OBJ_INFO(0, key, V8\\Value, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_v8_map_AsArray, ZEND_RETURN_VALUE, 0, V8\\ArrayObject, 0)
ZEND_END_ARG_INFO()


static const zend_function_entry php_v8_map_methods[] = {
        PHP_ME(V8Map, __construct, arginfo_v8_map___construct, ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)

        PHP_ME(V8Map, Size,     arginfo_v8_map_Size,    ZEND_ACC_PUBLIC)
        PHP_ME(V8Map, Clear,    arginfo_v8_map_Clear,   ZEND_ACC_PUBLIC)

        PHP_ME(V8Map, Get,      arginfo_v8_map_Get,     ZEND_ACC_PUBLIC)
        PHP_ME(V8Map, Set,      arginfo_v8_map_Set,     ZEND_ACC_PUBLIC)
        PHP_ME(V8Map, Has,      arginfo_v8_map_Has,     ZEND_ACC_PUBLIC)
        PHP_ME(V8Map, Delete,   arginfo_v8_map_Delete,  ZEND_ACC_PUBLIC)

        PHP_ME(V8Map, AsArray,  arginfo_v8_map_AsArray,  ZEND_ACC_PUBLIC)

        PHP_FE_END
};


PHP_MINIT_FUNCTION(php_v8_map) {
    zend_class_entry ce;
    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS, "MapObject", php_v8_map_methods);
    this_ce = zend_register_internal_class_ex(&ce, php_v8_object_class_entry);

    return SUCCESS;
}
