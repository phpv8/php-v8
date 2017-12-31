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

#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include "php_v8_stack_trace.h"
#include "php_v8_stack_frame.h"
#include "php_v8_value.h"
#include "php_v8.h"

zend_class_entry* php_v8_stack_trace_class_entry;

#define this_ce php_v8_stack_trace_class_entry

void php_v8_stack_trace_create_from_stack_trace(zval *return_value, php_v8_isolate_t *php_v8_isolate, v8::Local<v8::StackTrace> trace) {

    assert(!trace.IsEmpty());

    object_init_ex(return_value, this_ce);

    PHP_V8_DECLARE_ISOLATE(php_v8_isolate);

    /* v8::StackTrace::GetFrames */
    /* v8::StackTrace::GetFrame */
    /* v8::StackTrace::GetFrameCount */
    zval frames_array_zv;
    uint32_t frames_cnt = static_cast<uint32_t>(trace->GetFrameCount()); // Can frames count value be negative?

    array_init_size(&frames_array_zv, frames_cnt);

    zval frame_zv;

    for (uint32_t i = 0; i < frames_cnt; i++) {
        php_v8_stack_frame_create_from_stack_frame(isolate, &frame_zv, trace->GetFrame(i));
        add_index_zval(&frames_array_zv, i, &frame_zv);
    }

    zend_update_property(this_ce, return_value, ZEND_STRL("frames"), &frames_array_zv);
    zval_ptr_dtor(&frames_array_zv);
}

static PHP_METHOD(StackTrace, __construct)
{
    zval *frames_zv = NULL;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "a", &frames_zv) == FAILURE) {
        return;
    }

    // TODO: check that all frame items are instance of StackFrame

    zend_update_property(this_ce, getThis(), ZEND_STRL("frames"), frames_zv);
}

static PHP_METHOD(StackTrace, getFrames)
{
    zval rv;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    RETVAL_ZVAL(zend_read_property(this_ce, getThis(), ZEND_STRL("frames"), 0, &rv), 1, 0);
}

static PHP_METHOD(StackTrace, getFrame)
{
    zval rv;

    zend_long index = -1;
    zval *frames = NULL;
    zval *frame = NULL;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "l", &index) == FAILURE) {
        return;
    }

    frames = zend_read_property(this_ce, getThis(), ZEND_STRL("frames"), 0, &rv);

    if (index < 0) {
        PHP_V8_THROW_EXCEPTION("Fame index is out of range");
        return;
    }

    frame = zend_hash_index_find(Z_ARRVAL_P(frames), static_cast<zend_ulong>(index));

    if (frame == NULL) {
        PHP_V8_THROW_EXCEPTION("Fame index is out of range");
        return;
    }

    RETVAL_ZVAL(frame, 1, 0);
}

static PHP_METHOD(StackTrace, getFrameCount)
{
    zval rv;
    uint32_t cnt = 0;

    zval *frames = NULL;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    frames = zend_read_property(this_ce, getThis(), ZEND_STRL("frames"), 0, &rv);

    cnt = zend_array_count(Z_ARRVAL_P(frames));

    RETURN_LONG(cnt);
}

static PHP_METHOD(StackTrace, currentStackTrace)
{
    zval *isolate_zv;
    zend_long frame_limit = 0;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "ol", &isolate_zv, &frame_limit) == FAILURE) {
        return;
    }

    PHP_V8_CHECK_STACK_TRACE_RANGE(frame_limit, "Frame limit is out of range");

    PHP_V8_ISOLATE_FETCH_WITH_CHECK(isolate_zv, php_v8_isolate);
    PHP_V8_DECLARE_ISOLATE(php_v8_isolate);

    v8::Local<v8::StackTrace> trace = v8::StackTrace::CurrentStackTrace(isolate, static_cast<int>(frame_limit));

    PHP_V8_THROW_VALUE_EXCEPTION_WHEN_EMPTY(trace, "Failed to get current stack trace");

    php_v8_stack_trace_create_from_stack_trace(return_value, php_v8_isolate, trace);
}

PHP_V8_ZEND_BEGIN_ARG_WITH_CONSTRUCTOR_INFO_EX(arginfo___construct, 1)
                ZEND_ARG_TYPE_INFO(0, frames, IS_ARRAY, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_getFrames, ZEND_RETURN_VALUE, 0, IS_ARRAY, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_getFrame, ZEND_RETURN_VALUE, 1, V8\\StackFrame, 0)
                ZEND_ARG_TYPE_INFO(0, index, IS_LONG, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_getFrameCount, ZEND_RETURN_VALUE, 0, IS_LONG, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_currentStackTrace, ZEND_RETURN_VALUE, 2, V8\\StackTrace, 0)
                ZEND_ARG_OBJ_INFO(0, isolate, V8\\Isolate, 0)
                ZEND_ARG_TYPE_INFO(0, frame_limit, IS_LONG, 0)
ZEND_END_ARG_INFO()


static const zend_function_entry php_v8_stack_trace_methods[] = {
        PHP_V8_ME(StackTrace, __construct,       ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)
        PHP_V8_ME(StackTrace, getFrames,         ZEND_ACC_PUBLIC)
        PHP_V8_ME(StackTrace, getFrame,          ZEND_ACC_PUBLIC)
        PHP_V8_ME(StackTrace, getFrameCount,     ZEND_ACC_PUBLIC)
        PHP_V8_ME(StackTrace, currentStackTrace, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)

        PHP_FE_END
};


PHP_MINIT_FUNCTION (php_v8_stack_trace) {
    zend_class_entry ce;
    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS, "StackTrace", php_v8_stack_trace_methods);
    this_ce = zend_register_internal_class(&ce);

    zend_declare_class_constant_long(this_ce, ZEND_STRL("MIN_FRAME_LIMIT"), PHP_V8_STACK_TRACE_MIN_FRAME_LIMIT);
    zend_declare_class_constant_long(this_ce, ZEND_STRL("MAX_FRAME_LIMIT"), PHP_V8_STACK_TRACE_MAX_FRAME_LIMIT);

    zend_declare_property_null(this_ce, ZEND_STRL("frames"), ZEND_ACC_PRIVATE);

    return SUCCESS;
}
