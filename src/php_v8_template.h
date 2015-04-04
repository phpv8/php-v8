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

#ifndef PHP_V8_TEMPLATE_H
#define PHP_V8_TEMPLATE_H

extern "C" {
#include "php.h"

#ifdef ZTS
#include "TSRM.h"
#endif
}

extern zend_class_entry* php_v8_template_ce;

extern void php_v8_object_template_Set(INTERNAL_FUNCTION_PARAMETERS);
extern void php_v8_function_template_Set(INTERNAL_FUNCTION_PARAMETERS);

extern void php_v8_object_template_SetAccessorProperty(INTERNAL_FUNCTION_PARAMETERS);
extern void php_v8_function_template_SetAccessorProperty(INTERNAL_FUNCTION_PARAMETERS);

extern void php_v8_object_template_SetNativeDataProperty(INTERNAL_FUNCTION_PARAMETERS);
extern void php_v8_function_template_SetNativeDataProperty(INTERNAL_FUNCTION_PARAMETERS);

#define PHP_V8_TEMPLATE_STORE_ISOLATE(to_zval, from_isolate_zv) zend_update_property(php_v8_template_ce, (to_zval), ZEND_STRL("isolate"), (from_isolate_zv));
#define PHP_V8_TEMPLATE_READ_ISOLATE(from_zval) zend_read_property(php_v8_template_ce, (from_zval), ZEND_STRL("isolate"), 0, &rv)


PHP_MINIT_FUNCTION(php_v8_template);

#endif //PHP_V8_TEMPLATE_H

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: noet sw=4 ts=4 fdm=marker
 * vim<600: noet sw=4 ts=4
 */
