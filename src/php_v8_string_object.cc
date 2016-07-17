/*
  +----------------------------------------------------------------------+
  | This file is part of the pinepain/php-v8 PHP extension.              |
  |                                                                      |
  | Copyright (c) 2015-2016 Bogdan Padalko <pinepain@gmail.com>          |
  |                                                                      |
  | Licensed under the MIT license: http://opensource.org/licenses/MIT   |
  |                                                                      |
  | For the full copyright and license information, please view the      |
  | LICENSE file that was distributed with this source or visit          |
  | http://opensource.org/licenses/MIT                                   |
  +----------------------------------------------------------------------+
*/

#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include "php_v8_string_object.h"
#include "php_v8_string.h"
#include "php_v8_object.h"
#include "php_v8_value.h"
#include "php_v8_context.h"
#include "php_v8.h"

zend_class_entry *php_v8_string_object_class_entry;
#define this_ce php_v8_string_object_class_entry

v8::Local<v8::StringObject> php_v8_value_get_string_object_local(v8::Isolate *isolate, php_v8_value_t *php_v8_value) {
    return v8::Local<v8::StringObject>::Cast(php_v8_value_get_value_local(isolate, php_v8_value));
};

static PHP_METHOD(V8StringObject, __construct) {
    zval rv;
    zval *php_v8_context_zv;
    zval *php_v8_string_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "oo", &php_v8_context_zv, &php_v8_string_zv) == FAILURE) {
        return;
    }

    PHP_V8_OBJECT_CONSTRUCT(getThis(), php_v8_context_zv, php_v8_context, php_v8_value);

    PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_string_zv, php_v8_value_to_set);

    v8::Local<v8::String> local_string = php_v8_value_get_string_local(isolate, php_v8_value_to_set);
    v8::Local<v8::StringObject> local_string_obj = v8::StringObject::New(local_string).As<v8::StringObject>();

    PHP_V8_THROW_VALUE_EXCEPTION_WHEN_EMPTY(local_string_obj, "Failed to create StringObject value");

    ZVAL_COPY_VALUE(&php_v8_value->this_ptr, getThis());
    php_v8_object_store_self_ptr(isolate, local_string_obj, php_v8_value);

    php_v8_value->persistent->Reset(isolate, local_string_obj);
}

static PHP_METHOD(V8StringObject, ValueOf) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);
    //PHP_V8_ENTER_STORED_CONTEXT(php_v8_value);

    v8::Local<v8::String> local_string = php_v8_value_get_string_object_local(isolate, php_v8_value)->ValueOf();

    php_v8_get_or_create_value(return_value, local_string, isolate);
}


ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_string_object___construct, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 2)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_OBJ_INFO(0, value, V8\\StringValue, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_string_object_ValueOf, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 0)
ZEND_END_ARG_INFO()


static const zend_function_entry php_v8_string_object_methods[] = {
        PHP_ME(V8StringObject, __construct, arginfo_v8_string_object___construct, ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)

        PHP_ME(V8StringObject, ValueOf, arginfo_v8_string_object_ValueOf, ZEND_ACC_PUBLIC)

        PHP_FE_END
};



PHP_MINIT_FUNCTION(php_v8_string_object) {
    zend_class_entry ce;
    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS, "StringObject", php_v8_string_object_methods);
    this_ce = zend_register_internal_class_ex(&ce, php_v8_object_class_entry);

    return SUCCESS;
}


/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: noet sw=4 ts=4 fdm=marker
 * vim<600: noet sw=4 ts=4
 */
