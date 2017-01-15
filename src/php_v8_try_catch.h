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

#ifndef PHP_V8_TRY_CATCH_H
#define PHP_V8_TRY_CATCH_H

#include "php_v8_context.h"
#include "php_v8_isolate.h"
#include <v8.h>

extern "C" {
#include "php.h"

#ifdef ZTS
#include "TSRM.h"
#endif
}

extern zend_class_entry* php_v8_try_catch_class_entry;

extern void php_v8_try_catch_create_from_try_catch(zval *return_value, php_v8_isolate_t *php_v8_isolate, php_v8_context_t *php_v8_context, v8::TryCatch *try_catch);

#define PHP_V8_TRY_CATCH_READ_ISOLATE(from_zval) zend_read_property(php_v8_try_catch_class_entry, (from_zval), ZEND_STRL("isolate"), 0, &rv)
#define PHP_V8_TRY_CATCH_READ_CONTEXT(from_zval) zend_read_property(php_v8_try_catch_class_entry, (from_zval), ZEND_STRL("context"), 0, &rv)


PHP_MINIT_FUNCTION(php_v8_try_catch);

#endif //PHP_V8_TRY_CATCH_H
