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

#include "php_v8_message.h"
#include "php_v8_stack_trace.h"
#include "php_v8_script_origin.h"
#include "php_v8_value.h"
#include "php_v8.h"

zend_class_entry* php_v8_message_class_entry;
#define this_ce php_v8_message_class_entry

void php_v8_message_create_from_message(zval *return_value, php_v8_isolate_t *php_v8_isolate, v8::Local<v8::Message> message) {

    assert(!message.IsEmpty());

    object_init_ex(return_value, this_ce);

    PHP_V8_DECLARE_ISOLATE(php_v8_isolate);
    v8::Local<v8::Context> context = isolate->GetEnteredContext();

    /* v8::Message::Get */
    if (!message->Get().IsEmpty()) {
        PHP_V8_CONVERT_FROM_V8_STRING_TO_STRING(isolate, message_chars, message->Get());
        zend_update_property_string(this_ce, return_value, ZEND_STRL("message"), message_chars);
    }

    /* v8::Message::GetSourceLine */
    if (!message->GetSourceLine(context).IsEmpty()) {
        PHP_V8_CONVERT_FROM_V8_STRING_TO_STRING(isolate, source_line_chars, message->GetSourceLine(context).ToLocalChecked());
        zend_update_property_string(this_ce, return_value, ZEND_STRL("source_line"), source_line_chars);
    }

    /* v8::Message::GetScriptOrigin */
    zval origin_zv;
    php_v8_create_script_origin(&origin_zv, context, message->GetScriptOrigin());
    zend_update_property(this_ce, return_value, ZEND_STRL("script_origin"), &origin_zv);
    zval_ptr_dtor(&origin_zv);

    /* v8::Message::GetScriptResourceName */
    if (!message->GetScriptResourceName().IsEmpty() && !message->GetScriptResourceName()->IsUndefined()) {
        PHP_V8_CONVERT_FROM_V8_STRING_TO_STRING(isolate, script_resource_name_chars, message->GetScriptResourceName());
        zend_update_property_string(this_ce, return_value, ZEND_STRL("resource_name"), script_resource_name_chars);
    }

    /* v8::Message::GetStackTrace */
    if (!message->GetStackTrace().IsEmpty()) {
        zval trace_zv;
        php_v8_stack_trace_create_from_stack_trace(&trace_zv, php_v8_isolate, message->GetStackTrace());
        zend_update_property(this_ce, return_value, ZEND_STRL("stack_trace"), &trace_zv);
        zval_ptr_dtor(&trace_zv);
    }

    /* v8::Message::GetLineNumber */
    /* NOTE: we don't use FromMaybe(v8::Message::kNoLineNumberInfo) due to static const (https://gcc.gnu.org/wiki/VerboseDiagnostics#missing_static_const_definition)*/
    int line_number = v8::Message::kNoLineNumberInfo;
    if (!message->GetLineNumber(context).IsNothing()) {
        line_number = message->GetLineNumber(context).FromJust();
    }

    if (v8::Message::kNoLineNumberInfo != line_number) {
        zend_update_property_long(this_ce, return_value, ZEND_STRL("line_number"), static_cast<zend_long>(line_number));
    }

    /* v8::Message::GetStartPosition */
    zend_update_property_long(this_ce, return_value, ZEND_STRL("start_position"), static_cast<zend_long>(message->GetStartPosition()));

    /* v8::Message::GetEndPosition */
    zend_update_property_long(this_ce, return_value, ZEND_STRL("end_position"), static_cast<zend_long>(message->GetEndPosition()));

    /* v8::Message::GetStartColumn */
    /* NOTE: we don't use FromMaybe(v8::Message::kNoColumnInfo) due to static const (https://gcc.gnu.org/wiki/VerboseDiagnostics#missing_static_const_definition)*/
    int start_column = v8::Message::kNoColumnInfo;
    if (!message->GetStartColumn(context).IsNothing()) {
        start_column = message->GetStartColumn(context).FromJust();
    }

    if (v8::Message::kNoColumnInfo != start_column) {
        zend_update_property_long(this_ce, return_value, ZEND_STRL("start_column"), static_cast<zend_long>(start_column));
    }

    /* v8::Message::GetEndColumn */
    /* NOTE: we don't use FromMaybe(v8::Message::kNoColumnInfo) due to static const (https://gcc.gnu.org/wiki/VerboseDiagnostics#missing_static_const_definition)*/
    int end_column  = v8::Message::kNoColumnInfo;
    if (!message->GetEndColumn(context).IsNothing()) {
        end_column  = message->GetEndColumn(context).FromJust();
    }
    if (v8::Message::kNoColumnInfo != end_column) {
        zend_update_property_long(this_ce, return_value, ZEND_STRL("end_column"), static_cast<zend_long>(end_column));
    }
}


static PHP_METHOD(Message, __construct) {

    zend_string *message = NULL;
    zend_string *source_line = NULL;
    zval *script_origin = NULL;
    zend_string *resource_name = NULL;
    zval *stack_trace = NULL;

    zend_long line_number    = -1;
    zend_long start_position = -1;
    zend_long end_position   = -1;
    zend_long start_column   = -1;
    zend_long end_column     = -1;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "SSoSo|lllll",
                              &message, &source_line, &script_origin, &resource_name, &stack_trace,
                              &line_number, &start_position, &end_position, &start_column, &end_column) == FAILURE) {
        return;
    }

    zend_update_property_str(this_ce, getThis(), ZEND_STRL("message"), message);
    zend_update_property_str(this_ce, getThis(), ZEND_STRL("source_line"), source_line);
    zend_update_property(this_ce, getThis(), ZEND_STRL("script_origin"), script_origin);
    zend_update_property_str(this_ce, getThis(), ZEND_STRL("resource_name"), resource_name);
    zend_update_property(this_ce, getThis(), ZEND_STRL("stack_trace"), stack_trace);

    if (line_number > 0) {
        zend_update_property_long(this_ce, getThis(), ZEND_STRL("line_number"), line_number);
    }
    if (start_position >= 0) {
        zend_update_property_long(this_ce, getThis(), ZEND_STRL("start_position"), start_position);
    }
    if (end_position >= 0) {
        zend_update_property_long(this_ce, getThis(), ZEND_STRL("end_position"), end_position);
    }
    if (start_column > 0) {
        zend_update_property_long(this_ce, getThis(), ZEND_STRL("start_column"), start_column);
    }
    if (end_column > 0) {
        zend_update_property_long(this_ce, getThis(), ZEND_STRL("end_column"), end_column);
    }
}

static PHP_METHOD(Message, get)
{
    zval rv;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    RETVAL_ZVAL(zend_read_property(this_ce, getThis(), ZEND_STRL("message"), 0, &rv), 1, 0);
}

static PHP_METHOD(Message, getSourceLine)
{
    zval rv;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    RETVAL_ZVAL(zend_read_property(this_ce, getThis(), ZEND_STRL("source_line"), 0, &rv), 1, 0);
}

static PHP_METHOD(Message, getScriptOrigin)
{
    zval rv;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    RETVAL_ZVAL(zend_read_property(this_ce, getThis(), ZEND_STRL("script_origin"), 0, &rv), 1, 0);
}

static PHP_METHOD(Message, getScriptResourceName)
{
    zval rv;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    RETVAL_ZVAL(zend_read_property(this_ce, getThis(), ZEND_STRL("resource_name"), 0, &rv), 1, 0);
}

static PHP_METHOD(Message, getStackTrace)
{
    zval rv;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    RETVAL_ZVAL(zend_read_property(this_ce, getThis(), ZEND_STRL("stack_trace"), 0, &rv), 1, 0);
}

static PHP_METHOD(Message, getLineNumber)
{
    zval rv;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    RETVAL_ZVAL(zend_read_property(this_ce, getThis(), ZEND_STRL("line_number"), 0, &rv), 1, 0);
}

static PHP_METHOD(Message, getStartPosition)
{
    zval rv;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    RETVAL_ZVAL(zend_read_property(this_ce, getThis(), ZEND_STRL("start_position"), 0, &rv), 1, 0);
}

static PHP_METHOD(Message, getEndPosition)
{
    zval rv;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    RETVAL_ZVAL(zend_read_property(this_ce, getThis(), ZEND_STRL("end_position"), 0, &rv), 1, 0);
}

static PHP_METHOD(Message, getStartColumn)
{
    zval rv;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    RETVAL_ZVAL(zend_read_property(this_ce, getThis(), ZEND_STRL("start_column"), 0, &rv), 1, 0);
}

static PHP_METHOD(Message, getEndColumn)
{
    zval rv;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    RETVAL_ZVAL(zend_read_property(this_ce, getThis(), ZEND_STRL("end_column"), 0, &rv), 1, 0);
}

PHP_V8_ZEND_BEGIN_ARG_WITH_CONSTRUCTOR_INFO_EX(arginfo___construct, 5)
                ZEND_ARG_TYPE_INFO(0, message, IS_STRING, 0)
                ZEND_ARG_TYPE_INFO(0, source_line, IS_STRING, 0)
                ZEND_ARG_OBJ_INFO(0, script_origin, V8\\ScriptOrigin, 0)
                ZEND_ARG_TYPE_INFO(0, resource_name, IS_STRING, 0)
                ZEND_ARG_OBJ_INFO(0, stack_trace, V8\\StackTrace, 0)
                ZEND_ARG_TYPE_INFO(0, line_number, IS_LONG, 1)
                ZEND_ARG_TYPE_INFO(0, start_position, IS_LONG, 1)
                ZEND_ARG_TYPE_INFO(0, end_position, IS_LONG, 1)
                ZEND_ARG_TYPE_INFO(0, start_column, IS_LONG, 1)
                ZEND_ARG_TYPE_INFO(0, end_column, IS_LONG, 1)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_get, ZEND_RETURN_VALUE, 0, IS_STRING, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_getSourceLine, ZEND_RETURN_VALUE, 0, IS_STRING, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_getScriptOrigin, ZEND_RETURN_VALUE, 0, V8\\ScriptOrigin, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_getScriptResourceName, ZEND_RETURN_VALUE, 0, IS_STRING, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_getStackTrace, ZEND_RETURN_VALUE, 0, V8\\StackTrace, 1)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_getLineNumber, ZEND_RETURN_VALUE, 0, IS_LONG, 1)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_getStartPosition, ZEND_RETURN_VALUE, 0, IS_LONG, 1)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_getEndPosition, ZEND_RETURN_VALUE, 0, IS_LONG, 1)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_getStartColumn, ZEND_RETURN_VALUE, 0, IS_LONG, 1)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_getEndColumn, ZEND_RETURN_VALUE, 0, IS_LONG, 1)
ZEND_END_ARG_INFO()


static const zend_function_entry php_v8_message_methods[] = {
        PHP_V8_ME(Message, __construct,           ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)
        PHP_V8_ME(Message, get,                   ZEND_ACC_PUBLIC)
        PHP_V8_ME(Message, getSourceLine,         ZEND_ACC_PUBLIC)
        PHP_V8_ME(Message, getScriptOrigin,       ZEND_ACC_PUBLIC)
        PHP_V8_ME(Message, getScriptResourceName, ZEND_ACC_PUBLIC)
        PHP_V8_ME(Message, getStackTrace,         ZEND_ACC_PUBLIC)
        PHP_V8_ME(Message, getLineNumber,         ZEND_ACC_PUBLIC)
        PHP_V8_ME(Message, getStartPosition,      ZEND_ACC_PUBLIC)
        PHP_V8_ME(Message, getEndPosition,        ZEND_ACC_PUBLIC)
        PHP_V8_ME(Message, getStartColumn,        ZEND_ACC_PUBLIC)
        PHP_V8_ME(Message, getEndColumn,          ZEND_ACC_PUBLIC)

        PHP_FE_END
};

PHP_MINIT_FUNCTION (php_v8_message) {
    zend_class_entry ce;
    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS, "Message", php_v8_message_methods);
    this_ce = zend_register_internal_class(&ce);

    zend_declare_property_string(this_ce, ZEND_STRL("message"),       "", ZEND_ACC_PRIVATE);
    zend_declare_property_null(this_ce,   ZEND_STRL("script_origin"),     ZEND_ACC_PRIVATE);
    zend_declare_property_string(this_ce, ZEND_STRL("source_line"),   "", ZEND_ACC_PRIVATE);
    zend_declare_property_string(this_ce, ZEND_STRL("resource_name"), "", ZEND_ACC_PRIVATE);
    zend_declare_property_null(this_ce,   ZEND_STRL("stack_trace"),       ZEND_ACC_PRIVATE);

    zend_declare_property_null(this_ce, ZEND_STRL("line_number"),    ZEND_ACC_PRIVATE);
    zend_declare_property_null(this_ce, ZEND_STRL("start_position"), ZEND_ACC_PRIVATE);
    zend_declare_property_null(this_ce, ZEND_STRL("end_position"),   ZEND_ACC_PRIVATE);

    zend_declare_property_null(this_ce, ZEND_STRL("start_column"), ZEND_ACC_PRIVATE);
    zend_declare_property_null(this_ce, ZEND_STRL("end_column"),   ZEND_ACC_PRIVATE);

    return SUCCESS;
}
