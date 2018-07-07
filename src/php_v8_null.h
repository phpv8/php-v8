/*
 * This file is part of the phpv8/php-v8 PHP extension.
 *
 * Copyright (c) 2015-2018 Bogdan Padalko <thepinepain@gmail.com>
 *
 * Licensed under the MIT license: http://opensource.org/licenses/MIT
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source or visit
 * http://opensource.org/licenses/MIT
 */

#ifndef PHP_V8_NULL_H
#define PHP_V8_NULL_H

extern "C" {
#include "php.h"

#ifdef ZTS
#include "TSRM.h"
#endif
}

extern zend_class_entry* php_v8_null_class_entry;

PHP_MINIT_FUNCTION(php_v8_null);

#endif //PHP_V8_NULL_H
