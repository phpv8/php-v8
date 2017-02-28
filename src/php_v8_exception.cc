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

#include "php_v8_exception.h"
#include "php_v8_stack_trace.h"
#include "php_v8_message.h"
#include "php_v8_string.h"
#include "php_v8_value.h"
#include "php_v8_isolate.h"
#include "php_v8.h"

zend_class_entry *php_v8_exception_class_entry;
#define this_ce php_v8_exception_class_entry


static PHP_METHOD(V8Exception, RangeError) {
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

static PHP_METHOD(V8Exception, ReferenceError) {
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

static PHP_METHOD(V8Exception, SyntaxError) {
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

static PHP_METHOD(V8Exception, TypeError) {
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

static PHP_METHOD(V8Exception, Error) {
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

static PHP_METHOD(V8Exception, CreateMessage) {
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

static PHP_METHOD(V8Exception, GetStackTrace) {
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


PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_v8_exception_RangeError, ZEND_RETURN_VALUE, 2, V8\\Value, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_OBJ_INFO(0, message, V8\\StringValue, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_v8_exception_ReferenceError, ZEND_RETURN_VALUE, 2, V8\\Value, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_OBJ_INFO(0, message, V8\\StringValue, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_v8_exception_SyntaxError, ZEND_RETURN_VALUE, 2, V8\\Value, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_OBJ_INFO(0, message, V8\\StringValue, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_v8_exception_TypeError, ZEND_RETURN_VALUE, 2, V8\\Value, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_OBJ_INFO(0, message, V8\\StringValue, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_v8_exception_Error, ZEND_RETURN_VALUE, 2, V8\\Value, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_OBJ_INFO(0, message, V8\\StringValue, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_v8_exception_CreateMessage, ZEND_RETURN_VALUE, 2, V8\\Message, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_OBJ_INFO(0, exception, V8\\Value, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_v8_exception_GetStackTrace, ZEND_RETURN_VALUE, 2, V8\\StackTrace, 1)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_OBJ_INFO(0, exception, V8\\Value, 0)
ZEND_END_ARG_INFO()

static const zend_function_entry php_v8_exception_methods[] = {
        PHP_ME(V8Exception, RangeError,     arginfo_v8_exception_RangeError,        ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
        PHP_ME(V8Exception, ReferenceError, arginfo_v8_exception_ReferenceError,    ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
        PHP_ME(V8Exception, SyntaxError,    arginfo_v8_exception_SyntaxError,       ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
        PHP_ME(V8Exception, TypeError,      arginfo_v8_exception_TypeError,         ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
        PHP_ME(V8Exception, Error,          arginfo_v8_exception_Error,             ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)

        PHP_ME(V8Exception, CreateMessage,  arginfo_v8_exception_CreateMessage,     ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
        PHP_ME(V8Exception, GetStackTrace,  arginfo_v8_exception_GetStackTrace,     ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)

        PHP_FE_END
};

PHP_MINIT_FUNCTION(php_v8_exception) {
    zend_class_entry ce;
    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS, "Exception", php_v8_exception_methods);
    this_ce = zend_register_internal_class_ex(&ce, php_v8_value_class_entry);

    return SUCCESS;
}
