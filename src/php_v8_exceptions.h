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

#ifndef PHP_V8_EXCEPTIONS_H
#define PHP_V8_EXCEPTIONS_H

#include "php_v8_context.h"
#include "php_v8_isolate.h"
#include <v8.h>

extern "C" {
#include "php.h"

#ifdef ZTS
#include "TSRM.h"
#endif

#include "zend_exceptions.h"
}

extern zend_class_entry* php_v8_generic_exception_class_entry;
extern zend_class_entry* php_v8_try_catch_exception_class_entry;
extern zend_class_entry* php_v8_termination_exception_class_entry;
extern zend_class_entry* php_v8_abstract_resource_limit_exception_class_entry;
extern zend_class_entry* php_v8_time_limit_exception_class_entry;
extern zend_class_entry* php_v8_memory_limit_exception_class_entry;

extern zend_class_entry* php_v8_value_exception_class_entry;
extern zend_class_entry* php_v8_script_exception_class_entry;

extern void php_v8_try_catch_throw_exception(v8::TryCatch *try_catch, const char* message, zend_class_entry *ce);
extern void php_v8_throw_try_catch_exception(php_v8_isolate_t *php_v8_isolate, php_v8_context_t *php_v8_context, v8::TryCatch *try_catch);
extern void php_v8_throw_try_catch_exception(php_v8_context_t *php_v8_context, v8::TryCatch *try_catch);


#define PHP_V8_THROW_EXCEPTION_CE(message, ce) zend_throw_exception((ce), (message), 0);
#define PHP_V8_THROW_EXCEPTION(message) PHP_V8_THROW_EXCEPTION_CE((message), php_v8_generic_exception_class_entry);
#define PHP_V8_THROW_VALUE_EXCEPTION(message) PHP_V8_THROW_EXCEPTION_CE((message), php_v8_value_exception_class_entry);


#define PHP_V8_TRY_CATCH(isolate) v8::TryCatch try_catch(isolate);
#define PHP_V8_CATCH_START(value) if ((value).IsEmpty()) { assert(try_catch.HasCaught());
#define PHP_V8_CATCH_END() } assert(!try_catch.HasCaught());

#define PHP_V8_MAYBE_CATCH(php_v8_context, try_catch) \
    php_v8_isolate_maybe_update_limits_hit((php_v8_context)->php_v8_isolate);\
    php_v8_isolate_limits_maybe_stop_timer((php_v8_context)->php_v8_isolate);\
    if ((try_catch).HasCaught()) { \
        php_v8_throw_try_catch_exception((php_v8_context), &(try_catch)); \
        return; \
    }


#define PHP_V8_TRY_CATCH_EXCEPTION_STORE_ISOLATE(to_zval, from_isolate_zv) zend_update_property(php_v8_try_catch_exception_class_entry, (to_zval), ZEND_STRL("isolate"), (from_isolate_zv));
#define PHP_V8_TRY_CATCH_EXCEPTION_READ_ISOLATE(from_zval) zend_read_property(php_v8_try_catch_exception_class_entry, (from_zval), ZEND_STRL("isolate"), 0, &rv)

#define PHP_V8_TRY_CATCH_EXCEPTION_STORE_CONTEXT(to_zval, from_context_zv) zend_update_property(php_v8_try_catch_exception_class_entry, (to_zval), ZEND_STRL("context"), (from_context_zv));
#define PHP_V8_TRY_CATCH_EXCEPTION_READ_CONTEXT(from_zval) zend_read_property(php_v8_try_catch_exception_class_entry, (from_zval), ZEND_STRL("context"), 0, &rv)

#define PHP_V8_TRY_CATCH_EXCEPTION_STORE_TRY_CATCH(to_zval, from_isolate_zv) zend_update_property(php_v8_try_catch_exception_class_entry, (to_zval), ZEND_STRL("try_catch"), (from_isolate_zv));
#define PHP_V8_TRY_CATCH_EXCEPTION_READ_TRY_CATCH(from_zval) zend_read_property(php_v8_try_catch_exception_class_entry, (from_zval), ZEND_STRL("try_catch"), 0, &rv)


#define PHP_V8_THROW_EXCEPTION_WHEN_NOTHING_CE(value, message, ce) \
    if ((value).IsNothing()) { \
        PHP_V8_THROW_EXCEPTION_CE(message, ce); \
        return; \
    }

#define PHP_V8_THROW_EXCEPTION_WHEN_EMPTY_CE(value, message, ce) \
    if ((value).IsEmpty()) { \
        PHP_V8_THROW_EXCEPTION_CE(message, ce); \
        return; \
    }

#define PHP_V8_THROW_VALUE_EXCEPTION_WHEN_NOTHING(value, message) PHP_V8_THROW_EXCEPTION_WHEN_NOTHING_CE((value), (message), php_v8_value_exception_class_entry)
#define PHP_V8_THROW_VALUE_EXCEPTION_WHEN_EMPTY(value, message) PHP_V8_THROW_EXCEPTION_WHEN_EMPTY_CE((value), (message), php_v8_value_exception_class_entry)


#define PHP_V8_THROW_EXCEPTION_WHEN_NOTHING(value, message) \
    if ((value).IsNothing()) { \
        PHP_V8_THROW_EXCEPTION(message); \
        return; \
    }

#define PHP_V8_THROW_EXCEPTION_WHEN_EMPTY(value, message) \
    if ((value).IsEmpty()) { \
        PHP_V8_THROW_EXCEPTION(message); \
        return; \
    }

#define PHP_V8_THROW_EXCEPTION_WHEN_LIMITS_HIT(php_v8_context) \
    if ((php_v8_context)->php_v8_isolate->limits.time_limit_hit || (php_v8_context)->php_v8_isolate->limits.memory_limit_hit) { \
        php_v8_throw_try_catch_exception((php_v8_context), NULL); \
        return; \
    }

#define PHP_V8_EMPTY_HANDLER_MSG_PART " is empty. Forgot to call parent::__construct()?"
//#define PHP_V8_CHECK_EMPTY_HANDLER(val, message) if (NULL == (val)->php_v8_isolate || (val)->persistent->IsEmpty()) { PHP_V8_THROW_EXCEPTION(message); return; }
// we check handler to be !IsEmpty() in constructors and before value creations, so unless we didn't check that by mistacke, IsEmpty() check may be skipped
#define PHP_V8_CHECK_EMPTY_HANDLER(val, message) if (NULL == (val)->php_v8_isolate) { PHP_V8_THROW_EXCEPTION(message); return; }


PHP_MINIT_FUNCTION(php_v8_exceptions);


#endif //PHP_V8_EXCEPTIONS_H
