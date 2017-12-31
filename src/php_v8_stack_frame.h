/*
 * This file is part of the pinepain/php-v8 PHP extension.
 *
 * Copyright (c) 2015-2018 Bogdan Padalko <pinepain@gmail.com>
 *
 * Licensed under the MIT license: http://opensource.org/licenses/MIT
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source or visit
 * http://opensource.org/licenses/MIT
 */

#ifndef PHP_V8_STACK_FRAME_H
#define PHP_V8_STACK_FRAME_H

#include <v8.h>

extern "C" {
#include "php.h"

#ifdef ZTS
#include "TSRM.h"
#endif
}

extern zend_class_entry* php_v8_stack_frame_class_entry;

extern void php_v8_stack_frame_create_from_stack_frame(v8::Isolate *isolate, zval *return_value, v8::Local<v8::StackFrame> frame);


PHP_MINIT_FUNCTION(php_v8_stack_frame);

#endif //PHP_V8_STACK_FRAME_H
