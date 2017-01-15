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

#ifndef PHP_V8_FUNCTION_CALLBACK_INFO_H
#define PHP_V8_FUNCTION_CALLBACK_INFO_H

#include "php_v8_callback_info.h"
#include <v8.h>

extern "C" {
#include "php.h"

#ifdef ZTS
#include "TSRM.h"
#endif
}

extern zend_class_entry* php_v8_function_callback_info_class_entry;

extern php_v8_callback_info_t *php_v8_callback_info_create_from_info(zval *this_ptr, const v8::FunctionCallbackInfo<v8::Value>&args);


PHP_MINIT_FUNCTION (php_v8_function_callback_info);

#endif //PHP_V8_FUNCTION_CALLBACK_INFO_H
