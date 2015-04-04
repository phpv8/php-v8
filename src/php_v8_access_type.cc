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

#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include "php_v8_access_type.h"
#include "php_v8.h"
#include <v8.h>

zend_class_entry* php_v8_access_type_class_entry;
#define this_ce php_v8_access_type_class_entry


static const zend_function_entry php_v8_access_type_methods[] = {
        PHP_FE_END
};

PHP_MINIT_FUNCTION(php_v8_access_type) {
    zend_class_entry ce;
    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS, "AccessType", php_v8_access_type_methods);
    this_ce = zend_register_internal_class(&ce);

    zend_declare_class_constant_long(this_ce, ZEND_STRL("ACCESS_GET"), v8::AccessType::ACCESS_GET);
    zend_declare_class_constant_long(this_ce, ZEND_STRL("ACCESS_SET"), v8::AccessType::ACCESS_SET);
    zend_declare_class_constant_long(this_ce, ZEND_STRL("ACCESS_HAS"), v8::AccessType::ACCESS_HAS);
    zend_declare_class_constant_long(this_ce, ZEND_STRL("ACCESS_DELETE"), v8::AccessType::ACCESS_DELETE);
    zend_declare_class_constant_long(this_ce, ZEND_STRL("ACCESS_KEYS"), v8::AccessType::ACCESS_KEYS);

    return SUCCESS;
}


/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: noet sw=4 ts=4 fdm=marker
 * vim<600: noet sw=4 ts=4
 */

