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

extern v8::Local<v8::Function> php_v8_value_get_function_local(v8::Isolate *isolate, php_v8_value_t *php_v8_value);
extern bool php_v8_function_unpack_args(zval* arguments_zv, zval *this_ptr, int arg_position, v8::Isolate *isolate, int *argc, v8::Local<v8::Value> **argv);

#define PHP_V8_CHECK_FUNCTION_LENGTH_RANGE(val, message) \
    if ((val) > INT_MAX || (val) < INT_MIN) { \
        PHP_V8_THROW_VALUE_EXCEPTION(message); \
        return; \
    }


PHP_MINIT_FUNCTION(php_v8_function);

#endif //PHP_V8_FUNCTION_H

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: noet sw=4 ts=4 fdm=marker
 * vim<600: noet sw=4 ts=4
 */



