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

#ifndef PHP_V8_SCRIPT_ORIGIN_OPTIONS_H
#define PHP_V8_SCRIPT_ORIGIN_OPTIONS_H

#include <v8.h>

extern "C" {
#include "php.h"

#ifdef ZTS
#include "TSRM.h"
#endif
}

extern zend_class_entry* php_v8_script_origin_options_class_entry;

extern void php_v8_create_script_origin_options(zval * return_value, v8::ScriptOriginOptions options);

#define PHP_V8_SCRIPT_ORIGIN_OPTION_IS_SHARED_CROSS_ORIGIN 1
#define PHP_V8_SCRIPT_ORIGIN_OPTION_IS_OPAQUE 1 << 1
#define PHP_V8_SCRIPT_ORIGIN_OPTION_IS_WASM 1 << 2
#define PHP_V8_SCRIPT_ORIGIN_OPTION_IS_MODULE 1 << 3

#define PHP_V8_SCRIPT_ORIGIN_OPTIONS ( 0                    \
    | PHP_V8_SCRIPT_ORIGIN_OPTION_IS_SHARED_CROSS_ORIGIN    \
    | PHP_V8_SCRIPT_ORIGIN_OPTION_IS_OPAQUE                 \
    | PHP_V8_SCRIPT_ORIGIN_OPTION_IS_WASM                   \
    | PHP_V8_SCRIPT_ORIGIN_OPTION_IS_MODULE                 \
)                                                           \


PHP_MINIT_FUNCTION (php_v8_script_origin_options);

#endif //PHP_V8_SCRIPT_ORIGIN_OPTIONS_H
