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

#ifndef PHP_V8_ACCESS_CONTROL_H
#define PHP_V8_ACCESS_CONTROL_H

#include <v8.h>

extern "C" {
#include "php.h"

#ifdef ZTS
#include "TSRM.h"
#endif
}

extern zend_class_entry* php_v8_access_control_class_entry;

PHP_MINIT_FUNCTION (php_v8_access_control);

#define PHP_V8_ACCESS_CONTROL_FLAGS ( 0 \
    | v8::AccessControl::DEFAULT        \
    | v8::AccessControl::ALL_CAN_READ   \
    | v8::AccessControl::ALL_CAN_WRITE  \
)


#endif //PHP_V8_ACCESS_CONTROL_H
