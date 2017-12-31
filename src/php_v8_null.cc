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

#include "php_v8_null.h"
#include "php_v8_primitive.h"
#include "php_v8_value.h"
#include "php_v8.h"

zend_class_entry *php_v8_null_class_entry;
#define this_ce php_v8_null_class_entry

static PHP_METHOD(NullValue, __construct) {
    zval *php_v8_isolate_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o", &php_v8_isolate_zv) == FAILURE) {
        return;
    }

    PHP_V8_VALUE_CONSTRUCT(getThis(), php_v8_isolate_zv, php_v8_isolate, php_v8_value);

    php_v8_value->persistent->Reset(isolate, v8::Null(isolate));
}

static PHP_METHOD(NullValue, value) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }
    
    RETURN_NULL()
}


PHP_V8_ZEND_BEGIN_ARG_WITH_CONSTRUCTOR_INFO_EX(arginfo___construct, 1)
                ZEND_ARG_OBJ_INFO(0, isolate, V8\\Isolate, 0)
ZEND_END_ARG_INFO()

// no strict typing as it returns NULL and null typehint doesn't work on PHP 7.1
ZEND_BEGIN_ARG_INFO_EX(arginfo_value, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 0)
ZEND_END_ARG_INFO()

static const zend_function_entry php_v8_null_methods[] = {
        PHP_V8_ME(NullValue, __construct, ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)
        PHP_V8_ME(NullValue, value,       ZEND_ACC_PUBLIC)
        PHP_FE_END
};

PHP_MINIT_FUNCTION(php_v8_null) {
    zend_class_entry ce;
    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS, "NullValue", php_v8_null_methods);
    this_ce = zend_register_internal_class_ex(&ce, php_v8_primitive_class_entry);

    return SUCCESS;
}
