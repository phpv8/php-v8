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

#include "php_v8_array.h"
#include "php_v8_object.h"
#include "php_v8_int32.h"
#include "php_v8_context.h"
#include "php_v8_value.h"
#include "php_v8.h"

zend_class_entry *php_v8_array_class_entry;
#define this_ce php_v8_array_class_entry


static PHP_METHOD(Array, __construct) {
    zval rv;
    zval *php_v8_context_zv;

    zend_long length = 0;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o|l", &php_v8_context_zv, &length) == FAILURE) {
        return;
    }

    PHP_V8_OBJECT_CONSTRUCT(getThis(), php_v8_context_zv, php_v8_context, php_v8_value);

    PHP_V8_CHECK_INT32_RANGE(length, "Length is out of range");

    v8::Local<v8::Array> local_array = v8::Array::New(isolate, static_cast<int>(length));

    PHP_V8_THROW_VALUE_EXCEPTION_WHEN_EMPTY(local_array, "Failed to create Array value");

    php_v8_object_store_self_ptr(php_v8_value, local_array);

    php_v8_value->persistent->Reset(isolate, local_array);
}

static PHP_METHOD(Array, length) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);

    RETURN_LONG(static_cast<zend_long >(php_v8_value_get_local_as<v8::Array>(php_v8_value)->Length()));
}


PHP_V8_ZEND_BEGIN_ARG_WITH_CONSTRUCTOR_INFO_EX(arginfo___construct, 1)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_TYPE_INFO(0, length, IS_LONG, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_length, ZEND_RETURN_VALUE, 0, IS_LONG, 0)
ZEND_END_ARG_INFO()


static const zend_function_entry php_v8_array_methods[] = {
        PHP_V8_ME(Array, __construct, ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)
        PHP_V8_ME(Array, length,      ZEND_ACC_PUBLIC)

        PHP_FE_END
};


PHP_MINIT_FUNCTION(php_v8_array) {
    zend_class_entry ce;
    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS, "ArrayObject", php_v8_array_methods);
    this_ce = zend_register_internal_class_ex(&ce, php_v8_object_class_entry);

    return SUCCESS;
}
