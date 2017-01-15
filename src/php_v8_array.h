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

#ifndef PHP_V8_ARRAY_H
#define PHP_V8_ARRAY_H

#include "php_v8_value.h"
#include <v8.h>

extern "C" {
#include "php.h"

#ifdef ZTS
#include "TSRM.h"
#endif
}

extern zend_class_entry* php_v8_array_class_entry;

extern v8::Local<v8::Array> php_v8_value_get_array_local(v8::Isolate *isolate, php_v8_value_t *php_v8_value);


PHP_MINIT_FUNCTION(php_v8_array);

#endif //PHP_V8_ARRAY_H
