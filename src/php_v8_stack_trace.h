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

#ifndef PHP_V8_STACK_TRACE_H
#define PHP_V8_STACK_TRACE_H

#include "php_v8_isolate.h"
#include <v8.h>

extern "C" {
#include "php.h"

#ifdef ZTS
#include "TSRM.h"
#endif
}

extern zend_class_entry* php_v8_stack_trace_class_entry;
extern zend_class_entry* php_v8_stack_trace_options_class_entry;

extern void php_v8_stack_trace_create_from_stack_trace(zval *return_value, php_v8_isolate_t *php_v8_isolate, v8::Local<v8::StackTrace> trace);

#define PHP_V8_STACK_TRACE_MIN_FRAME_LIMIT 0
#define PHP_V8_STACK_TRACE_MAX_FRAME_LIMIT 1000

#define PHP_V8_STACK_TRACE_OPTIONS ( 0                                      \
    | v8::StackTrace::StackTraceOptions::kLineNumber                        \
    | v8::StackTrace::StackTraceOptions::kColumnOffset                      \
    | v8::StackTrace::StackTraceOptions::kScriptName                        \
    | v8::StackTrace::StackTraceOptions::kFunctionName                      \
    | v8::StackTrace::StackTraceOptions::kIsEval                            \
    | v8::StackTrace::StackTraceOptions::kIsConstructor                     \
    | v8::StackTrace::StackTraceOptions::kScriptNameOrSourceURL             \
    | v8::StackTrace::StackTraceOptions::kScriptId                          \
    | v8::StackTrace::StackTraceOptions::kExposeFramesAcrossSecurityOrigins \
    | v8::StackTrace::StackTraceOptions::kOverview                          \
    | v8::StackTrace::StackTraceOptions::kDetailed )

#define PHP_V8_CHECK_STACK_TRACE_RANGE(val, message) \
    if ((val) > PHP_V8_STACK_TRACE_MAX_FRAME_LIMIT || (val) < PHP_V8_STACK_TRACE_MIN_FRAME_LIMIT) { \
        PHP_V8_THROW_VALUE_EXCEPTION(message); \
        return; \
    }


PHP_MINIT_FUNCTION(php_v8_stack_trace);

#endif //PHP_V8_STACK_TRACE_H
/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: noet sw=4 ts=4 fdm=marker
 * vim<600: noet sw=4 ts=4
 */
