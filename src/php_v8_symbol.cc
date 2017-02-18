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

#include "php_v8_symbol.h"
#include "php_v8_name.h"
#include "php_v8_string.h"
#include "php_v8_value.h"
#include "php_v8.h"

zend_class_entry* php_v8_symbol_class_entry;
#define this_ce php_v8_symbol_class_entry

v8::Local<v8::Symbol> php_v8_value_get_symbol_local(v8::Isolate *isolate, php_v8_value_t *php_v8_value) {
    return v8::Local<v8::Symbol>::Cast(php_v8_value_get_value_local(isolate, php_v8_value));
};


static PHP_METHOD(V8Symbol, __construct) {
    zval *php_v8_isolate_zv;
    zval *php_v8_string_zv = NULL;

    v8::Local<v8::String> local_name = v8::Local<v8::String>();

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o|o!", &php_v8_isolate_zv, &php_v8_string_zv) == FAILURE) {
        return;
    }

    PHP_V8_VALUE_CONSTRUCT(getThis(), php_v8_isolate_zv, php_v8_isolate, php_v8_value);

    if (NULL != php_v8_string_zv) {
        PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_string_zv, php_v8_string);
        PHP_V8_DATA_ISOLATES_CHECK(php_v8_string, php_v8_value);

        local_name = php_v8_value_get_string_local(isolate, php_v8_string);
    }

    v8::Local<v8::Symbol> local_symbol = v8::Symbol::New(isolate, local_name);

    if (local_symbol.IsEmpty()) {
        PHP_V8_THROW_VALUE_EXCEPTION("Failed to create Symbol value");
        return;
    }

    php_v8_value->persistent->Reset(isolate, local_symbol);
}

static PHP_METHOD(V8Symbol, Name)
{
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);

    v8::Local<v8::Symbol> local_symbol = php_v8_value_get_symbol_local(isolate, php_v8_value);
    v8::Local<v8::Value> local_name = local_symbol->Name();

    php_v8_get_or_create_value(return_value, local_name, php_v8_value->php_v8_isolate);
}

static PHP_METHOD(V8Symbol, For)
{
    zval *php_v8_context_zv;
    zval *php_v8_name_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "oo", &php_v8_context_zv, &php_v8_name_zv) == FAILURE) {
        return;
    }

    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);
    PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_name_zv, php_v8_value);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_context);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    v8::Local<v8::String> local_string = php_v8_value_get_string_local(isolate, php_v8_value);
    v8::Local<v8::Symbol> local_symbol = v8::Symbol::For(isolate, local_string);

    PHP_V8_THROW_VALUE_EXCEPTION_WHEN_EMPTY(local_symbol, "Failed to create Symbol value");

    php_v8_get_or_create_value(return_value, local_symbol, php_v8_context->php_v8_isolate);
}

static PHP_METHOD(V8Symbol, ForApi)
{
    zval *php_v8_context_zv;
    zval *php_v8_name_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "oo", &php_v8_context_zv, &php_v8_name_zv) == FAILURE) {
        return;
    }

    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);
    PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_name_zv, php_v8_value);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_context);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    v8::Local<v8::String> local_string = php_v8_value_get_string_local(isolate, php_v8_value);
    v8::Local<v8::Symbol> local_symbol = v8::Symbol::ForApi(isolate, local_string);

    PHP_V8_THROW_VALUE_EXCEPTION_WHEN_EMPTY(local_symbol, "Failed to create Symbol value");

    php_v8_get_or_create_value(return_value, local_symbol, php_v8_context->php_v8_isolate);
}

static PHP_METHOD(V8Symbol, GetIterator)
{
    zval *php_v8_isolate_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o", &php_v8_isolate_zv) == FAILURE) {
        return;
    }

    PHP_V8_ISOLATE_FETCH_WITH_CHECK(php_v8_isolate_zv, php_v8_isolate);
    PHP_V8_ENTER_ISOLATE(php_v8_isolate);

    v8::Local<v8::Symbol> local_symbol = v8::Symbol::GetIterator(isolate);

    PHP_V8_THROW_VALUE_EXCEPTION_WHEN_EMPTY(local_symbol, "Failed to create Symbol value");

    php_v8_get_or_create_value(return_value, local_symbol, php_v8_isolate);
}

static PHP_METHOD(V8Symbol, GetUnscopables)
{
    zval *php_v8_isolate_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o", &php_v8_isolate_zv) == FAILURE) {
        return;
    }

    PHP_V8_ISOLATE_FETCH_WITH_CHECK(php_v8_isolate_zv, php_v8_isolate);
    PHP_V8_ENTER_ISOLATE(php_v8_isolate);

    v8::Local<v8::Symbol> local_symbol = v8::Symbol::GetUnscopables(isolate);

    PHP_V8_THROW_VALUE_EXCEPTION_WHEN_EMPTY(local_symbol, "Failed to create Symbol value");

    php_v8_get_or_create_value(return_value, local_symbol, php_v8_isolate);
}

static PHP_METHOD(V8Symbol, GetToPrimitive)
{
    zval *php_v8_isolate_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o", &php_v8_isolate_zv) == FAILURE) {
        return;
    }

    PHP_V8_ISOLATE_FETCH_WITH_CHECK(php_v8_isolate_zv, php_v8_isolate);
    PHP_V8_ENTER_ISOLATE(php_v8_isolate);

    v8::Local<v8::Symbol> local_symbol = v8::Symbol::GetToPrimitive(isolate);

    PHP_V8_THROW_VALUE_EXCEPTION_WHEN_EMPTY(local_symbol, "Failed to create Symbol value");

    php_v8_get_or_create_value(return_value, local_symbol, php_v8_isolate);
}

static PHP_METHOD(V8Symbol, GetToStringTag)
{
    zval *php_v8_isolate_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o", &php_v8_isolate_zv) == FAILURE) {
        return;
    }

    PHP_V8_ISOLATE_FETCH_WITH_CHECK(php_v8_isolate_zv, php_v8_isolate);
    PHP_V8_ENTER_ISOLATE(php_v8_isolate);

    v8::Local<v8::Symbol> local_symbol = v8::Symbol::GetToStringTag(isolate);

    PHP_V8_THROW_VALUE_EXCEPTION_WHEN_EMPTY(local_symbol, "Failed to create Symbol value");

    php_v8_get_or_create_value(return_value, local_symbol, php_v8_isolate);
}

static PHP_METHOD(V8Symbol, GetIsConcatSpreadable)
{
    zval *php_v8_isolate_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o", &php_v8_isolate_zv) == FAILURE) {
        return;
    }

    PHP_V8_ISOLATE_FETCH_WITH_CHECK(php_v8_isolate_zv, php_v8_isolate);
    PHP_V8_ENTER_ISOLATE(php_v8_isolate);

    v8::Local<v8::Symbol> local_symbol = v8::Symbol::GetIsConcatSpreadable(isolate);

    PHP_V8_THROW_VALUE_EXCEPTION_WHEN_EMPTY(local_symbol, "Failed to create Symbol value");

    php_v8_get_or_create_value(return_value, local_symbol, php_v8_isolate);
}


ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_symbol___construct, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 1)
    ZEND_ARG_OBJ_INFO(0, isolate, V8\\Isolate, 0)
    ZEND_ARG_OBJ_INFO(0, name, V8\\StringValue, 1)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_v8_symbol_Name, ZEND_RETURN_VALUE, 0, V8\\Value, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_v8_symbol_For, ZEND_RETURN_VALUE, 2, V8\\SymbolValue, 0)
    ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
    ZEND_ARG_OBJ_INFO(0, name, V8\\StringValue, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_v8_symbol_ForApi, ZEND_RETURN_VALUE, 2, V8\\SymbolValue, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_OBJ_INFO(0, name, V8\\StringValue, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_v8_symbol_GetIterator, ZEND_RETURN_VALUE, 1, V8\\SymbolValue, 0)
                ZEND_ARG_OBJ_INFO(0, isolate, V8\\Isolate, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_v8_symbol_GetUnscopables, ZEND_RETURN_VALUE, 1, V8\\SymbolValue, 0)
                ZEND_ARG_OBJ_INFO(0, isolate, V8\\Isolate, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_v8_symbol_GetToPrimitive, ZEND_RETURN_VALUE, 1, V8\\SymbolValue, 0)
                ZEND_ARG_OBJ_INFO(0, isolate, V8\\Isolate, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_v8_symbol_GetToStringTag, ZEND_RETURN_VALUE, 1, V8\\SymbolValue, 0)
                ZEND_ARG_OBJ_INFO(0, isolate, V8\\Isolate, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_v8_symbol_GetIsConcatSpreadable, ZEND_RETURN_VALUE, 1, V8\\SymbolValue, 0)
                ZEND_ARG_OBJ_INFO(0, isolate, V8\\Isolate, 0)
ZEND_END_ARG_INFO()


static const zend_function_entry php_v8_symbol_methods[] = {
    PHP_ME(V8Symbol, __construct, arginfo_v8_symbol___construct, ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)

    PHP_ME(V8Symbol, Name, arginfo_v8_symbol_Name, ZEND_ACC_PUBLIC)

    PHP_ME(V8Symbol, For,       arginfo_v8_symbol_For, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(V8Symbol, ForApi,    arginfo_v8_symbol_ForApi, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)

    PHP_ME(V8Symbol, GetIterator,           arginfo_v8_symbol_GetIterator, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(V8Symbol, GetUnscopables,        arginfo_v8_symbol_GetUnscopables, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(V8Symbol, GetToPrimitive,        arginfo_v8_symbol_GetToPrimitive, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(V8Symbol, GetToStringTag,        arginfo_v8_symbol_GetToStringTag, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(V8Symbol, GetIsConcatSpreadable, arginfo_v8_symbol_GetIsConcatSpreadable, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)

    PHP_FE_END
};


PHP_MINIT_FUNCTION(php_v8_symbol)
{
    zend_class_entry ce;
    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS, "SymbolValue", php_v8_symbol_methods);
    this_ce = zend_register_internal_class_ex(&ce, php_v8_name_class_entry);

    return SUCCESS;
}
