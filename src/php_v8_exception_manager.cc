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

#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include "php_v8_exception_manager.h"
#include "php_v8_stack_trace.h"
#include "php_v8_message.h"
#include "php_v8_string.h"
#include "php_v8_value.h"
#include "php_v8_isolate.h"
#include "php_v8.h"

zend_class_entry *php_v8_exception_manager_class_entry;
#define this_ce php_v8_exception_manager_class_entry


static PHP_METHOD(ExceptionManager, createRangeError) {
    zval *php_v8_message_zv;
    zval *php_v8_context_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "oo", &php_v8_context_zv, &php_v8_message_zv) == FAILURE) {
        return;
    }

    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);
    PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_message_zv, php_v8_message);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_message, php_v8_context);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    v8::Local<v8::String> message = php_v8_value_get_local_as<v8::String>(php_v8_message);

    v8::Local<v8::Value> local_value = v8::Exception::RangeError(message);

    php_v8_create_value(return_value, local_value, php_v8_context->php_v8_isolate);
}

static PHP_METHOD(ExceptionManager, createReferenceError) {
    zval *php_v8_message_zv;
    zval *php_v8_context_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "oo", &php_v8_context_zv, &php_v8_message_zv) == FAILURE) {
        return;
    }

    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);
    PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_message_zv, php_v8_message);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_message, php_v8_context);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    v8::Local<v8::String> message = php_v8_value_get_local_as<v8::String>(php_v8_message);

    v8::Local<v8::Value> local_value = v8::Exception::ReferenceError(message);

    php_v8_create_value(return_value, local_value, php_v8_context->php_v8_isolate);
}

static PHP_METHOD(ExceptionManager, createSyntaxError) {
    zval *php_v8_message_zv;
    zval *php_v8_context_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "oo", &php_v8_context_zv, &php_v8_message_zv) == FAILURE) {
        return;
    }

    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);
    PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_message_zv, php_v8_message);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_message, php_v8_context);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    v8::Local<v8::String> message = php_v8_value_get_local_as<v8::String>(php_v8_message);

    v8::Local<v8::Value> local_value = v8::Exception::SyntaxError(message);

    php_v8_create_value(return_value, local_value, php_v8_context->php_v8_isolate);
}

static PHP_METHOD(ExceptionManager, createTypeError) {
    zval *php_v8_message_zv;
    zval *php_v8_context_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "oo", &php_v8_context_zv, &php_v8_message_zv) == FAILURE) {
        return;
    }

    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);
    PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_message_zv, php_v8_message);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_message, php_v8_context);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
    PHP_V8_ENTER_CONTEXT(php_v8_context);
    v8::Local<v8::String> message = php_v8_value_get_local_as<v8::String>(php_v8_message);

    v8::Local<v8::Value> local_value = v8::Exception::TypeError(message);

    php_v8_create_value(return_value, local_value, php_v8_context->php_v8_isolate);
}

static PHP_METHOD(ExceptionManager, createError) {
    zval *php_v8_message_zv;
    zval *php_v8_context_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "oo", &php_v8_context_zv, &php_v8_message_zv) == FAILURE) {
        return;
    }

    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);
    PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_message_zv, php_v8_message);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_message, php_v8_context);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    v8::Local<v8::String> message = php_v8_value_get_local_as<v8::String>(php_v8_message);

    v8::Local<v8::Value> local_value = v8::Exception::Error(message);

    php_v8_create_value(return_value, local_value, php_v8_context->php_v8_isolate);
}

static PHP_METHOD(ExceptionManager, createMessage) {
    zval *php_v8_context_zv;
    zval *php_v8_exception_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "oo", &php_v8_context_zv, &php_v8_exception_zv) == FAILURE) {
        return;
    }

    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);
    PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_exception_zv, php_v8_exception);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_exception, php_v8_context);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    v8::Local<v8::Value> exception = php_v8_value_get_local(php_v8_exception);

    v8::Local<v8::Message> local_message = v8::Exception::CreateMessage(isolate, exception);

    php_v8_message_create_from_message(return_value, php_v8_context->php_v8_isolate, local_message);
}

static PHP_METHOD(ExceptionManager, getStackTrace) {
    zval *php_v8_exception_zv;
    zval *php_v8_context_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "oo", &php_v8_context_zv, &php_v8_exception_zv) == FAILURE) {
        return;
    }

    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);
    PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_exception_zv, php_v8_exception);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_exception, php_v8_context);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    v8::Local<v8::Value> exception = php_v8_value_get_local(php_v8_exception);

    v8::Local<v8::StackTrace> local_stack_trace = v8::Exception::GetStackTrace(exception);

    if (local_stack_trace.IsEmpty()) {
        RETURN_NULL();
    }
    
    php_v8_stack_trace_create_from_stack_trace(return_value, php_v8_context->php_v8_isolate, local_stack_trace);
}


PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_createRangeError, ZEND_RETURN_VALUE, 2, V8\\ObjectValue, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_OBJ_INFO(0, message, V8\\StringValue, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_createReferenceError, ZEND_RETURN_VALUE, 2, V8\\ObjectValue, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_OBJ_INFO(0, message, V8\\StringValue, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_createSyntaxError, ZEND_RETURN_VALUE, 2, V8\\ObjectValue, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_OBJ_INFO(0, message, V8\\StringValue, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_createTypeError, ZEND_RETURN_VALUE, 2, V8\\ObjectValue, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_OBJ_INFO(0, message, V8\\StringValue, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_createError, ZEND_RETURN_VALUE, 2, V8\\ObjectValue, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_OBJ_INFO(0, message, V8\\StringValue, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_createMessage, ZEND_RETURN_VALUE, 2, V8\\Message, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_OBJ_INFO(0, exception, V8\\Value, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_getStackTrace, ZEND_RETURN_VALUE, 2, V8\\StackTrace, 1)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_OBJ_INFO(0, exception, V8\\Value, 0)
ZEND_END_ARG_INFO()

static const zend_function_entry php_v8_exception_manger_methods[] = {
        PHP_V8_ME(ExceptionManager, createRangeError,     ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
        PHP_V8_ME(ExceptionManager, createReferenceError, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
        PHP_V8_ME(ExceptionManager, createSyntaxError,    ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
        PHP_V8_ME(ExceptionManager, createTypeError,      ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
        PHP_V8_ME(ExceptionManager, createError,          ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
        PHP_V8_ME(ExceptionManager, createMessage,  ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
        PHP_V8_ME(ExceptionManager, getStackTrace,        ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)

        PHP_FE_END
};

PHP_MINIT_FUNCTION(php_v8_exception_manger) {
    zend_class_entry ce;
    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS, "ExceptionManager", php_v8_exception_manger_methods);
    this_ce = zend_register_internal_class(&ce);

    return SUCCESS;
}
