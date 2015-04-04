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

#ifndef PHP_V8_OBJECT_H
#define PHP_V8_OBJECT_H

#include "php_v8_value.h"
#include <v8.h>

extern "C" {
#include "php.h"

#ifdef ZTS
#include "TSRM.h"
#endif
}

extern zend_class_entry* php_v8_object_class_entry;

extern v8::Local<v8::Object> php_v8_value_get_object_local(v8::Isolate *isolate, php_v8_value_t *php_v8_value);

extern bool php_v8_object_delete_self_ptr(v8::Isolate *isolate, v8::Local<v8::Object> local_object);
extern bool php_v8_object_store_self_ptr(v8::Isolate *isolate, v8::Local<v8::Object> local_object,
                                         php_v8_value_t *php_v8_value);
extern php_v8_value_t * php_v8_object_get_self_ptr(v8::Isolate *isolate, v8::Local<v8::Object> local_object);


#define PHP_V8_OBJECT_STORE_CONTEXT(to_zval, from_context_zv) zend_update_property(php_v8_object_class_entry, (to_zval), ZEND_STRL("context"), (from_context_zv));
#define PHP_V8_OBJECT_READ_CONTEXT(from_zval) zend_read_property(php_v8_object_class_entry, (from_zval), ZEND_STRL("context"), 0, &rv)


PHP_MINIT_FUNCTION(php_v8_object);

#endif //PHP_V8_OBJECT_H

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: noet sw=4 ts=4 fdm=marker
 * vim<600: noet sw=4 ts=4
 */
