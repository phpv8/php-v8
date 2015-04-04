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

#ifndef PHP_V8_ACCESS_TYPE_H
#define PHP_V8_ACCESS_TYPE_H

extern "C" {
#include "php.h"

#ifdef ZTS
#include "TSRM.h"
#endif
}

extern zend_class_entry* php_v8_access_type_class_entry;

#define PHP_V8_ACCESS_TYPE_FLAGS ( 0    \
    | v8::AccessType::ACCESS_GET        \
    | v8::AccessType::ACCESS_SET        \
    | v8::AccessType::ACCESS_HAS        \
    | v8::AccessType::ACCESS_DELETE     \
    | v8::AccessType::ACCESS_KEYS       \
)

PHP_MINIT_FUNCTION(php_v8_access_type);

#endif //PHP_V8_ACCESS_TYPE_H
/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: noet sw=4 ts=4 fdm=marker
 * vim<600: noet sw=4 ts=4
 */

