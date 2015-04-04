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

#ifndef PHP_V8_INTEGER_H
#define PHP_V8_INTEGER_H

#include "php_v8_value.h"
#include <v8.h>

extern "C" {
#include "php.h"

#ifdef ZTS
#include "TSRM.h"
#endif
}

extern zend_class_entry* php_v8_integer_class_entry;

extern v8::Local<v8::Integer> php_v8_value_get_integer_local(v8::Isolate *isolate, php_v8_value_t *php_v8_value);

#define PHP_V8_CHECK_INTEGER_RANGE(val, message) \
    if ((val) > UINT32_MAX || (val) < INT32_MIN) { \
        PHP_V8_THROW_VALUE_EXCEPTION(message); \
        return; \
    }

PHP_MINIT_FUNCTION(php_v8_integer);

#endif //PHP_V8_INTEGER_H

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: noet sw=4 ts=4 fdm=marker
 * vim<600: noet sw=4 ts=4
 */





