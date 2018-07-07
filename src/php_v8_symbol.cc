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

#include "php_v8_symbol.h"
#include "php_v8_name.h"
#include "php_v8_string.h"
#include "php_v8_value.h"
#include "php_v8.h"

zend_class_entry* php_v8_symbol_class_entry;
#define this_ce php_v8_symbol_class_entry


static PHP_METHOD(Symbol, __construct) {
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

        local_name = php_v8_value_get_local_as<v8::String>(php_v8_string);
    }

    v8::Local<v8::Symbol> local_symbol = v8::Symbol::New(isolate, local_name);

    if (local_symbol.IsEmpty()) {
        PHP_V8_THROW_VALUE_EXCEPTION("Failed to create Symbol value");
        return;
    }

    php_v8_value->persistent->Reset(isolate, local_symbol);
}

static PHP_METHOD(Symbol, value)
{
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);

    v8::Local<v8::Symbol> local_symbol = php_v8_value_get_local_as<v8::Symbol>(php_v8_value);
    v8::Local<v8::Value> local_name = local_symbol->Name();

    if (local_name->IsUndefined()) {
        RETURN_EMPTY_STRING();
    }

    v8::String::Utf8Value str(isolate, local_name);

    PHP_V8_CONVERT_UTF8VALUE_TO_STRING_WITH_CHECK(str, cstr);

    RETVAL_STRINGL(cstr, str.length());
}

static PHP_METHOD(Symbol, name)
{
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);

    v8::Local<v8::Symbol> local_symbol = php_v8_value_get_local_as<v8::Symbol>(php_v8_value);
    v8::Local<v8::Value> local_name = local_symbol->Name();

    php_v8_get_or_create_value(return_value, local_name, php_v8_value->php_v8_isolate);
}

static PHP_METHOD(Symbol, createFor)
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

    v8::Local<v8::String> local_string = php_v8_value_get_local_as<v8::String>(php_v8_value);
    v8::Local<v8::Symbol> local_symbol = v8::Symbol::For(isolate, local_string);

    PHP_V8_THROW_VALUE_EXCEPTION_WHEN_EMPTY(local_symbol, "Failed to create Symbol value");

    php_v8_get_or_create_value(return_value, local_symbol, php_v8_context->php_v8_isolate);
}

static PHP_METHOD(Symbol, createForApi)
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

    v8::Local<v8::String> local_string = php_v8_value_get_local_as<v8::String>(php_v8_value);
    v8::Local<v8::Symbol> local_symbol = v8::Symbol::ForApi(isolate, local_string);

    PHP_V8_THROW_VALUE_EXCEPTION_WHEN_EMPTY(local_symbol, "Failed to create Symbol value");

    php_v8_get_or_create_value(return_value, local_symbol, php_v8_context->php_v8_isolate);
}

// Well-known symbols

#define PHP_V8_SYMBOL_WELL_KNOWN_METHOD(classname, name)                                        \
    PHP_METHOD(classname, get##name##Symbol)                                                            \
    {                                                                                           \
        zval *php_v8_isolate_zv;                                                                \
                                                                                                \
        if (zend_parse_parameters(ZEND_NUM_ARGS(), "o", &php_v8_isolate_zv) == FAILURE) {       \
            return;                                                                             \
        }                                                                                       \
                                                                                                \
        PHP_V8_ISOLATE_FETCH_WITH_CHECK(php_v8_isolate_zv, php_v8_isolate);                     \
        PHP_V8_ENTER_ISOLATE(php_v8_isolate);                                                   \
                                                                                                \
        v8::Local<v8::Symbol> local_symbol = v8::Symbol::Get##name(isolate);                    \
                                                                                                \
        PHP_V8_THROW_VALUE_EXCEPTION_WHEN_EMPTY(local_symbol, "Failed to create Symbol value"); \
                                                                                                \
        php_v8_get_or_create_value(return_value, local_symbol, php_v8_isolate);                 \
    }                                                                                           \

static PHP_V8_SYMBOL_WELL_KNOWN_METHOD(Symbol, HasInstance);
static PHP_V8_SYMBOL_WELL_KNOWN_METHOD(Symbol, IsConcatSpreadable);
static PHP_V8_SYMBOL_WELL_KNOWN_METHOD(Symbol, Iterator);
static PHP_V8_SYMBOL_WELL_KNOWN_METHOD(Symbol, Match);
static PHP_V8_SYMBOL_WELL_KNOWN_METHOD(Symbol, Replace);
static PHP_V8_SYMBOL_WELL_KNOWN_METHOD(Symbol, Search);
static PHP_V8_SYMBOL_WELL_KNOWN_METHOD(Symbol, Split);
static PHP_V8_SYMBOL_WELL_KNOWN_METHOD(Symbol, ToPrimitive);
static PHP_V8_SYMBOL_WELL_KNOWN_METHOD(Symbol, ToStringTag);
static PHP_V8_SYMBOL_WELL_KNOWN_METHOD(Symbol, Unscopables);


PHP_V8_ZEND_BEGIN_ARG_WITH_CONSTRUCTOR_INFO_EX(arginfo___construct, 1)
    ZEND_ARG_OBJ_INFO(0, isolate, V8\\Isolate, 0)
    ZEND_ARG_OBJ_INFO(0, name, V8\\StringValue, 1)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_value, ZEND_RETURN_VALUE, 0, IS_STRING, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_name, ZEND_RETURN_VALUE, 0, V8\\Value, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_createFor, ZEND_RETURN_VALUE, 2, V8\\SymbolValue, 0)
    ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
    ZEND_ARG_OBJ_INFO(0, name, V8\\StringValue, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_createForApi, ZEND_RETURN_VALUE, 2, V8\\SymbolValue, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_OBJ_INFO(0, name, V8\\StringValue, 0)
ZEND_END_ARG_INFO()

#define PHP_V8_SYMBOL_WELL_KNOWN_ARGS(name)                                                          \
    PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_##name##Symbol, ZEND_RETURN_VALUE, 1, V8\\SymbolValue, 0)    \
                    ZEND_ARG_OBJ_INFO(0, isolate, V8\\Isolate, 0)                                           \
    ZEND_END_ARG_INFO()                                                                                     \

// Well-known symbols
PHP_V8_SYMBOL_WELL_KNOWN_ARGS(getHasInstance);
PHP_V8_SYMBOL_WELL_KNOWN_ARGS(getIsConcatSpreadable);
PHP_V8_SYMBOL_WELL_KNOWN_ARGS(getIterator);
PHP_V8_SYMBOL_WELL_KNOWN_ARGS(getMatch);
PHP_V8_SYMBOL_WELL_KNOWN_ARGS(getReplace);
PHP_V8_SYMBOL_WELL_KNOWN_ARGS(getSearch);
PHP_V8_SYMBOL_WELL_KNOWN_ARGS(getSplit);
PHP_V8_SYMBOL_WELL_KNOWN_ARGS(getToPrimitive);
PHP_V8_SYMBOL_WELL_KNOWN_ARGS(getToStringTag);
PHP_V8_SYMBOL_WELL_KNOWN_ARGS(getUnscopables);


static const zend_function_entry php_v8_symbol_methods[] = {
    PHP_V8_ME(Symbol, __construct, ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)
    PHP_V8_ME(Symbol, value,       ZEND_ACC_PUBLIC)
    PHP_V8_ME(Symbol, name,        ZEND_ACC_PUBLIC)
    PHP_V8_ME(Symbol, createFor,         ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_V8_ME(Symbol, createForApi,      ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)

    // Well-known symbols
    PHP_V8_ME(Symbol, getHasInstanceSymbol,        ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_V8_ME(Symbol, getIsConcatSpreadableSymbol, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_V8_ME(Symbol, getIteratorSymbol,           ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_V8_ME(Symbol, getMatchSymbol,              ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_V8_ME(Symbol, getReplaceSymbol,            ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_V8_ME(Symbol, getSearchSymbol,             ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_V8_ME(Symbol, getSplitSymbol,              ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_V8_ME(Symbol, getToPrimitiveSymbol,        ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_V8_ME(Symbol, getToStringTagSymbol,        ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_V8_ME(Symbol, getUnscopablesSymbol,        ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)

    PHP_FE_END
};


PHP_MINIT_FUNCTION(php_v8_symbol)
{
    zend_class_entry ce;
    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS, "SymbolValue", php_v8_symbol_methods);
    this_ce = zend_register_internal_class_ex(&ce, php_v8_name_class_entry);

    return SUCCESS;
}
