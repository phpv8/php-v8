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

#ifndef PHP_V8_STRING_H
#define PHP_V8_STRING_H

#include "php_v8_exceptions.h"
#include "php_v8_value.h"
#include "php_v8.h"
#include <v8.h>

extern "C" {
#include "php.h"

#ifdef ZTS
#include "TSRM.h"
#endif
}

extern zend_class_entry* php_v8_string_class_entry;

extern v8::Local<v8::String> php_v8_value_get_string_local(v8::Isolate *isolate, php_v8_value_t *php_v8_value);

#define MAYBE_ZSTR_VAL(zstr) ((zstr) ? ZSTR_VAL(zstr) : "")
#define MAYBE_ZSTR_LEN(zstr) ((zstr) ? ZSTR_LEN(zstr) : 0)

#define PHP_V8_CHECK_STRING_RANGE(str, message) \
    if (MAYBE_ZSTR_LEN(str) > v8::String::kMaxLength) { \
        PHP_V8_THROW_VALUE_EXCEPTION(message); \
        return; \
    }

PHP_MINIT_FUNCTION(php_v8_string);

#endif //PHP_V8_STRING_H
