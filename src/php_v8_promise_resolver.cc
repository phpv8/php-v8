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

#include "php_v8_promise_resolver.h"
#include "php_v8_promise.h"
#include "php_v8_object.h"
#include "php_v8_string.h"
#include "php_v8_value.h"
#include "php_v8_context.h"
#include "php_v8.h"

zend_class_entry *php_v8_promise_resolver_class_entry;

#define this_ce php_v8_promise_resolver_class_entry


static PHP_METHOD(Resolver, __construct) {
    zval rv;
    zval *php_v8_context_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o", &php_v8_context_zv) == FAILURE) {
        return;
    }

    PHP_V8_OBJECT_CONSTRUCT(getThis(), php_v8_context_zv, php_v8_context, php_v8_value);

    v8::MaybeLocal<v8::Promise::Resolver> maybe_local_resolver = v8::Promise::Resolver::New(context);

    PHP_V8_THROW_VALUE_EXCEPTION_WHEN_EMPTY(maybe_local_resolver, "Failed to create Resolver object");

    v8::Local<v8::Promise::Resolver> local_resolver = maybe_local_resolver.ToLocalChecked();
    php_v8_object_store_self_ptr(php_v8_value, local_resolver);

    php_v8_value->persistent->Reset(isolate, local_resolver);
}

static PHP_METHOD(Resolver, resolve) {
    zval *php_v8_context_zv;
    zval *php_v8_rvalue_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "oo", &php_v8_context_zv, &php_v8_rvalue_zv) == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);
    PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_rvalue_zv, php_v8_rvalue);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_context);
    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_rvalue);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    v8::Local<v8::Promise::Resolver> local_resolver = php_v8_value_get_local_as<v8::Promise::Resolver>(php_v8_value);
    v8::Local<v8::Value> local_rvalue = php_v8_value_get_local_as<v8::Value>(php_v8_rvalue);

    v8::Maybe<bool> maybe_resolved = local_resolver->Resolve(context, local_rvalue);

    PHP_V8_THROW_VALUE_EXCEPTION_WHEN_NOTHING(maybe_resolved, "Failed to resolve a promise");
}

static PHP_METHOD(Resolver, reject) {
    zval *php_v8_context_zv;
    zval *php_v8_rvalue_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "oo", &php_v8_context_zv, &php_v8_rvalue_zv) == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);
    PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_rvalue_zv, php_v8_rvalue);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_context);
    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_rvalue);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    v8::Local<v8::Promise::Resolver> local_resolver = php_v8_value_get_local_as<v8::Promise::Resolver>(php_v8_value);
    v8::Local<v8::Value> local_rvalue = php_v8_value_get_local_as<v8::Value>(php_v8_rvalue);

    v8::Maybe<bool> maybe_rejected = local_resolver->Reject(context, local_rvalue);

    PHP_V8_THROW_VALUE_EXCEPTION_WHEN_NOTHING(maybe_rejected, "Failed to reject a promise");
}


PHP_V8_ZEND_BEGIN_ARG_WITH_CONSTRUCTOR_INFO_EX(arginfo___construct, 1)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_VOID_INFO_EX(arginfo_resolve, 2)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_OBJ_INFO(0, value, V8\\Value, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_VOID_INFO_EX(arginfo_reject, 2)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_OBJ_INFO(0, value, V8\\Value, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_getPromise, ZEND_RETURN_VALUE, 1, V8\\PromiseObject, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
ZEND_END_ARG_INFO()


static const zend_function_entry php_v8_promise_resolver_methods[] = {
        PHP_V8_ME(Resolver, __construct, ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)
        PHP_V8_ME(Resolver, resolve,   ZEND_ACC_PUBLIC)
        PHP_V8_ME(Resolver, reject,   ZEND_ACC_PUBLIC)

        PHP_FE_END
};


PHP_MINIT_FUNCTION(php_v8_promise_resolver) {
    zend_class_entry ce;
    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS "\\PromiseObject", "ResolverObject", php_v8_promise_resolver_methods);
    this_ce = zend_register_internal_class_ex(&ce, php_v8_promise_class_entry);

    return SUCCESS;
}
