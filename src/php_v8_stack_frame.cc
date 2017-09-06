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

#include "php_v8_stack_frame.h"
#include "php_v8_value.h"
#include "php_v8.h"

zend_class_entry *php_v8_stack_frame_class_entry;
#define this_ce php_v8_stack_frame_class_entry


void php_v8_stack_frame_create_from_stack_frame(v8::Isolate *isolate, zval *return_value, v8::Local<v8::StackFrame> frame) {

    assert(!frame.IsEmpty());

    object_init_ex(return_value, this_ce);

    /* v8::StackFrame::GetLineNumber */
    zend_update_property_long(this_ce, return_value, ZEND_STRL("line_number"), static_cast<zend_long>(frame->GetLineNumber()));

    /* v8::StackFrame::GetColumn */
    zend_update_property_long(this_ce, return_value, ZEND_STRL("column"), static_cast<zend_long>(frame->GetColumn()));

    /* v8::StackFrame::GetScriptId */
    zend_update_property_long(this_ce, return_value, ZEND_STRL("script_id"), static_cast<zend_long>(frame->GetScriptId()));

    /* v8::StackFrame::GetScriptName */
    if (!frame->GetScriptName().IsEmpty()) {
        PHP_V8_CONVERT_FROM_V8_STRING_TO_STRING(isolate, script_name_chars, frame->GetScriptName());
        zend_update_property_string(this_ce, return_value, ZEND_STRL("script_name"), script_name_chars);
    }

    /* v8::StackFrame::GetScriptNameOrSourceURL */
    if (!frame->GetScriptNameOrSourceURL().IsEmpty()) {
        PHP_V8_CONVERT_FROM_V8_STRING_TO_STRING(isolate, script_name_or_source_url_chars, frame->GetScriptNameOrSourceURL());
        zend_update_property_string(this_ce, return_value, ZEND_STRL("script_name_or_source_url"), script_name_or_source_url_chars);
    }

    /* v8::StackFrame::GetFunctionName */
    if (!frame->GetFunctionName().IsEmpty()) {
        PHP_V8_CONVERT_FROM_V8_STRING_TO_STRING(isolate, function_name_chars, frame->GetFunctionName());
        zend_update_property_string(this_ce, return_value, ZEND_STRL("function_name"), function_name_chars);
    }

    /* v8::StackFrame::IsEval */
    zend_update_property_bool(this_ce, return_value, ZEND_STRL("is_eval"), static_cast<zend_bool >(frame->IsEval()));

    /* v8::StackFrame::IsConstructor */
    zend_update_property_bool(this_ce, return_value, ZEND_STRL("is_constructor"), static_cast<zend_bool >(frame->IsConstructor()));
}

static PHP_METHOD(StackFrame, __construct) {
    zend_long line_number = -1;
    zend_long column = -1;
    zend_long script_id = -1;

    zend_string *script_name = NULL;
    zend_string *script_name_or_source_url = NULL;
    zend_string *function_name = NULL;

    zend_bool is_eval = '\0';
    zend_bool is_constructor = '\0';

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "|lllSSSbb",
                              &line_number, &column, &script_id,
                              &script_name, &script_name_or_source_url, &function_name,
                              &is_eval, &is_constructor) == FAILURE) {
        return;
    }

    if (line_number > 0) {
        zend_update_property_long(this_ce, getThis(), ZEND_STRL("line_number"), line_number);
    }
    if (column > 0) {
        zend_update_property_long(this_ce, getThis(), ZEND_STRL("column"), column);
    }
    if (script_id > 0) {
        zend_update_property_long(this_ce, getThis(), ZEND_STRL("script_id"), script_id);
    }

    if (script_name != NULL) {
        zend_update_property_str(this_ce, getThis(), ZEND_STRL("script_name"), script_name);
    }

    if (script_name_or_source_url != NULL) {
        zend_update_property_str(this_ce, getThis(), ZEND_STRL("script_name_or_source_url"), script_name_or_source_url);
    }

    if (function_name != NULL) {
        zend_update_property_str(this_ce, getThis(), ZEND_STRL("function_name"), function_name);
    }

    zend_update_property_bool(this_ce, getThis(), ZEND_STRL("is_eval"), is_eval);
    zend_update_property_bool(this_ce, getThis(), ZEND_STRL("is_constructor"), is_constructor);
}

static PHP_METHOD(StackFrame, getLineNumber) {
    zval rv;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    RETVAL_ZVAL(zend_read_property(this_ce, getThis(), ZEND_STRL("line_number"), 0, &rv), 1, 0);
}

static PHP_METHOD(StackFrame, getColumn) {
    zval rv;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    RETVAL_ZVAL(zend_read_property(this_ce, getThis(), ZEND_STRL("column"), 0, &rv), 1, 0);
}

static PHP_METHOD(StackFrame, getScriptId) {
    zval rv;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    RETVAL_ZVAL(zend_read_property(this_ce, getThis(), ZEND_STRL("script_id"), 0, &rv), 1, 0);
}

static PHP_METHOD(StackFrame, getScriptName) {
    zval rv;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    RETVAL_ZVAL(zend_read_property(this_ce, getThis(), ZEND_STRL("script_name"), 0, &rv), 1, 0);
}

static PHP_METHOD(StackFrame, getScriptNameOrSourceURL) {
    zval rv;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    RETVAL_ZVAL(zend_read_property(this_ce, getThis(), ZEND_STRL("script_name_or_source_url"), 0, &rv), 1, 0);
}

static PHP_METHOD(StackFrame, getFunctionName) {
    zval rv;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    RETVAL_ZVAL(zend_read_property(this_ce, getThis(), ZEND_STRL("function_name"), 0, &rv), 1, 0);
}

static PHP_METHOD(StackFrame, isEval) {
    zval rv;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    RETVAL_ZVAL(zend_read_property(this_ce, getThis(), ZEND_STRL("is_eval"), 0, &rv), 1, 0);
}

static PHP_METHOD(StackFrame, isConstructor) {
    zval rv;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    RETVAL_ZVAL(zend_read_property(this_ce, getThis(), ZEND_STRL("is_constructor"), 0, &rv), 1, 0);
}

PHP_V8_ZEND_BEGIN_ARG_WITH_CONSTRUCTOR_INFO_EX(arginfo___construct, 0)
                ZEND_ARG_TYPE_INFO(0, line_number, IS_LONG, 1)
                ZEND_ARG_TYPE_INFO(0, column, IS_LONG, 1)
                ZEND_ARG_TYPE_INFO(0, script_id, IS_LONG, 1)
                ZEND_ARG_TYPE_INFO(0, script_name, IS_STRING, 0)
                ZEND_ARG_TYPE_INFO(0, script_name_or_source_url, IS_STRING, 0)
                ZEND_ARG_TYPE_INFO(0, function_name, IS_STRING, 0)
                ZEND_ARG_TYPE_INFO(0, is_eval, _IS_BOOL, 0)
                ZEND_ARG_TYPE_INFO(0, is_constructor, _IS_BOOL, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_getLineNumber, ZEND_RETURN_VALUE, 0, IS_LONG, 1)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_getColumn, ZEND_RETURN_VALUE, 0, IS_LONG, 1)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_getScriptId, ZEND_RETURN_VALUE, 0, IS_LONG, 1)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_getScriptName, ZEND_RETURN_VALUE, 0, IS_STRING, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_getScriptNameOrSourceURL, ZEND_RETURN_VALUE, 0, IS_STRING, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_getFunctionName, ZEND_RETURN_VALUE, 0, IS_STRING, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_isEval, ZEND_RETURN_VALUE, 0, _IS_BOOL, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_isConstructor, ZEND_RETURN_VALUE, 0, _IS_BOOL, 0)
ZEND_END_ARG_INFO()


static const zend_function_entry php_v8_stack_frame_methods[] = {
        PHP_V8_ME(StackFrame, __construct,              ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)
        PHP_V8_ME(StackFrame, getLineNumber,            ZEND_ACC_PUBLIC)
        PHP_V8_ME(StackFrame, getColumn,                ZEND_ACC_PUBLIC)
        PHP_V8_ME(StackFrame, getScriptId,              ZEND_ACC_PUBLIC)
        PHP_V8_ME(StackFrame, getScriptName,            ZEND_ACC_PUBLIC)
        PHP_V8_ME(StackFrame, getScriptNameOrSourceURL, ZEND_ACC_PUBLIC)
        PHP_V8_ME(StackFrame, getFunctionName,          ZEND_ACC_PUBLIC)
        PHP_V8_ME(StackFrame, isEval,                   ZEND_ACC_PUBLIC)
        PHP_V8_ME(StackFrame, isConstructor,            ZEND_ACC_PUBLIC)

        PHP_FE_END
};


PHP_MINIT_FUNCTION (php_v8_stack_frame) {
    zend_class_entry ce;
    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS, "StackFrame", php_v8_stack_frame_methods);
    this_ce = zend_register_internal_class(&ce);

    zend_declare_property_null(this_ce, ZEND_STRL("line_number"), ZEND_ACC_PRIVATE);
    zend_declare_property_null(this_ce, ZEND_STRL("column"),      ZEND_ACC_PRIVATE);
    zend_declare_property_null(this_ce, ZEND_STRL("script_id"),   ZEND_ACC_PRIVATE);

    zend_declare_property_string(this_ce, ZEND_STRL("script_name"),               "", ZEND_ACC_PRIVATE);
    zend_declare_property_string(this_ce, ZEND_STRL("script_name_or_source_url"), "", ZEND_ACC_PRIVATE);
    zend_declare_property_string(this_ce, ZEND_STRL("function_name"),             "", ZEND_ACC_PRIVATE);

    zend_declare_property_bool(this_ce, ZEND_STRL("is_eval"), static_cast<zend_bool>(false), ZEND_ACC_PRIVATE);
    zend_declare_property_bool(this_ce, ZEND_STRL("is_constructor"), static_cast<zend_bool>(false), ZEND_ACC_PRIVATE);

    return SUCCESS;
}
