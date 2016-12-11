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

#ifndef PHP_V8_NAMED_PROPERTY_HANDLER_CONFIGURATION_H
#define PHP_V8_NAMED_PROPERTY_HANDLER_CONFIGURATION_H

typedef struct _php_v8_named_property_handler_configuration_t php_v8_named_property_handler_configuration_t;

#include "php_v8_callbacks.h"
#include <v8.h>

extern "C" {
#include "php.h"

#ifdef ZTS
#include "TSRM.h"
#endif
}

extern zend_class_entry* php_v8_named_property_handler_configuration_class_entry;

extern php_v8_named_property_handler_configuration_t * php_v8_named_property_handler_configuration_fetch_object(zend_object *obj);

#define PHP_V8_NAMED_PROPERTY_HANDLER_FETCH(zv) php_v8_named_property_handler_configuration_fetch_object(Z_OBJ_P(zv))
#define PHP_V8_NAMED_PROPERTY_HANDLER_FETCH_INTO(pzval, into) php_v8_named_property_handler_configuration_t *(into) = PHP_V8_NAMED_PROPERTY_HANDLER_FETCH((pzval));


#define PHP_V8_EMPTY_NAMED_PROPERTY_MSG "NamedProperty" PHP_V8_EMPTY_HANDLER_MSG_PART
#define PHP_V8_CHECK_EMPTY_NAMED_PROPERTY_HANDLER_MSG(val, message) if (NULL == (val)->bucket) { PHP_V8_THROW_EXCEPTION(message); return; }
#define PHP_V8_CHECK_EMPTY_NAMED_PROPERTY_HANDLER(val) PHP_V8_CHECK_EMPTY_NAMED_PROPERTY_HANDLER_MSG((val), PHP_V8_EMPTY_NAMED_PROPERTY_MSG)

#define PHP_V8_NAMED_PROPERTY_HANDLER_FETCH_WITH_CHECK(pzval, into) \
    PHP_V8_NAMED_PROPERTY_HANDLER_FETCH_INTO(pzval, into); \
    PHP_V8_CHECK_EMPTY_NAMED_PROPERTY_HANDLER(into);


#define PHP_V8_NAMED_PROPERTY_HANDLER_STORE_ISOLATE(to_zval, from_isolate_zv) zend_update_property(php_v8_named_property_handler_configuration_class_entry, (to_zval), ZEND_STRL("isolate"), (from_isolate_zv));
#define PHP_V8_NAMED_PROPERTY_HANDLER_READ_ISOLATE(from_zval) zend_read_property(php_v8_named_property_handler_configuration_class_entry, (from_zval), ZEND_STRL("isolate"), 0, &rv)


typedef struct _php_v8_named_property_handler_configuration_t {
  v8::GenericNamedPropertyGetterCallback getter;
  v8::GenericNamedPropertySetterCallback setter;
  v8::GenericNamedPropertyQueryCallback query;
  v8::GenericNamedPropertyDeleterCallback deleter;
  v8::GenericNamedPropertyEnumeratorCallback enumerator;

  long flags;

  phpv8::CallbacksBucket *bucket;

  zval *gc_data;
  int   gc_data_count;

  zend_object std;
} php_v8_named_property_handler_configuration_t;


PHP_MINIT_FUNCTION(php_v8_named_property_handler_configuration);

#endif //PHP_V8_NAMED_PROPERTY_HANDLER_CONFIGURATION_H
