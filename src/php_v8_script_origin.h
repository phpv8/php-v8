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

#ifndef PHP_V8_SCRIPT_ORIGIN_H
#define PHP_V8_SCRIPT_ORIGIN_H

#include <v8.h>

extern "C" {
#include "php.h"

#ifdef ZTS
#include "TSRM.h"
#endif
}

extern zend_class_entry* php_v8_script_origin_class_entry;

extern void php_v8_create_script_origin(zval * return_value, v8::Local<v8::Context> context, v8::ScriptOrigin origin);
extern v8::ScriptOrigin *php_v8_create_script_origin_from_zval(zval *value, v8::Isolate *isolate);

PHP_MINIT_FUNCTION (php_v8_script_origin);

#endif //PHP_V8_SCRIPT_ORIGIN_H
/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: noet sw=4 ts=4 fdm=marker
 * vim<600: noet sw=4 ts=4
 */





