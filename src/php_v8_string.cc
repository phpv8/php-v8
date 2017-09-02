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

#include "php_v8_string.h"
#include "php_v8_name.h"
#include "php_v8_value.h"
#include "php_v8.h"

zend_class_entry* php_v8_string_class_entry;
#define this_ce php_v8_string_class_entry


static PHP_METHOD(String, __construct) {
    zval *php_v8_isolate_zv;

    zend_string *string = NULL;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o|S", &php_v8_isolate_zv, &string) == FAILURE) {
        return;
    }

    PHP_V8_VALUE_CONSTRUCT(getThis(), php_v8_isolate_zv, php_v8_isolate, php_v8_value);

    PHP_V8_CHECK_STRING_RANGE(string, "String is too long");

    v8::MaybeLocal<v8::String> maybe_str_tpl = v8::String::NewFromUtf8(isolate,
                                                                       MAYBE_ZSTR_VAL(string),
                                                                       v8::NewStringType::kNormal,
                                                                       static_cast<int>(MAYBE_ZSTR_LEN(string)));

    PHP_V8_THROW_VALUE_EXCEPTION_WHEN_EMPTY(maybe_str_tpl, "Failed to create String value");

    v8::Local<v8::String> str_tpl_checked = maybe_str_tpl.ToLocalChecked();

    php_v8_value->persistent->Reset(isolate, str_tpl_checked);
}

static PHP_METHOD(String, value)
{
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_ISOLATE(php_v8_value->php_v8_isolate);

    v8::Local<v8::Value> str_tpl = php_v8_value_get_local(php_v8_value);

    v8::String::Utf8Value str(str_tpl);

    PHP_V8_CONVERT_UTF8VALUE_TO_STRING_WITH_CHECK(str, cstr);

    RETVAL_STRINGL(cstr, str.length());
}


static PHP_METHOD(String, length)
{
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_ISOLATE(php_v8_value->php_v8_isolate);

    v8::Local<v8::String> str_tpl_checked = php_v8_value_get_local_as<v8::String>(php_v8_value);

    RETVAL_LONG(str_tpl_checked->Length());
}


static PHP_METHOD(String, utf8Length)
{
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_ISOLATE(php_v8_value->php_v8_isolate);

    v8::Local<v8::String> str_tpl_checked = php_v8_value_get_local_as<v8::String>(php_v8_value);

    RETVAL_LONG(str_tpl_checked->Utf8Length());
}


static PHP_METHOD(String, isOneByte)
{
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_ISOLATE(php_v8_value->php_v8_isolate);

    v8::Local<v8::String> str_tpl_checked = php_v8_value_get_local_as<v8::String>(php_v8_value);

    RETVAL_BOOL(str_tpl_checked->IsOneByte());
}


static PHP_METHOD(String, containsOnlyOneByte)
{
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_ISOLATE(php_v8_value->php_v8_isolate);

    v8::Local<v8::String> str_tpl_checked = php_v8_value_get_local_as<v8::String>(php_v8_value);

    RETVAL_BOOL(str_tpl_checked->ContainsOnlyOneByte());
}


PHP_V8_ZEND_BEGIN_ARG_WITH_CONSTRUCTOR_INFO_EX(arginfo___construct, 2)
    ZEND_ARG_OBJ_INFO(0, isolate, V8\\Isolate, 0)
    ZEND_ARG_INFO(0, data)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_value, ZEND_RETURN_VALUE, 0, IS_STRING, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_length, ZEND_RETURN_VALUE, 0, IS_LONG, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_utf8Length, ZEND_RETURN_VALUE, 0, IS_LONG, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_isOneByte, ZEND_RETURN_VALUE, 0, _IS_BOOL, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_containsOnlyOneByte, ZEND_RETURN_VALUE, 0, _IS_BOOL, 0)
ZEND_END_ARG_INFO()


static const zend_function_entry php_v8_string_methods[] = {
    PHP_V8_ME(String, __construct,         ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)
    PHP_V8_ME(String, value,               ZEND_ACC_PUBLIC)
    PHP_V8_ME(String, length,              ZEND_ACC_PUBLIC)
    PHP_V8_ME(String, utf8Length,          ZEND_ACC_PUBLIC)
    PHP_V8_ME(String, isOneByte,           ZEND_ACC_PUBLIC)
    PHP_V8_ME(String, containsOnlyOneByte, ZEND_ACC_PUBLIC)

    PHP_FE_END
};


PHP_MINIT_FUNCTION(php_v8_string)
{
    zend_class_entry ce;
    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS, "StringValue", php_v8_string_methods);
    this_ce = zend_register_internal_class_ex(&ce, php_v8_name_class_entry);

    zend_declare_class_constant_long(this_ce, ZEND_STRL("MAX_LENGTH"), v8::String::kMaxLength);

    return SUCCESS;
}
