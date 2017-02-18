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

#include "php_v8_date.h"
#include "php_v8_object.h"
#include "php_v8_value.h"
#include "php_v8_context.h"
#include "php_v8.h"

zend_class_entry *php_v8_date_class_entry;
#define this_ce php_v8_date_class_entry

v8::Local<v8::Date> php_v8_value_get_date_local(v8::Isolate *isolate, php_v8_value_t *php_v8_value) {
    return v8::Local<v8::Date>::Cast(php_v8_value_get_value_local(isolate, php_v8_value));
};

static PHP_METHOD(V8Date, __construct) {
    zval rv;
    zval *php_v8_context_zv;

    double time;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "od", &php_v8_context_zv, &time) == FAILURE) {
        return;
    }

    PHP_V8_OBJECT_CONSTRUCT(getThis(), php_v8_context_zv, php_v8_context, php_v8_value);

    v8::MaybeLocal<v8::Value> maybe_local_date = v8::Date::New(context, time);

    PHP_V8_THROW_VALUE_EXCEPTION_WHEN_EMPTY(maybe_local_date, "Failed to create Date value");

    v8::Local<v8::Date> local_date = maybe_local_date.ToLocalChecked().As<v8::Date>();

    ZVAL_COPY_VALUE(&php_v8_value->this_ptr, getThis());
    php_v8_object_store_self_ptr(php_v8_value, local_date);

    php_v8_value->persistent->Reset(isolate, local_date);
}


static PHP_METHOD(V8Date, ValueOf) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);

    RETURN_DOUBLE(php_v8_value_get_date_local(isolate, php_v8_value)->ValueOf());
}


///**
// * Notification that the embedder has changed the time zone,
// * daylight savings time, or other date / time configuration
// * parameters.  V8 keeps a cache of various values used for
// * date / time computation.  This notification will reset
// * those cached values for the current context so that date /
// * time configuration changes would be reflected in the Date
// * object.
// *
// * This API should not be called more than needed as it will
// * negatively impact the performance of date operations.
// */
//static void DateTimeConfigurationChangeNotification(Isolate* isolate);
static PHP_METHOD(V8Date, DateTimeConfigurationChangeNotification) {
    zval *isolate_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o", &isolate_zv) == FAILURE) {
        return;
    }

    PHP_V8_ISOLATE_FETCH_WITH_CHECK(isolate_zv, php_v8_isolate);

    v8::Date::DateTimeConfigurationChangeNotification(php_v8_isolate->isolate);
}


ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_date___construct, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 2)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_TYPE_INFO(0, time, IS_DOUBLE, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_date_ValueOf, ZEND_RETURN_VALUE, 0, IS_DOUBLE, 0)
ZEND_END_ARG_INFO()

// void method
ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_date_DateTimeConfigurationChangeNotification, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 1)
                ZEND_ARG_OBJ_INFO(0, isolate, V8\\isolate, 0)
ZEND_END_ARG_INFO()


static const zend_function_entry php_v8_date_methods[] = {
        PHP_ME(V8Date, __construct, arginfo_v8_date___construct, ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)

        PHP_ME(V8Date, ValueOf, arginfo_v8_date_ValueOf, ZEND_ACC_PUBLIC)

        PHP_ME(V8Date, DateTimeConfigurationChangeNotification, arginfo_v8_date_DateTimeConfigurationChangeNotification, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)

        PHP_FE_END
};


PHP_MINIT_FUNCTION(php_v8_date) {
    zend_class_entry ce;
    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS, "DateObject", php_v8_date_methods);
    this_ce = zend_register_internal_class_ex(&ce, php_v8_object_class_entry);

    return SUCCESS;
}
