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

#include "php_v8_integer.h"
#include "php_v8_number.h"
#include "php_v8_value.h"
#include "php_v8_isolate.h"
#include "php_v8.h"

zend_class_entry *php_v8_integer_class_entry;
#define this_ce php_v8_integer_class_entry


static PHP_METHOD(Integer, __construct) {
    zval *php_v8_isolate_zv;

    zend_long value = 0;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "ol", &php_v8_isolate_zv, &value) == FAILURE) {
        return;
    }

    PHP_V8_VALUE_CONSTRUCT(getThis(), php_v8_isolate_zv, php_v8_isolate, php_v8_value)

    PHP_V8_CHECK_INTEGER_RANGE(value, "Integer value to set is out of range");
    v8::Local<v8::Integer> local_value;

    if (value > INT32_MAX) {
        local_value = v8::Integer::NewFromUnsigned(isolate, static_cast<uint32_t>(value));
    } else {
        local_value = v8::Integer::New(isolate, static_cast<int32_t>(value));
    }

    PHP_V8_THROW_VALUE_EXCEPTION_WHEN_EMPTY(local_value, "Failed to create Integer value");

    php_v8_value->persistent->Reset(isolate, local_value);
}


static PHP_METHOD(Integer, value) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_ISOLATE(php_v8_value->php_v8_isolate);

    v8::Local<v8::Integer> local_number = php_v8_value_get_local_as<v8::Integer>(php_v8_value);

    RETVAL_LONG(local_number->Value());
}


ZEND_BEGIN_ARG_INFO_EX(arginfo_integer___construct, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 2)
                ZEND_ARG_OBJ_INFO(0, isolate, V8\\Isolate, 0)
                ZEND_ARG_TYPE_INFO(0, value, IS_LONG, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_integer_value, ZEND_RETURN_VALUE, 0, IS_LONG, 0)
ZEND_END_ARG_INFO()


static const zend_function_entry php_v8_integer_methods[] = {
        PHP_ME(Integer, __construct, arginfo_integer___construct, ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)
        PHP_ME(Integer, value, arginfo_integer_value, ZEND_ACC_PUBLIC)
        PHP_FE_END
};


PHP_MINIT_FUNCTION(php_v8_integer) {
    zend_class_entry ce;
    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS, "IntegerValue", php_v8_integer_methods);
    this_ce = zend_register_internal_class_ex(&ce, php_v8_number_class_entry);

    return SUCCESS;
}
