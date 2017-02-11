/*
 * This file is part of the pinepain/php-v8 PHP extension.
 *
 * Copyright (c) 2015-2017 Bogdan Padalko <pinepain@gmail.com>
 *
 * Licensed under the MIT license: http://opensource.org/licenses/MIT
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source or visit
 * http://opensource.org/licenses/MIT
 */

#ifndef PHP_V8_SOURCE_H
#define PHP_V8_SOURCE_H


#include "php_v8_exception.h"
#include "php_v8_context.h"
#include "php_v8_isolate.h"
#include <v8.h>

extern "C" {
#include "php.h"

#ifdef ZTS
#include "TSRM.h"
#endif
}

extern zend_class_entry *php_v8_source_class_entry;

extern void php_v8_update_source_cached_data(zval *src_zv, v8::ScriptCompiler::Source *source);

#define PHP_V8_SOURCE_READ_SOURCE_STRING(from_zval) zend_read_property(php_v8_source_class_entry, (from_zval), ZEND_STRL("source_string"), 0, &rv)
#define PHP_V8_SOURCE_READ_ORIGIN(from_zval) zend_read_property(php_v8_source_class_entry, (from_zval), ZEND_STRL("origin"), 0, &rv)
#define PHP_V8_SOURCE_READ_CACHED_DATA(from_zval) zend_read_property(php_v8_source_class_entry, (from_zval), ZEND_STRL("cached_data"), 0, &rv)

PHP_MINIT_FUNCTION(php_v8_source);

#endif //PHP_V8_SOURCE_H
