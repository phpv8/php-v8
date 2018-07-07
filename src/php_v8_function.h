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

#ifndef PHP_V8_FUNCTION_H
#define PHP_V8_FUNCTION_H

#include "php_v8_value.h"
#include <v8.h>

extern "C" {
#include "php.h"

#ifdef ZTS
#include "TSRM.h"
#endif
}

extern zend_class_entry* php_v8_function_class_entry;

extern bool php_v8_function_unpack_args(zval *arguments_zv, int arg_position, v8::Isolate *isolate, int *argc, v8::Local<v8::Value> **argv);
extern bool php_v8_function_unpack_string_args(zval* arguments_zv, int arg_position, v8::Isolate *isolate, int *argc, v8::Local<v8::String> **argv);
extern bool php_v8_function_unpack_object_args(zval* arguments_zv, int arg_position, v8::Isolate *isolate, int *argc, v8::Local<v8::Object> **argv);

#define PHP_V8_CHECK_FUNCTION_LENGTH_RANGE(val, message) \
    if ((val) > INT_MAX || (val) < INT_MIN) { \
        PHP_V8_THROW_VALUE_EXCEPTION(message); \
        return; \
    }


PHP_MINIT_FUNCTION(php_v8_function);

#endif //PHP_V8_FUNCTION_H
