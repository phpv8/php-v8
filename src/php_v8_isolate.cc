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

#include "php_v8_isolate.h"
#include "php_v8_startup_data.h"
#include "php_v8_heap_statistics.h"

#include "php_v8_context.h"
#include "php_v8_exceptions.h"
#include "php_v8_stack_trace.h"
#include "php_v8_value.h"
#include "php_v8_a.h"
#include "php_v8.h"

#include <float.h>

#include <iostream>

zend_class_entry *php_v8_isolate_class_entry;
#define this_ce php_v8_isolate_class_entry

static zend_object_handlers php_v8_isolate_object_handlers;


static void php_v8_maybe_terminate_execution(php_v8_isolate_t *php_v8_isolate) {
    if (php_v8_isolate->isolate->IsExecutionTerminating()) {
        return;
    }

    php_v8_isolate->isolate->TerminateExecution();
}

static inline void php_v8_isolate_destroy(php_v8_isolate_t *php_v8_isolate) {
    v8::Isolate *isolate = nullptr;

    if (php_v8_isolate->isolate) {

        php_v8_maybe_terminate_execution(php_v8_isolate);

        if (CG(unclean_shutdown)) {
            // freeing order is not guaranteed upon unclean shutdown, so we explicitly exit all entered isolates,
            // to ensure that current one won't remain entered so that we'll properly dispose it below
            while ( (isolate = v8::Isolate::GetCurrent())) {
                isolate->Exit();
            }
        }

        php_v8_isolate->isolate->Dispose(); // this cause error when we try to call on already entered isolate
    }
}


static HashTable * php_v8_isolate_gc(zval *object, zval **table, int *n) {
    PHP_V8_ISOLATE_FETCH_INTO(object, php_v8_isolate);

    int size = 0;

    size += php_v8_isolate->weak_function_templates->getGcCount();
    size += php_v8_isolate->weak_object_templates->getGcCount();
    size += php_v8_isolate->weak_values->getGcCount();

    if (php_v8_isolate->gc_data_count < size) {
        php_v8_isolate->gc_data = (zval *)safe_erealloc(php_v8_isolate->gc_data, size, sizeof(zval), 0);
    }

    php_v8_isolate->gc_data_count = size;

    zval *gc_data = php_v8_isolate->gc_data;

    php_v8_isolate->weak_function_templates->collectGcZvals(gc_data);
    php_v8_isolate->weak_object_templates->collectGcZvals(gc_data);
    php_v8_isolate->weak_values->collectGcZvals(gc_data);

    *table = php_v8_isolate->gc_data;
    *n     = php_v8_isolate->gc_data_count;

    return zend_std_get_properties(object);
}

static void php_v8_isolate_free(zend_object *object) {
    php_v8_isolate_t *php_v8_isolate = php_v8_isolate_fetch_object(object);

    php_v8_isolate_limits_free(php_v8_isolate);

    if (php_v8_isolate->weak_function_templates) {
        delete php_v8_isolate->weak_function_templates;
    }

    if (php_v8_isolate->weak_object_templates) {
        delete php_v8_isolate->weak_object_templates;
    }

    if (php_v8_isolate->weak_values) {
        delete php_v8_isolate->weak_values;
    }

    if (php_v8_isolate->gc_data) {
        efree(php_v8_isolate->gc_data);
    }

    if (php_v8_isolate->isolate && PHP_V8_ISOLATE_HAS_VALID_HANDLE(php_v8_isolate)) {
        php_v8_isolate->key.Reset();
    }

    php_v8_isolate->key.~Persistent();

    php_v8_isolate_destroy(php_v8_isolate);

    zend_object_std_dtor(&php_v8_isolate->std);

    if (php_v8_isolate->create_params) {
        if (php_v8_isolate->create_params->array_buffer_allocator) {
            delete php_v8_isolate->create_params->array_buffer_allocator;
        }

        delete php_v8_isolate->create_params;
    }
}

static void php_v8_isolate_oom_error_callback(const char *location, bool is_heap_oom) {
    zend_error(E_ERROR, "V8 OOM hit: location=%s, is_heap_oom=%s\n", location, is_heap_oom ? "yes" : "no");
}

static zend_object *php_v8_isolate_ctor(zend_class_entry *ce) {
    php_v8_isolate_t *php_v8_isolate;

    php_v8_isolate = (php_v8_isolate_t *) ecalloc(1, sizeof(php_v8_isolate_t) + zend_object_properties_size(ce));

    zend_object_std_init(&php_v8_isolate->std, ce);
    object_properties_init(&php_v8_isolate->std, ce);

    php_v8_init();

    php_v8_isolate->create_params = new v8::Isolate::CreateParams();
    php_v8_isolate->create_params->array_buffer_allocator = v8::ArrayBuffer::Allocator::NewDefaultAllocator();

    php_v8_isolate->weak_function_templates = new phpv8::PersistentCollection<v8::FunctionTemplate>();
    php_v8_isolate->weak_object_templates = new phpv8::PersistentCollection<v8::ObjectTemplate>();
    php_v8_isolate->weak_values = new phpv8::PersistentCollection<v8::Value>();
    new(&php_v8_isolate->key) v8::Persistent<v8::Private>();

    php_v8_isolate->std.handlers = &php_v8_isolate_object_handlers;

    php_v8_isolate_limits_ctor(php_v8_isolate);

    return &php_v8_isolate->std;
}

static void php_v8_fatal_error_handler(const char *location, const char *message) /* {{{ */
{
    v8::Isolate *isolate = v8::Isolate::GetCurrent();
    assert(isolate != NULL); // as we set fatal error handler per-isolate, we should always have at least any of them as current one

    php_v8_isolate_t *php_v8_isolate = PHP_V8_ISOLATE_FETCH_REFERENCE(isolate);

    assert(NULL != php_v8_isolate);

    char *buff;
    if (location) {
        spprintf(&buff, 0, "%s %s", location, message);
    } else {
        spprintf(&buff, 0, "%s", message);
    }

    PHP_V8_THROW_EXCEPTION(buff);
    efree(buff);
}


static PHP_METHOD(V8Isolate, __construct) {
    zval *snapshot_zv = NULL;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "|o!", &snapshot_zv) == FAILURE) {
        return;
    }

    PHP_V8_ISOLATE_FETCH_INTO(getThis(), php_v8_isolate);

    if (snapshot_zv != NULL) {
        PHP_V8_STARTUP_DATA_FETCH_INTO(snapshot_zv, php_v8_startup_data);
        zend_update_property(this_ce, getThis(), ZEND_STRL("snapshot"), snapshot_zv);

        php_v8_isolate->create_params->snapshot_blob = php_v8_startup_data->blob;
    }

    php_v8_isolate->isolate = v8::Isolate::New(*php_v8_isolate->create_params);
    PHP_V8_ISOLATE_STORE_REFERENCE(php_v8_isolate);

    php_v8_isolate->isolate_handle = Z_OBJ_HANDLE_P(getThis());

    php_v8_isolate->isolate->SetFatalErrorHandler(php_v8_fatal_error_handler);
    php_v8_isolate->isolate->SetOOMErrorHandler(php_v8_isolate_oom_error_callback);

    PHP_V8_ENTER_ISOLATE(php_v8_isolate);

    v8::MaybeLocal<v8::String> local_key_string = v8::String::NewFromUtf8(isolate, "php-v8::self", v8::NewStringType::kInternalized);
    PHP_V8_THROW_EXCEPTION_WHEN_EMPTY(local_key_string, "Failed initialize Isolate");

    v8::Local<v8::Private> local_private_key = v8::Private::ForApi(isolate, local_key_string.ToLocalChecked());
    php_v8_isolate->key.Reset(isolate, local_private_key);
}

static PHP_METHOD(V8Isolate, SetTimeLimit) {
    double time_limit_in_seconds;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "d", &time_limit_in_seconds) == FAILURE) {
        return;
    }

    PHP_V8_ISOLATE_FETCH_WITH_CHECK(getThis(), php_v8_isolate);

    if (time_limit_in_seconds < 0) {
        PHP_V8_THROW_EXCEPTION("Time limit should be a non-negative float");
        return;
    }

    php_v8_isolate_limits_set_time_limit(php_v8_isolate, time_limit_in_seconds);

    zend_update_property_double(this_ce, getThis(), ZEND_STRL("time_limit"), time_limit_in_seconds);
    zend_update_property_bool(this_ce, getThis(), ZEND_STRL("time_limit_hit"), 0);
}

static PHP_METHOD(V8Isolate, GetTimeLimit) {
    zval rv;

    zval *prop = NULL;
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_ISOLATE_FETCH_WITH_CHECK(getThis(), php_v8_isolate);

    prop = zend_read_property(this_ce, getThis(), ZEND_STRL("time_limit"), 0, &rv);

    RETVAL_ZVAL(prop, 1, 0);
}

static PHP_METHOD(V8Isolate, IsTimeLimitHit) {
    zval rv;

    zval *prop = NULL;
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_ISOLATE_FETCH_WITH_CHECK(getThis(), php_v8_isolate);

    prop = zend_read_property(this_ce, getThis(), ZEND_STRL("time_limit_hit"), 0, &rv);

    RETVAL_ZVAL(prop, 1, 0);
}

static PHP_METHOD(V8Isolate, SetMemoryLimit) {
    long memory_limit_in_bytes;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "l", &memory_limit_in_bytes) == FAILURE) {
        return;
    }

    PHP_V8_ISOLATE_FETCH_WITH_CHECK(getThis(), php_v8_isolate);

    if (memory_limit_in_bytes < 0) {
        PHP_V8_THROW_EXCEPTION("Memory limit should be a non-negative numeric value");
        return;
    }

    php_v8_isolate_limits_set_memory_limit(php_v8_isolate, static_cast<size_t>(memory_limit_in_bytes));

    zend_update_property_long(this_ce, getThis(), ZEND_STRL("memory_limit"), memory_limit_in_bytes);
    zend_update_property_bool(this_ce, getThis(), ZEND_STRL("memory_limit_hit"), 0);
}

static PHP_METHOD(V8Isolate, GetMemoryLimit) {
    zval rv;

    zval *prop = NULL;
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_ISOLATE_FETCH_WITH_CHECK(getThis(), php_v8_isolate);

    prop = zend_read_property(this_ce, getThis(), ZEND_STRL("memory_limit"), 0, &rv);

    RETVAL_ZVAL(prop, 1, 0);
}

static PHP_METHOD(V8Isolate, IsMemoryLimitHit) {
    zval rv;

    zval *prop = NULL;
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_ISOLATE_FETCH_WITH_CHECK(getThis(), php_v8_isolate);

    prop = zend_read_property(this_ce, getThis(), ZEND_STRL("memory_limit_hit"), 0, &rv);

    RETVAL_ZVAL(prop, 1, 0);
}

static PHP_METHOD(V8Isolate, GetSnapshot) {
    zval rv;

    zval *prop = NULL;
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_ISOLATE_FETCH_WITH_CHECK(getThis(), php_v8_isolate);

    prop = zend_read_property(this_ce, getThis(), ZEND_STRL("snapshot"), 0, &rv);

    RETVAL_ZVAL(prop, 1, 0);
}

static PHP_METHOD(V8Isolate, GetHeapStatistics) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_ISOLATE_FETCH_WITH_CHECK(getThis(), php_v8_isolate);
    PHP_V8_ENTER_ISOLATE(php_v8_isolate)

    v8::HeapStatistics hs;

    isolate->GetHeapStatistics(&hs);

    php_v8_heap_statistics_create_from_heap_statistics(return_value, &hs);
}

static PHP_METHOD(V8Isolate, InContext) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_ISOLATE_FETCH_WITH_CHECK(getThis(), php_v8_isolate);
    PHP_V8_ENTER_ISOLATE(php_v8_isolate)


    RETURN_BOOL(php_v8_isolate->isolate->InContext())
}

static PHP_METHOD(V8Isolate, GetEnteredContext) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_ISOLATE_FETCH_WITH_CHECK(getThis(), php_v8_isolate);
    PHP_V8_ENTER_ISOLATE(php_v8_isolate)

    PHP_V8_ISOLATE_REQUIRE_IN_CONTEXT(isolate);

    v8::Local<v8::Context> local_context = php_v8_isolate->isolate->GetEnteredContext();

    php_v8_context_t *php_v8_context = php_v8_context_get_reference(local_context);

    ZVAL_OBJ(return_value, &php_v8_context->std);
    Z_ADDREF_P(return_value);
}

static PHP_METHOD(V8Isolate, ThrowException) {
    zval *php_v8_context_zv;
    zval *php_v8_value_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "oo", &php_v8_context_zv, &php_v8_value_zv) == FAILURE) {
        return;
    }

    PHP_V8_ISOLATE_FETCH_WITH_CHECK(getThis(), php_v8_isolate);

    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);
    PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_value_zv, php_v8_value);

    PHP_V8_DATA_ISOLATES_CHECK_USING(php_v8_context, php_v8_isolate);
    PHP_V8_DATA_ISOLATES_CHECK_USING(php_v8_value, php_v8_isolate);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    v8::Local<v8::Value> local_value = php_v8_value_get_local(php_v8_value);
    v8::Local<v8::Value> local_return_value = isolate->ThrowException(local_value);

    /* From v8 source code, Isolate::ThrowException() returns v8::Undefined() */
    php_v8_get_or_create_value(return_value, local_return_value, php_v8_value->php_v8_isolate);
}

static PHP_METHOD(V8Isolate, IdleNotificationDeadline) {
    double deadline_in_seconds;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "d", &deadline_in_seconds) == FAILURE) {
        return;
    }

    PHP_V8_ISOLATE_FETCH_WITH_CHECK(getThis(), php_v8_isolate);
    PHP_V8_ENTER_ISOLATE(php_v8_isolate);

    RETURN_BOOL(isolate->IdleNotificationDeadline(deadline_in_seconds));
}

static PHP_METHOD(V8Isolate, LowMemoryNotification) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_ISOLATE_FETCH_WITH_CHECK(getThis(), php_v8_isolate);
    PHP_V8_ENTER_ISOLATE(php_v8_isolate);

    isolate->LowMemoryNotification();
}


static PHP_METHOD(V8Isolate, ContextDisposedNotification) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_ISOLATE_FETCH_WITH_CHECK(getThis(), php_v8_isolate);
    PHP_V8_ENTER_ISOLATE(php_v8_isolate);

    RETURN_LONG((long) isolate->ContextDisposedNotification());
}

static PHP_METHOD(V8Isolate, TerminateExecution) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_ISOLATE_FETCH_WITH_CHECK(getThis(), php_v8_isolate);
    PHP_V8_DECLARE_ISOLATE(php_v8_isolate);
    // PHP_V8_ENTER_ISOLATE(php_v8_isolate); // we do not have to enter isolate for it termination

    // In theory, we do not want to ask for termination when it already in process
//    if (php_v8_isolate->isolate->IsExecutionTerminating()) {
//        return;
//    }

    isolate->TerminateExecution();
}

static PHP_METHOD(V8Isolate, IsExecutionTerminating) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_ISOLATE_FETCH_WITH_CHECK(getThis(), php_v8_isolate);
    PHP_V8_ENTER_ISOLATE(php_v8_isolate);

    RETURN_BOOL(isolate->IsExecutionTerminating());
}

static PHP_METHOD(V8Isolate, CancelTerminateExecution) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_ISOLATE_FETCH_WITH_CHECK(getThis(), php_v8_isolate);
    PHP_V8_ENTER_ISOLATE(php_v8_isolate);

    isolate->CancelTerminateExecution();
}

static PHP_METHOD(V8Isolate, SetCaptureStackTraceForUncaughtExceptions) {
    zend_bool capture;
    zend_long frame_limit = 10;
    zend_long options = static_cast<zend_long>(v8::StackTrace::StackTraceOptions::kOverview);

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "b|ll", &capture, &frame_limit, &options) == FAILURE) {
        return;
    }

    PHP_V8_CHECK_STACK_TRACE_RANGE(frame_limit, "Frame limit is out of range");

    PHP_V8_ISOLATE_FETCH_WITH_CHECK(getThis(), php_v8_isolate);
    PHP_V8_ENTER_ISOLATE(php_v8_isolate);

    isolate->SetCaptureStackTraceForUncaughtExceptions(static_cast<bool>(capture),
                                                       static_cast<int>(frame_limit),
                                                       static_cast<v8::StackTrace::StackTraceOptions>(options & PHP_V8_STACK_TRACE_OPTIONS));
}

static PHP_METHOD(V8Isolate, IsDead) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_ISOLATE_FETCH_WITH_CHECK(getThis(), php_v8_isolate);
    PHP_V8_DECLARE_ISOLATE(php_v8_isolate);

    RETURN_BOOL(isolate->IsDead());
}

static PHP_METHOD(V8Isolate, IsInUse) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_ISOLATE_FETCH_WITH_CHECK(getThis(), php_v8_isolate);
    PHP_V8_DECLARE_ISOLATE(php_v8_isolate);

    RETURN_BOOL(isolate->IsInUse());
}


ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_isolate___construct, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 0)
                ZEND_ARG_OBJ_INFO(0, snapshot, V8\\StartupData, 1)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_isolate_SetTimeLimit, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 1)
                ZEND_ARG_TYPE_INFO(0, time_limit_in_seconds, IS_DOUBLE, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_isolate_GetTimeLimit, ZEND_RETURN_VALUE, 0, IS_DOUBLE, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_isolate_IsTimeLimitHit, ZEND_RETURN_VALUE, 0, _IS_BOOL, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_isolate_SetMemoryLimit, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 1)
                ZEND_ARG_TYPE_INFO(0, memory_limit_in_bytes, IS_LONG, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_isolate_GetMemoryLimit, ZEND_RETURN_VALUE, 0, IS_LONG, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_isolate_IsMemoryLimitHit, ZEND_RETURN_VALUE, 0, _IS_BOOL, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_v8_isolate_GetSnapshot, ZEND_RETURN_VALUE, 0, V8\\StartupData, 1)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_v8_isolate_GetHeapStatistics, ZEND_RETURN_VALUE, 0, V8\\HeapStatistics, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_isolate_InContext, ZEND_RETURN_VALUE, 0, _IS_BOOL, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_v8_isolate_GetEnteredContext, ZEND_RETURN_VALUE, 0, V8\\Context, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_v8_isolate_ThrowException, ZEND_RETURN_VALUE, 2, V8\\Value, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_OBJ_INFO(0, value, V8\\Value, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_isolate_IdleNotificationDeadline, ZEND_RETURN_VALUE, 1, _IS_BOOL, 0)
                ZEND_ARG_INFO(0, deadline_in_seconds)
ZEND_END_ARG_INFO()

// void method
ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_isolate_LowMemoryNotification, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_isolate_ContextDisposedNotification, ZEND_RETURN_VALUE, 0, IS_LONG, 0)
ZEND_END_ARG_INFO()

// void method
ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_isolate_TerminateExecution, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_isolate_IsExecutionTerminating, ZEND_RETURN_VALUE, 0, _IS_BOOL, 0)
ZEND_END_ARG_INFO()

// void method
ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_isolate_CancelTerminateExecution, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 0)
ZEND_END_ARG_INFO()

// void method
ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_isolate_SetCaptureStackTraceForUncaughtExceptions, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 1)
                ZEND_ARG_TYPE_INFO(0, capture, _IS_BOOL, 0)
                ZEND_ARG_TYPE_INFO(0, frame_limit, IS_LONG, 0)
                ZEND_ARG_TYPE_INFO(0, options, IS_LONG, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_isolate_IsDead, ZEND_RETURN_VALUE, 0, _IS_BOOL, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_isolate_IsInUse, ZEND_RETURN_VALUE, 0, _IS_BOOL, 0)
ZEND_END_ARG_INFO()


static const zend_function_entry php_v8_isolate_methods[] = {
        PHP_ME(V8Isolate, __construct, arginfo_v8_isolate___construct, ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)

        PHP_ME(V8Isolate, SetTimeLimit, arginfo_v8_isolate_SetTimeLimit, ZEND_ACC_PUBLIC)
        PHP_ME(V8Isolate, GetTimeLimit, arginfo_v8_isolate_GetTimeLimit, ZEND_ACC_PUBLIC)
        PHP_ME(V8Isolate, IsTimeLimitHit, arginfo_v8_isolate_IsTimeLimitHit, ZEND_ACC_PUBLIC)

        PHP_ME(V8Isolate, SetMemoryLimit, arginfo_v8_isolate_SetMemoryLimit, ZEND_ACC_PUBLIC)
        PHP_ME(V8Isolate, GetMemoryLimit, arginfo_v8_isolate_GetMemoryLimit, ZEND_ACC_PUBLIC)
        PHP_ME(V8Isolate, IsMemoryLimitHit, arginfo_v8_isolate_IsMemoryLimitHit, ZEND_ACC_PUBLIC)

        PHP_ME(V8Isolate, GetSnapshot, arginfo_v8_isolate_GetSnapshot, ZEND_ACC_PUBLIC)

        PHP_ME(V8Isolate, GetHeapStatistics, arginfo_v8_isolate_GetHeapStatistics, ZEND_ACC_PUBLIC)

        PHP_ME(V8Isolate, InContext, arginfo_v8_isolate_InContext, ZEND_ACC_PUBLIC)
        PHP_ME(V8Isolate, GetEnteredContext, arginfo_v8_isolate_GetEnteredContext, ZEND_ACC_PUBLIC)

        PHP_ME(V8Isolate, ThrowException, arginfo_v8_isolate_ThrowException, ZEND_ACC_PUBLIC)
        PHP_ME(V8Isolate, IdleNotificationDeadline, arginfo_v8_isolate_IdleNotificationDeadline, ZEND_ACC_PUBLIC)
        PHP_ME(V8Isolate, LowMemoryNotification, arginfo_v8_isolate_LowMemoryNotification, ZEND_ACC_PUBLIC)
        PHP_ME(V8Isolate, ContextDisposedNotification, arginfo_v8_isolate_ContextDisposedNotification, ZEND_ACC_PUBLIC)

        PHP_ME(V8Isolate, TerminateExecution, arginfo_v8_isolate_TerminateExecution, ZEND_ACC_PUBLIC)
        PHP_ME(V8Isolate, IsExecutionTerminating, arginfo_v8_isolate_IsExecutionTerminating, ZEND_ACC_PUBLIC)
        PHP_ME(V8Isolate, CancelTerminateExecution, arginfo_v8_isolate_CancelTerminateExecution, ZEND_ACC_PUBLIC)

        PHP_ME(V8Isolate, SetCaptureStackTraceForUncaughtExceptions, arginfo_v8_isolate_SetCaptureStackTraceForUncaughtExceptions, ZEND_ACC_PUBLIC)
        PHP_ME(V8Isolate, IsDead, arginfo_v8_isolate_IsDead, ZEND_ACC_PUBLIC)
        PHP_ME(V8Isolate, IsInUse, arginfo_v8_isolate_IsInUse, ZEND_ACC_PUBLIC)

        PHP_FE_END
};


PHP_MINIT_FUNCTION (php_v8_isolate) {
    zend_class_entry ce;

    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS, "Isolate", php_v8_isolate_methods);
    this_ce = zend_register_internal_class(&ce);
    this_ce->create_object = php_v8_isolate_ctor;

    zend_declare_property_null(this_ce, ZEND_STRL("snapshot"), ZEND_ACC_PRIVATE);

    zend_declare_property_double(this_ce, ZEND_STRL("time_limit"), 0.0, ZEND_ACC_PRIVATE);
    zend_declare_property_bool(this_ce, ZEND_STRL("time_limit_hit"), 0, ZEND_ACC_PRIVATE);

    zend_declare_property_long(this_ce, ZEND_STRL("memory_limit"), 0, ZEND_ACC_PRIVATE);
    zend_declare_property_bool(this_ce, ZEND_STRL("memory_limit_hit"), 0, ZEND_ACC_PRIVATE);

    memcpy(&php_v8_isolate_object_handlers, zend_get_std_object_handlers(), sizeof(zend_object_handlers));

    php_v8_isolate_object_handlers.offset    = XtOffsetOf(php_v8_isolate_t, std);
    php_v8_isolate_object_handlers.free_obj  = php_v8_isolate_free;
    php_v8_isolate_object_handlers.get_gc    = php_v8_isolate_gc;
    php_v8_isolate_object_handlers.clone_obj = NULL;

    return SUCCESS;
}
