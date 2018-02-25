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

#include "php_v8_proxy.h"
#include "php_v8_object.h"
#include "php_v8_value.h"
#include "php_v8_context.h"
#include "php_v8.h"

zend_class_entry *php_v8_proxy_class_entry;

#define this_ce php_v8_proxy_class_entry


static PHP_METHOD(Proxy, __construct) {
    zval rv;
    zval *php_v8_context_zv;
    zval *php_v8_target_zv;
    zval *php_v8_handler_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "ooo", &php_v8_context_zv, &php_v8_target_zv, &php_v8_handler_zv) ==
        FAILURE) {
        return;
    }

    PHP_V8_OBJECT_CONSTRUCT(getThis(), php_v8_context_zv, php_v8_context, php_v8_value);
    PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_target_zv, php_v8_target);
    PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_handler_zv, php_v8_handler);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_target, php_v8_context);
    PHP_V8_DATA_ISOLATES_CHECK(php_v8_handler, php_v8_context);

    v8::Local<v8::Object> local_target = php_v8_value_get_local_as<v8::Object>(php_v8_target);
    v8::Local<v8::Object> local_handler = php_v8_value_get_local_as<v8::Object>(php_v8_handler);

    v8::MaybeLocal<v8::Proxy> maybe_local_proxy = v8::Proxy::New(context, local_target, local_handler);

    PHP_V8_THROW_VALUE_EXCEPTION_WHEN_EMPTY(maybe_local_proxy, "Failed to create Proxy object");

    v8::Local<v8::Proxy> local_Proxy = maybe_local_proxy.ToLocalChecked();

    php_v8_object_store_self_ptr(php_v8_value, local_Proxy);

    php_v8_value->persistent->Reset(isolate, local_Proxy);
}


static PHP_METHOD(Proxy, getTarget) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);
    PHP_V8_ENTER_STORED_CONTEXT(php_v8_value);

    v8::Local<v8::Value> local_target = php_v8_value_get_local_as<v8::Proxy>(php_v8_value)->GetTarget();

    PHP_V8_THROW_VALUE_EXCEPTION_WHEN_UNDEFINED(local_target, "Failed to get Proxy target"); // this should never happen

    if (local_target->IsNull()) {
        RETURN_NULL();
    }

    php_v8_get_or_create_value(return_value, local_target, php_v8_value->php_v8_isolate);
}

static PHP_METHOD(Proxy, getHandler) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);
    PHP_V8_ENTER_STORED_CONTEXT(php_v8_value);

    v8::Local<v8::Value> local_handler = php_v8_value_get_local_as<v8::Proxy>(php_v8_value)->GetHandler();

    // this should never happen
    PHP_V8_THROW_VALUE_EXCEPTION_WHEN_UNDEFINED(local_handler, "Failed to get Proxy handler"); // this should never happen

    if (local_handler->IsNull()) {
        RETURN_NULL();
    }

    php_v8_get_or_create_value(return_value, local_handler, php_v8_value->php_v8_isolate);
}

static PHP_METHOD(Proxy, isRevoked) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);
    PHP_V8_ENTER_STORED_CONTEXT(php_v8_value);

    RETURN_BOOL(static_cast<zend_bool>(php_v8_value_get_local_as<v8::Proxy>(php_v8_value)->IsRevoked()));
}

static PHP_METHOD(Proxy, revoke) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);
    PHP_V8_ENTER_STORED_CONTEXT(php_v8_value);

    php_v8_value_get_local_as<v8::Proxy>(php_v8_value)->Revoke();
}


PHP_V8_ZEND_BEGIN_ARG_WITH_CONSTRUCTOR_INFO_EX(arginfo___construct, 2)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_OBJ_INFO(0, target, V8\\ObjectValue, 0)
                ZEND_ARG_OBJ_INFO(0, handler, V8\\ObjectValue, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_getTarget, ZEND_RETURN_VALUE, 0, V8\\ObjectValue, 1)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_getHandler, ZEND_RETURN_VALUE, 0, V8\\ObjectValue, 1)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_isRevoked, ZEND_RETURN_VALUE, 0, _IS_BOOL, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_VOID_INFO_EX(arginfo_revoke, 0)
ZEND_END_ARG_INFO()


static const zend_function_entry php_v8_proxy_methods[] = {
        PHP_V8_ME(Proxy, __construct, ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)
        PHP_V8_ME(Proxy, getTarget, ZEND_ACC_PUBLIC)
        PHP_V8_ME(Proxy, getHandler, ZEND_ACC_PUBLIC)
        PHP_V8_ME(Proxy, isRevoked, ZEND_ACC_PUBLIC)
        PHP_V8_ME(Proxy, revoke, ZEND_ACC_PUBLIC)

        PHP_FE_END
};


PHP_MINIT_FUNCTION (php_v8_proxy) {
    zend_class_entry ce;
    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS, "ProxyObject", php_v8_proxy_methods);
    this_ce = zend_register_internal_class_ex(&ce, php_v8_object_class_entry);

    return SUCCESS;
}
