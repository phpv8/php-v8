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

#include "php_v8_property_callback_info.h"
#include "php_v8_callback_info.h"
#include "php_v8_return_value.h"
#include "php_v8_value.h"
#include "php_v8.h"

zend_class_entry *php_v8_property_callback_info_class_entry;
#define this_ce php_v8_property_callback_info_class_entry


template<class T>
php_v8_return_value_t *php_v8_callback_info_create_from_info_meta(zval *return_value, const v8::PropertyCallbackInfo<T> &args, int accepts);


php_v8_return_value_t *php_v8_callback_info_create_from_info(zval *return_value, const v8::PropertyCallbackInfo<v8::Value> &info) {
    return php_v8_callback_info_create_from_info_meta(return_value, info, PHP_V8_RETVAL_ACCEPTS_ANY);
}

php_v8_return_value_t *php_v8_callback_info_create_from_info(zval *return_value, const v8::PropertyCallbackInfo<v8::Array> &info) {
    return php_v8_callback_info_create_from_info_meta(return_value, info, PHP_V8_RETVAL_ACCEPTS_ARRAY);
}

php_v8_return_value_t *php_v8_callback_info_create_from_info(zval *return_value, const v8::PropertyCallbackInfo<v8::Integer> &info) {
    return php_v8_callback_info_create_from_info_meta(return_value, info, PHP_V8_RETVAL_ACCEPTS_INTEGER);
}

php_v8_return_value_t *php_v8_callback_info_create_from_info(zval *return_value, const v8::PropertyCallbackInfo<v8::Boolean> &info) {
    return php_v8_callback_info_create_from_info_meta(return_value, info, PHP_V8_RETVAL_ACCEPTS_BOOLEAN);
}

php_v8_return_value_t *php_v8_callback_info_create_from_info(zval *return_value, const v8::PropertyCallbackInfo<void> &info) {
    return php_v8_callback_info_create_from_info_meta(return_value, info, PHP_V8_RETVAL_ACCEPTS_VOID);
}

template<class T>
php_v8_return_value_t *php_v8_callback_info_create_from_info_meta(zval *return_value, const v8::PropertyCallbackInfo<T> &args, int accepts) {
    zval tmp;
    php_v8_return_value_t *php_v8_return_value;

    v8::Isolate *isolate = args.GetIsolate();
    v8::Local<v8::Context> context = isolate->GetCurrentContext();

    if (context.IsEmpty()) {
        PHP_V8_THROW_EXCEPTION("Internal exception: no calling context found");
        return NULL;
    }

    php_v8_isolate_t *php_v8_isolate = PHP_V8_ISOLATE_FETCH_REFERENCE(isolate);
    php_v8_context_t *php_v8_context = php_v8_context_get_reference(context);

    object_init_ex(return_value, this_ce);

    // common to both callback structures:
    // isolate
    ZVAL_OBJ(&tmp, &php_v8_isolate->std);
    zend_update_property(php_v8_callback_info_class_entry, return_value, ZEND_STRL("isolate"), &tmp);
    // context
    ZVAL_OBJ(&tmp, &php_v8_context->std);
    zend_update_property(php_v8_callback_info_class_entry, return_value, ZEND_STRL("context"), &tmp);
    // this
    php_v8_get_or_create_value(&tmp, args.This(), php_v8_isolate);
    zend_update_property(php_v8_callback_info_class_entry, return_value, ZEND_STRL("this"), &tmp);
    Z_DELREF(tmp);
    // holder
    php_v8_get_or_create_value(&tmp, args.Holder(), php_v8_isolate);
    zend_update_property(php_v8_callback_info_class_entry, return_value, ZEND_STRL("holder"), &tmp);
    Z_DELREF(tmp);
    // return value
    php_v8_return_value = php_v8_return_value_create_from_return_value(&tmp, php_v8_context, PHP_V8_RETVAL_ACCEPTS_ANY);
    zend_update_property(php_v8_callback_info_class_entry, return_value, ZEND_STRL("return_value"), &tmp);
    Z_DELREF(tmp);

    // specific to property callback structure:
    // should_throw_on_error
    zend_update_property_bool(this_ce, return_value, ZEND_STRL("should_throw_on_error"), static_cast<zend_bool>(args.ShouldThrowOnError()));

    return php_v8_return_value;
}

static PHP_METHOD(PropertyCallbackInfo, ShouldThrowOnError) {
    zval rv;
    zval *tmp;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    tmp = zend_read_property(this_ce, getThis(), ZEND_STRL("should_throw_on_error"), 0, &rv);
    ZVAL_COPY(return_value, tmp);
}

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_property_callback_info_ShouldThrowOnError, ZEND_RETURN_VALUE, 0, _IS_BOOL, 0)
ZEND_END_ARG_INFO()

static const zend_function_entry php_v8_property_callback_info_methods[] = {
        PHP_ME(PropertyCallbackInfo, ShouldThrowOnError, arginfo_v8_property_callback_info_ShouldThrowOnError, ZEND_ACC_PUBLIC)
        PHP_FE_END
};

PHP_MINIT_FUNCTION (php_v8_property_callback_info) {
    zend_class_entry ce;
    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS, "PropertyCallbackInfo", php_v8_property_callback_info_methods);
    this_ce = zend_register_internal_class_ex(&ce, php_v8_callback_info_class_entry);

    zend_declare_property_null(this_ce, ZEND_STRL("should_throw_on_error"), ZEND_ACC_PRIVATE);

    return SUCCESS;
}
