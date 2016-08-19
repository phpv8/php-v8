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

#include "php_v8_function_callback_info.h"
#include "php_v8_exceptions.h"
#include "php_v8_callback_info.h"
#include "php_v8_return_value.h"
#include "php_v8_value.h"
#include "php_v8.h"

zend_class_entry* php_v8_function_callback_info_class_entry;
#define this_ce php_v8_function_callback_info_class_entry


php_v8_callback_info_t *php_v8_callback_info_create_from_info(zval *this_ptr, const v8::FunctionCallbackInfo<v8::Value> &args) {
    zval retval;
    v8::Isolate *isolate = args.GetIsolate();
    v8::Local<v8::Context> context = isolate->GetCurrentContext();

    if (context.IsEmpty()) {
        PHP_V8_THROW_EXCEPTION("Internal exception: no calling context found");
        return NULL;
    }

    object_init_ex(this_ptr, this_ce);
    PHP_V8_CALLBACK_INFO_FETCH_INTO(this_ptr, php_v8_callback_info);

    php_v8_callback_info->php_v8_isolate = PHP_V8_ISOLATE_FETCH_REFERENCE(isolate);
    php_v8_callback_info->php_v8_context = php_v8_context_get_reference(context);

    php_v8_callback_info->this_obj->Reset(isolate, args.This());
    php_v8_callback_info->holder_obj->Reset(isolate, args.Holder());

    php_v8_callback_info->php_v8_return_value = php_v8_return_value_create_from_return_value(
            &retval,
            php_v8_callback_info->php_v8_isolate,
            php_v8_callback_info->php_v8_context,
            PHP_V8_RETVAL_ACCEPTS_ANY
    );

    /* function callback specific part */
    php_v8_callback_info->length = args.Length();

    php_v8_callback_info->arguments = (v8::Persistent<v8::Value> **) ecalloc(static_cast<size_t>(php_v8_callback_info->length), sizeof(*php_v8_callback_info->arguments));

    for (int i=0; i < php_v8_callback_info->length; i++) {
        php_v8_callback_info->arguments[i] = new v8::Persistent<v8::Value>(isolate, args[i]);
    }

    php_v8_callback_info->is_construct_call = args.IsConstructCall();

    return php_v8_callback_info;
}


static PHP_METHOD(V8FunctionCallbackInfo, Length) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_CALLBACK_INFO_FETCH_WITH_CHECK(getThis(), php_v8_callback_info);
    PHP_V8_V8_CALLBACK_INFO_CHECK_IN_CONTEXT(php_v8_callback_info);

    RETURN_LONG(static_cast<zend_long>(php_v8_callback_info->length));
}

static PHP_METHOD(V8FunctionCallbackInfo, Arguments) {
    v8::Local<v8::Value> local_value;

    zval arg_zv;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_CALLBACK_INFO_FETCH_WITH_CHECK(getThis(), php_v8_callback_info);
    PHP_V8_V8_CALLBACK_INFO_CHECK_IN_CONTEXT(php_v8_callback_info);

    if (!Z_ISUNDEF(php_v8_callback_info->args)) {
        RETURN_ZVAL(&php_v8_callback_info->args, 1, 0);
    }

    // TODO: looks like PHP_V8_V8_CALLBACK_INFO_CHECK_IN_CONTEXT make sure we have current context, but check this one more time
    v8::Isolate *isolate = php_v8_callback_info->php_v8_isolate->isolate;

    array_init_size(&php_v8_callback_info->args, static_cast<uint>(php_v8_callback_info->length));

    for (int i=0; i < php_v8_callback_info->length; i++) {

        local_value = v8::Local<v8::Value>::New(isolate, *php_v8_callback_info->arguments[i]);

        php_v8_get_or_create_value(&arg_zv, local_value, isolate);

        add_index_zval(&php_v8_callback_info->args, static_cast<zend_ulong>(i), &arg_zv);
    }

    RETURN_ZVAL(&php_v8_callback_info->args, 1, 0);
}

static PHP_METHOD(V8FunctionCallbackInfo, IsConstructCall) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_CALLBACK_INFO_FETCH_WITH_CHECK(getThis(), php_v8_callback_info);
    PHP_V8_V8_CALLBACK_INFO_CHECK_IN_CONTEXT(php_v8_callback_info);

    RETURN_BOOL(php_v8_callback_info->is_construct_call)
}


ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_function_callback_info_Length, ZEND_RETURN_VALUE, 0, IS_LONG, NULL, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_function_callback_info_Arguments, ZEND_RETURN_VALUE, 0, IS_ARRAY, NULL, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_function_callback_info_IsConstructCall, ZEND_RETURN_VALUE, 0, _IS_BOOL, NULL, 0)
ZEND_END_ARG_INFO()


static const zend_function_entry php_v8_function_callback_info_methods[] = {
        PHP_ME(V8FunctionCallbackInfo, Length, arginfo_v8_function_callback_info_Length, ZEND_ACC_PUBLIC)
        PHP_ME(V8FunctionCallbackInfo, Arguments, arginfo_v8_function_callback_info_Arguments, ZEND_ACC_PUBLIC)
        PHP_ME(V8FunctionCallbackInfo, IsConstructCall, arginfo_v8_function_callback_info_IsConstructCall, ZEND_ACC_PUBLIC)
        PHP_FE_END
};

PHP_MINIT_FUNCTION(php_v8_function_callback_info) {
    zend_class_entry ce;

    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS, "FunctionCallbackInfo", php_v8_function_callback_info_methods);
    this_ce = zend_register_internal_class_ex(&ce, php_v8_callback_info_class_entry);

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


