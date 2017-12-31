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

#include "php_v8_date.h"
#include "php_v8_object.h"
#include "php_v8_value.h"
#include "php_v8_context.h"
#include "php_v8.h"

zend_class_entry *php_v8_date_class_entry;
#define this_ce php_v8_date_class_entry


static PHP_METHOD(Date, __construct) {
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

    php_v8_object_store_self_ptr(php_v8_value, local_date);

    php_v8_value->persistent->Reset(isolate, local_date);
}


static PHP_METHOD(Date, valueOf) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);

    RETURN_DOUBLE(php_v8_value_get_local_as<v8::Date>(php_v8_value)->ValueOf());
}

static PHP_METHOD(Date, dateTimeConfigurationChangeNotification) {
    zval *isolate_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o", &isolate_zv) == FAILURE) {
        return;
    }

    PHP_V8_ISOLATE_FETCH_WITH_CHECK(isolate_zv, php_v8_isolate);

    v8::Date::DateTimeConfigurationChangeNotification(php_v8_isolate->isolate);
}


PHP_V8_ZEND_BEGIN_ARG_WITH_CONSTRUCTOR_INFO_EX(arginfo___construct, 2)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_TYPE_INFO(0, time, IS_DOUBLE, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_valueOf, ZEND_RETURN_VALUE, 0, IS_DOUBLE, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_VOID_INFO_EX(arginfo_dateTimeConfigurationChangeNotification, 1)
                ZEND_ARG_OBJ_INFO(0, isolate, V8\\isolate, 0)
ZEND_END_ARG_INFO()


static const zend_function_entry php_v8_date_methods[] = {
        PHP_V8_ME(Date, __construct, ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)
        PHP_V8_ME(Date, valueOf,     ZEND_ACC_PUBLIC)

        PHP_V8_ME(Date, dateTimeConfigurationChangeNotification, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)

        PHP_FE_END
};


PHP_MINIT_FUNCTION(php_v8_date) {
    zend_class_entry ce;
    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS, "DateObject", php_v8_date_methods);
    this_ce = zend_register_internal_class_ex(&ce, php_v8_object_class_entry);

    return SUCCESS;
}
