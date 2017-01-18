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

#include "php_v8_symbol_object.h"
#include "php_v8_symbol.h"
#include "php_v8_object.h"
#include "php_v8_value.h"
#include "php_v8_context.h"
#include "php_v8.h"

zend_class_entry *php_v8_symbol_object_class_entry;
#define this_ce php_v8_symbol_object_class_entry

v8::Local<v8::SymbolObject> php_v8_value_get_symbol_object_local(v8::Isolate *isolate, php_v8_value_t *php_v8_value) {
    return v8::Local<v8::SymbolObject>::Cast(php_v8_value_get_value_local(isolate, php_v8_value));
};

static PHP_METHOD(V8SymbolObject, __construct) {
    zval rv;
    zval *php_v8_context_zv;
    zval *php_v8_symbol_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "oo", &php_v8_context_zv, &php_v8_symbol_zv) == FAILURE) {
        return;
    }

    PHP_V8_OBJECT_CONSTRUCT(getThis(), php_v8_context_zv, php_v8_context, php_v8_value);
    PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_symbol_zv, php_v8_value_to_set);
    PHP_V8_DATA_ISOLATES_CHECK(php_v8_context, php_v8_value_to_set);

    v8::Local<v8::Symbol> local_symbol = php_v8_value_get_symbol_local(isolate, php_v8_value_to_set);

    v8::Local<v8::SymbolObject> local_symbol_obj = v8::SymbolObject::New(isolate, local_symbol).As<v8::SymbolObject>();

    PHP_V8_THROW_VALUE_EXCEPTION_WHEN_EMPTY(local_symbol_obj, "Failed to create SymbolObject value");

    ZVAL_COPY_VALUE(&php_v8_value->this_ptr, getThis());
    php_v8_object_store_self_ptr(isolate, local_symbol_obj, php_v8_value);

    php_v8_value->persistent->Reset(isolate, local_symbol_obj);
}

static PHP_METHOD(V8SymbolObject, ValueOf) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);
    PHP_V8_ENTER_STORED_CONTEXT(php_v8_value);

    v8::Local<v8::Symbol> local_symbol = php_v8_value_get_symbol_object_local(isolate, php_v8_value)->ValueOf();

    php_v8_get_or_create_value(return_value, local_symbol, isolate);
}


ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_symbol_object___construct, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 2)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_OBJ_INFO(0, value, V8\\SymbolValue, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_v8_symbol_object_ValueOf, ZEND_RETURN_VALUE, 0, V8\\SymbolValue, 0)
ZEND_END_ARG_INFO()


static const zend_function_entry php_v8_symbol_object_methods[] = {
        PHP_ME(V8SymbolObject, __construct, arginfo_v8_symbol_object___construct, ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)

        PHP_ME(V8SymbolObject, ValueOf, arginfo_v8_symbol_object_ValueOf, ZEND_ACC_PUBLIC)

        PHP_FE_END
};


PHP_MINIT_FUNCTION(php_v8_symbol_object) {
    zend_class_entry ce;
    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS, "SymbolObject", php_v8_symbol_object_methods);
    this_ce = zend_register_internal_class_ex(&ce, php_v8_object_class_entry);

    return SUCCESS;
}
