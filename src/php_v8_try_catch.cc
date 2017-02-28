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

#include "php_v8_try_catch.h"
#include "php_v8_message.h"
#include "php_v8_value.h"
#include "php_v8.h"

zend_class_entry* php_v8_try_catch_class_entry;
#define this_ce php_v8_try_catch_class_entry


void php_v8_try_catch_create_from_try_catch(zval *return_value, php_v8_isolate_t *php_v8_isolate, php_v8_context_t *php_v8_context, v8::TryCatch *try_catch) {
    zval isolate_zv;
    zval context_zv;

    object_init_ex(return_value, this_ce);

    PHP_V8_DECLARE_ISOLATE(php_v8_isolate);
    v8::Local<v8::Context> context = isolate->GetEnteredContext();

    ZVAL_OBJ(&isolate_zv, &php_v8_isolate->std);
    ZVAL_OBJ(&context_zv, &php_v8_context->std);

    zend_update_property(this_ce, return_value, ZEND_STRL("isolate"), &isolate_zv);
    zend_update_property(this_ce, return_value, ZEND_STRL("context"), &context_zv);

    zend_update_property_bool(this_ce, return_value, ZEND_STRL("can_continue"), static_cast<zend_long>(try_catch && try_catch->CanContinue()));
    zend_update_property_bool(this_ce, return_value, ZEND_STRL("has_terminated"), static_cast<zend_long>(try_catch && try_catch->HasTerminated()));

    if (try_catch && !try_catch->Exception().IsEmpty()) {
        zval exception_zv;
        php_v8_get_or_create_value(&exception_zv, try_catch->Exception(), php_v8_isolate);
        zend_update_property(this_ce, return_value, ZEND_STRL("exception"), &exception_zv);
        zval_ptr_dtor(&exception_zv);
    }

    if (try_catch && !try_catch->StackTrace(context).IsEmpty()) {
        zval stack_trace_zv;
        php_v8_get_or_create_value(&stack_trace_zv, try_catch->StackTrace(context).ToLocalChecked(), php_v8_isolate);
        zend_update_property(this_ce, return_value, ZEND_STRL("stack_trace"), &stack_trace_zv);
        zval_ptr_dtor(&stack_trace_zv);
    }

    if (try_catch && !try_catch->Message().IsEmpty()) {
        zval message_zv;
        php_v8_message_create_from_message(&message_zv, php_v8_isolate, try_catch->Message());
        zend_update_property(this_ce, return_value, ZEND_STRL("message"), &message_zv);

        zval_ptr_dtor(&message_zv);
    }
}


static PHP_METHOD(V8TryCatch, __construct) {
    zval *php_v8_isolate_zv;
    zval *php_v8_context_zv;
    zval *php_v8_exception_zv = NULL;
    zval *php_v8_stack_trace_zv = NULL;
    zval *php_v8_message_zv = NULL;

    zend_bool can_continue = '\0';
    zend_bool has_terminated = '\0';

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "oo|o!o!o!bb",
                              &php_v8_isolate_zv,
                              &php_v8_context_zv,
                              &php_v8_exception_zv,
                              &php_v8_stack_trace_zv,
                              &php_v8_message_zv,
                              &can_continue,
                              &has_terminated) == FAILURE) {
        return;
    }

    PHP_V8_ISOLATE_FETCH_WITH_CHECK(php_v8_isolate_zv, php_v8_isolate);
    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);

    PHP_V8_DATA_ISOLATES_CHECK_USING(php_v8_context, php_v8_isolate);

    zend_update_property(this_ce, getThis(), ZEND_STRL("isolate"), php_v8_isolate_zv);
    zend_update_property(this_ce, getThis(), ZEND_STRL("context"), php_v8_context_zv);

    if (php_v8_exception_zv != NULL) {
        PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_exception_zv, php_v8_exception_value);
        PHP_V8_DATA_ISOLATES_CHECK_USING(php_v8_exception_value, php_v8_isolate);

        zend_update_property(this_ce, getThis(), ZEND_STRL("exception"), php_v8_exception_zv);
    }

    if (php_v8_stack_trace_zv != NULL) {
        PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_stack_trace_zv, php_v8_stack_trace_value);
        PHP_V8_DATA_ISOLATES_CHECK_USING(php_v8_stack_trace_value, php_v8_isolate);

        zend_update_property(this_ce, getThis(), ZEND_STRL("stack_trace"), php_v8_stack_trace_zv);
    }

    if (php_v8_message_zv != NULL) {
        zend_update_property(this_ce, getThis(), ZEND_STRL("message"), php_v8_message_zv);
    }

    zend_update_property_bool(this_ce, getThis(), ZEND_STRL("can_continue"), can_continue);
    zend_update_property_bool(this_ce, getThis(), ZEND_STRL("has_terminated"), has_terminated);
}

static PHP_METHOD(V8TryCatch, GetIsolate)
{
    zval rv;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    RETVAL_ZVAL(zend_read_property(this_ce, getThis(), ZEND_STRL("isolate"), 0, &rv), 1, 0);
}

static PHP_METHOD(V8TryCatch, GetContext)
{
    zval rv;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    RETVAL_ZVAL(zend_read_property(this_ce, getThis(), ZEND_STRL("context"), 0, &rv), 1, 0);
}

static PHP_METHOD(V8TryCatch, Exception)
{
    zval rv;
    zval *prop;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    prop = zend_read_property(this_ce, getThis(), ZEND_STRL("exception"), 0, &rv);

    RETVAL_ZVAL(prop, 1, 0);
}

static PHP_METHOD(V8TryCatch, StackTrace)
{
    zval rv;
    zval *prop;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    prop = zend_read_property(this_ce, getThis(), ZEND_STRL("stack_trace"), 0, &rv);

    RETVAL_ZVAL(prop, 1, 0);
}

static PHP_METHOD(V8TryCatch, Message)
{
    zval rv;
    zval *prop;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    prop = zend_read_property(this_ce, getThis(), ZEND_STRL("message"), 0, &rv);

    RETVAL_ZVAL(prop, 1, 0);
}

static PHP_METHOD(V8TryCatch, CanContinue)
{
    zval rv;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    RETVAL_ZVAL(zend_read_property(this_ce, getThis(), ZEND_STRL("can_continue"), 0, &rv), 1, 0);
}

static PHP_METHOD(V8TryCatch, HasTerminated)
{
    zval rv;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    RETVAL_ZVAL(zend_read_property(this_ce, getThis(), ZEND_STRL("has_terminated"), 0, &rv), 1, 0);
}


ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_try_catch___construct, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 2)
                ZEND_ARG_OBJ_INFO(0, isolate, V8\\Isolate, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_OBJ_INFO(0, exception, V8\\Value, 1)
                ZEND_ARG_OBJ_INFO(0, stack_trace, V8\\Value, 1)
                ZEND_ARG_OBJ_INFO(0, message, V8\\Message, 1)
                ZEND_ARG_TYPE_INFO(0, can_continue, _IS_BOOL, 0)
                ZEND_ARG_TYPE_INFO(0, has_terminated, _IS_BOOL, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_v8_try_catch_GetIsolate, ZEND_RETURN_VALUE, 0, V8\\Isolate, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_v8_try_catch_GetContext, ZEND_RETURN_VALUE, 0, V8\\Context, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_v8_try_catch_Exception, ZEND_RETURN_VALUE, 0, V8\\Value, 1)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_v8_try_catch_StackTrace, ZEND_RETURN_VALUE, 0, V8\\Value, 1)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_v8_try_catch_Message, ZEND_RETURN_VALUE, 0, V8\\Message, 1)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_try_catch_CanContinue, ZEND_RETURN_VALUE, 0, _IS_BOOL, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_try_catch_HasTerminated, ZEND_RETURN_VALUE, 0, _IS_BOOL, 0)
ZEND_END_ARG_INFO()


static const zend_function_entry php_v8_try_catch_methods[] = {
        PHP_ME(V8TryCatch, __construct,     arginfo_v8_try_catch___construct,     ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)

        PHP_ME(V8TryCatch, GetIsolate,      arginfo_v8_try_catch_GetIsolate,    ZEND_ACC_PUBLIC)
        PHP_ME(V8TryCatch, GetContext,      arginfo_v8_try_catch_GetContext,    ZEND_ACC_PUBLIC)

        PHP_ME(V8TryCatch, Exception,       arginfo_v8_try_catch_Exception,     ZEND_ACC_PUBLIC)
        PHP_ME(V8TryCatch, StackTrace,      arginfo_v8_try_catch_StackTrace,    ZEND_ACC_PUBLIC)
        PHP_ME(V8TryCatch, Message,         arginfo_v8_try_catch_Message,       ZEND_ACC_PUBLIC)

        PHP_ME(V8TryCatch, CanContinue,     arginfo_v8_try_catch_CanContinue,   ZEND_ACC_PUBLIC)
        PHP_ME(V8TryCatch, HasTerminated,   arginfo_v8_try_catch_HasTerminated, ZEND_ACC_PUBLIC)

        PHP_FE_END
};


PHP_MINIT_FUNCTION (php_v8_try_catch) {
    zend_class_entry ce;
    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS, "TryCatch", php_v8_try_catch_methods);
    this_ce = zend_register_internal_class(&ce);

    zend_declare_property_null(this_ce, ZEND_STRL("isolate"), ZEND_ACC_PRIVATE);
    zend_declare_property_null(this_ce, ZEND_STRL("context"), ZEND_ACC_PRIVATE);
    zend_declare_property_null(this_ce, ZEND_STRL("exception"), ZEND_ACC_PRIVATE);
    zend_declare_property_null(this_ce, ZEND_STRL("stack_trace"), ZEND_ACC_PRIVATE);
    zend_declare_property_null(this_ce, ZEND_STRL("message"), ZEND_ACC_PRIVATE);

    zend_declare_property_null(this_ce, ZEND_STRL("can_continue"), ZEND_ACC_PRIVATE);
    zend_declare_property_null(this_ce, ZEND_STRL("has_terminated"), ZEND_ACC_PRIVATE);

    return SUCCESS;
}
