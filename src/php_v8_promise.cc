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

#include "php_v8_promise.h"
#include "php_v8_object.h"
#include "php_v8_string.h"
#include "php_v8_value.h"
#include "php_v8_context.h"
#include "php_v8.h"

zend_class_entry *php_v8_promise_class_entry;

#define this_ce php_v8_promise_class_entry


static PHP_METHOD(Promise, __construct) {
    zval rv;
    zval *php_v8_context_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o", &php_v8_context_zv) == FAILURE) {
        return;
    }

    PHP_V8_OBJECT_CONSTRUCT(getThis(), php_v8_context_zv, php_v8_context, php_v8_value);

    v8::MaybeLocal<v8::Promise::Resolver> maybe_local_resolver = v8::Promise::Resolver::New(context);

    PHP_V8_THROW_VALUE_EXCEPTION_WHEN_EMPTY(maybe_local_resolver, "Failed to create Promise object");

    // under the v8 hood v8::Promise::Resolver and v8::Promise are interchangable (with cast)
    v8::Local<v8::Promise::Resolver> local_resolver = maybe_local_resolver.ToLocalChecked();
    php_v8_object_store_self_ptr(php_v8_value, local_resolver);

    php_v8_value->persistent->Reset(isolate, local_resolver);
}

static PHP_METHOD(Promise, resolve) {
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

static PHP_METHOD(Promise, reject) {
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

static PHP_METHOD(Promise, catch) {
    zval *php_v8_context_zv;
    zval *php_v8_function_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "oo", &php_v8_context_zv, &php_v8_function_zv) == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);
    PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_function_zv, php_v8_function);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_context);
    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_function);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    v8::Local<v8::Promise> local_promise = php_v8_value_get_local_as<v8::Promise>(php_v8_value);
    v8::Local<v8::Function> local_function = php_v8_value_get_local_as<v8::Function>(php_v8_function);

    v8::MaybeLocal<v8::Promise> maybe_local_promise = local_promise->Catch(context, local_function);

    PHP_V8_THROW_VALUE_EXCEPTION_WHEN_EMPTY(maybe_local_promise, "Failed to register rejection handler with a promise");

    php_v8_get_or_create_value(return_value, maybe_local_promise.ToLocalChecked(), php_v8_context->php_v8_isolate);
}

static PHP_METHOD(Promise, then) {
    zval *php_v8_context_zv;
    zval *php_v8_function_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "oo", &php_v8_context_zv, &php_v8_function_zv) == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);
    PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_function_zv, php_v8_function);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_context);
    PHP_V8_DATA_ISOLATES_CHECK(php_v8_value, php_v8_function);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    v8::Local<v8::Promise> local_promise = php_v8_value_get_local_as<v8::Promise>(php_v8_value);
    v8::Local<v8::Function> local_function = php_v8_value_get_local_as<v8::Function>(php_v8_function);

    v8::MaybeLocal<v8::Promise> maybe_local_promise = local_promise->Then(context, local_function);

    PHP_V8_THROW_VALUE_EXCEPTION_WHEN_EMPTY(maybe_local_promise, "Failed to register resolution handler with a promise");

    php_v8_get_or_create_value(return_value, maybe_local_promise.ToLocalChecked(), php_v8_context->php_v8_isolate);
}

static PHP_METHOD(Promise, hasHandler) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);
    PHP_V8_ENTER_STORED_CONTEXT(php_v8_value);

    v8::Local<v8::Promise> local_promise = php_v8_value_get_local_as<v8::Promise>(php_v8_value);

    RETURN_BOOL(static_cast<zend_bool>(local_promise->HasHandler()));
}

static PHP_METHOD(Promise, result) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);
    PHP_V8_ENTER_STORED_CONTEXT(php_v8_value);

    v8::Local<v8::Promise> local_promise = php_v8_value_get_local_as<v8::Promise>(php_v8_value);

    if (v8::Promise::PromiseState::kPending == local_promise->State()) {
        PHP_V8_THROW_VALUE_EXCEPTION("Promise is in pending state");
        return;
    }

    v8::Local<v8::Value> local_value = local_promise->Result();

    php_v8_get_or_create_value(return_value, local_value, php_v8_value->php_v8_isolate);
}

static PHP_METHOD(Promise, state) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);
    PHP_V8_ENTER_STORED_CONTEXT(php_v8_value);

    v8::Local<v8::Promise> local_promise = php_v8_value_get_local_as<v8::Promise>(php_v8_value);

    RETURN_LONG(static_cast<zend_long>(local_promise->State()));
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

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_catch, ZEND_RETURN_VALUE, 2, V8\\PromiseObject, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_OBJ_INFO(0, handler, V8\\FunctionObject, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_then, ZEND_RETURN_VALUE, 2, V8\\PromiseObject, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_OBJ_INFO(0, handler, V8\\FunctionObject, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_hasHandler, ZEND_RETURN_VALUE, 0, _IS_BOOL, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_result, ZEND_RETURN_VALUE, 0, V8\\Value, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_state, ZEND_RETURN_VALUE, 0, IS_LONG, 0)
ZEND_END_ARG_INFO()


static const zend_function_entry php_v8_promise_methods[] = {
        PHP_V8_ME(Promise, __construct, ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)
        PHP_V8_ME(Promise, resolve,   ZEND_ACC_PUBLIC)
        PHP_V8_ME(Promise, reject,   ZEND_ACC_PUBLIC)
        PHP_V8_ME(Promise, catch,   ZEND_ACC_PUBLIC)
        PHP_V8_ME(Promise, then,    ZEND_ACC_PUBLIC)
        PHP_V8_ME(Promise, hasHandler,    ZEND_ACC_PUBLIC)
        PHP_V8_ME(Promise, result,    ZEND_ACC_PUBLIC)
        PHP_V8_ME(Promise, state,    ZEND_ACC_PUBLIC)

        PHP_FE_END
};


PHP_MINIT_FUNCTION(php_v8_promise) {
    zend_class_entry ce;
    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS, "PromiseObject", php_v8_promise_methods);
    this_ce = zend_register_internal_class_ex(&ce, php_v8_object_class_entry);

    zend_declare_class_constant_long(this_ce, ZEND_STRL("STATE_PENDING"),   static_cast<zend_long>(v8::Promise::PromiseState::kPending));
    zend_declare_class_constant_long(this_ce, ZEND_STRL("STATE_FULFILLED"), static_cast<zend_long>(v8::Promise::PromiseState::kFulfilled));
    zend_declare_class_constant_long(this_ce, ZEND_STRL("STATE_REJECTED"),  static_cast<zend_long>(v8::Promise::PromiseState::kRejected));

    return SUCCESS;
}
