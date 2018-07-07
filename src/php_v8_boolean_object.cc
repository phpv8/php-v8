/*
 * This file is part of the phpv8/php-v8 PHP extension.
 *
 * Copyright (c) 2015-2018 Bogdan Padalko <thepinepain@gmail.com>
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

#include "php_v8_boolean_object.h"
#include "php_v8_value.h"
#include "php_v8_context.h"
#include "php_v8_object.h"
#include "php_v8.h"

zend_class_entry *php_v8_boolean_object_class_entry;
#define this_ce php_v8_boolean_object_class_entry


static PHP_METHOD(BooleanObject, __construct) {
    zval rv;
    zval *php_v8_context_zv;

    zend_bool value;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "ob", &php_v8_context_zv, &value) == FAILURE) {
        return;
    }

    PHP_V8_OBJECT_CONSTRUCT(getThis(), php_v8_context_zv, php_v8_context, php_v8_value);

    v8::Local<v8::BooleanObject> local_bool_obj = v8::BooleanObject::New(isolate, value).As<v8::BooleanObject>();

    php_v8_object_store_self_ptr(php_v8_value, local_bool_obj);

    php_v8_value->persistent->Reset(isolate, local_bool_obj);
}


static PHP_METHOD(BooleanObject, valueOf) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);

    v8::Local<v8::BooleanObject> local_boolean = php_v8_value_get_local_as<v8::BooleanObject>(php_v8_value);

    RETURN_BOOL(static_cast<zend_bool>(local_boolean->ValueOf()));
}


PHP_V8_ZEND_BEGIN_ARG_WITH_CONSTRUCTOR_INFO_EX(arginfo___construct, 2)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_TYPE_INFO(0, value, _IS_BOOL, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_valueOf, ZEND_RETURN_VALUE, 0, _IS_BOOL, 0)
ZEND_END_ARG_INFO()


static const zend_function_entry php_v8_boolean_object_methods[] = {
        PHP_V8_ME(BooleanObject, __construct, ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)
        PHP_V8_ME(BooleanObject, valueOf,     ZEND_ACC_PUBLIC)

        PHP_FE_END
};



PHP_MINIT_FUNCTION(php_v8_boolean_object) {
    zend_class_entry ce;
    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS, "BooleanObject", php_v8_boolean_object_methods);
    this_ce = zend_register_internal_class_ex(&ce, php_v8_object_class_entry);

    return SUCCESS;
}
