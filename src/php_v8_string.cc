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

#include "php_v8_string.h"
#include "php_v8_name.h"
#include "php_v8_value.h"
#include "php_v8.h"

zend_class_entry* php_v8_string_class_entry;
#define this_ce php_v8_string_class_entry

v8::Local<v8::String> php_v8_value_get_string_local(v8::Isolate *isolate, php_v8_value_t *php_v8_value) {
    return v8::Local<v8::String>::Cast(php_v8_value_get_value_local(isolate, php_v8_value));
};


static PHP_METHOD(V8String, __construct) {
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

static PHP_METHOD(V8String, Value)
{
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_ISOLATE(php_v8_value->php_v8_isolate);

    v8::Local<v8::Value> str_tpl = php_v8_value_get_value_local(isolate, php_v8_value);

    v8::String::Utf8Value str(str_tpl);

    PHP_V8_CONVERT_UTF8VALUE_TO_STRING_WITH_CHECK(str, cstr);

    RETVAL_STRINGL(cstr, str.length());
}


static PHP_METHOD(V8String, Length)
{
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_ISOLATE(php_v8_value->php_v8_isolate);

    v8::Local<v8::String> str_tpl_checked = php_v8_value_get_string_local(isolate, php_v8_value);

    RETVAL_LONG(str_tpl_checked->Length());
}


static PHP_METHOD(V8String, Utf8Length)
{
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_ISOLATE(php_v8_value->php_v8_isolate);

    v8::Local<v8::String> str_tpl_checked = php_v8_value_get_string_local(isolate, php_v8_value);

    RETVAL_LONG(str_tpl_checked->Utf8Length());
}


static PHP_METHOD(V8String, IsOneByte)
{
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_ISOLATE(php_v8_value->php_v8_isolate);

    v8::Local<v8::String> str_tpl_checked = php_v8_value_get_string_local(isolate, php_v8_value);

    RETVAL_BOOL(str_tpl_checked->IsOneByte());
}


static PHP_METHOD(V8String, ContainsOnlyOneByte)
{
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_ISOLATE(php_v8_value->php_v8_isolate);

    v8::Local<v8::String> str_tpl_checked = php_v8_value_get_string_local(isolate, php_v8_value);

    RETVAL_BOOL(str_tpl_checked->ContainsOnlyOneByte());
}



ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_string___construct, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 2)
    ZEND_ARG_OBJ_INFO(0, isolate, V8\\Isolate, 0)
    ZEND_ARG_INFO(0, data)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_string_Value, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_string_Length, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_string_Utf8Length, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_string_IsOneByte, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_string_ContainsOnlyOneByte, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 0)
ZEND_END_ARG_INFO()


static const zend_function_entry php_v8_string_methods[] = {
    PHP_ME(V8String, __construct, arginfo_v8_string___construct, ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)
    PHP_ME(V8String, Value, arginfo_v8_string_Value, ZEND_ACC_PUBLIC)
    PHP_ME(V8String, Length, arginfo_v8_string_Length, ZEND_ACC_PUBLIC)
    PHP_ME(V8String, Utf8Length, arginfo_v8_string_Utf8Length, ZEND_ACC_PUBLIC)
    PHP_ME(V8String, IsOneByte, arginfo_v8_string_IsOneByte, ZEND_ACC_PUBLIC)
    PHP_ME(V8String, ContainsOnlyOneByte, arginfo_v8_string_ContainsOnlyOneByte, ZEND_ACC_PUBLIC)
    PHP_FE_END
};


PHP_MINIT_FUNCTION(php_v8_string)
{
    zend_class_entry ce;
    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS, "StringValue", php_v8_string_methods);
    this_ce = zend_register_internal_class_ex(&ce, php_v8_name_class_entry);

    zend_declare_class_constant_long(this_ce, ZEND_STRL("kMaxLength"), v8::String::kMaxLength);

    return SUCCESS;
}
