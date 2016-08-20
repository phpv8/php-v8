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

#include "php_v8_callback_info.h"
#include "php_v8_return_value.h"
#include "php_v8_value.h"
#include "php_v8.h"


zend_class_entry *php_v8_callback_info_class_entry;
#define this_ce php_v8_callback_info_class_entry

static zend_object_handlers php_v8_callback_info_object_handlers;

php_v8_callback_info_t * php_v8_callback_info_fetch_object(zend_object *obj) {
    return (php_v8_callback_info_t *)((char *)obj - XtOffsetOf(php_v8_callback_info_t, std));
}

void php_v8_callback_info_invalidate(php_v8_callback_info_t *php_v8_callback_info) {
    if (php_v8_callback_info->php_v8_return_value) {
        php_v8_return_value_mark_expired(php_v8_callback_info->php_v8_return_value);
    }
}


static HashTable * php_v8_callback_info_gc(zval *object, zval **table, int *n) {
    PHP_V8_CALLBACK_INFO_FETCH_INTO(object, php_v8_callback_info);

    int size = 2; // args + php_v8_return_value->this_ptr

    if (php_v8_callback_info->gc_data_count < size) {
        php_v8_callback_info->gc_data = (zval *)safe_erealloc(php_v8_callback_info->gc_data, size, sizeof(zval), 0);
    }

    php_v8_callback_info->gc_data_count = size;

    ZVAL_COPY_VALUE(&php_v8_callback_info->gc_data[0], &php_v8_callback_info->args);
    ZVAL_COPY_VALUE(&php_v8_callback_info->gc_data[1], &php_v8_callback_info->php_v8_return_value->this_ptr);

    *table = php_v8_callback_info->gc_data;
    *n     = php_v8_callback_info->gc_data_count;

    return zend_std_get_properties(object);
}

void php_v8_callback_info_free(zend_object *object) {
    php_v8_callback_info_t *php_v8_callback_info = php_v8_callback_info_fetch_object(object);

    if (php_v8_callback_info->length) {

        for (int i=0; i< php_v8_callback_info->length; i++) {
            if (PHP_V8_ISOLATE_HAS_VALID_HANDLE(php_v8_callback_info)) {
                php_v8_callback_info->arguments[i]->Reset();
            }

            delete php_v8_callback_info->arguments[i];
        }

        efree(php_v8_callback_info->arguments);
    }

    if (php_v8_callback_info->this_obj) {
        if (PHP_V8_ISOLATE_HAS_VALID_HANDLE(php_v8_callback_info)) {
            php_v8_callback_info->this_obj->Reset();
        }

        delete php_v8_callback_info->this_obj;
    }

    if (php_v8_callback_info->holder_obj) {
        if (PHP_V8_ISOLATE_HAS_VALID_HANDLE(php_v8_callback_info)) {
            php_v8_callback_info->holder_obj->Reset();
        }

        delete php_v8_callback_info->holder_obj;
    }

    if (php_v8_callback_info->php_v8_return_value) {
        if (!Z_ISUNDEF(php_v8_callback_info->php_v8_return_value->this_ptr)) {
            zval_ptr_dtor(&php_v8_callback_info->php_v8_return_value->this_ptr);
        }

        php_v8_callback_info->php_v8_return_value = NULL;
    }

    if (!Z_ISUNDEF(php_v8_callback_info->args)) {
        zval_ptr_dtor(&php_v8_callback_info->args);
    }

    if (php_v8_callback_info->gc_data) {
        efree(php_v8_callback_info->gc_data);
    }

    zend_object_std_dtor(&php_v8_callback_info->std);
}

static zend_object * php_v8_callback_info_ctor(zend_class_entry *ce) {

    php_v8_callback_info_t *php_v8_callback_info;

    php_v8_callback_info = (php_v8_callback_info_t *) ecalloc(1, sizeof(php_v8_callback_info_t) + zend_object_properties_size(ce));

    zend_object_std_init(&php_v8_callback_info->std, ce);
    object_properties_init(&php_v8_callback_info->std, ce);

    php_v8_callback_info->this_obj = new v8::Persistent<v8::Object>();
    php_v8_callback_info->holder_obj = new v8::Persistent<v8::Object>();

    php_v8_callback_info->std.handlers = &php_v8_callback_info_object_handlers;

    return &php_v8_callback_info->std;
}


static PHP_METHOD(V8CallbackInfo, GetIsolate) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_CALLBACK_INFO_FETCH_WITH_CHECK(getThis(), php_v8_callback_info);
    PHP_V8_V8_CALLBACK_INFO_CHECK_IN_CONTEXT(php_v8_callback_info);

    RETVAL_ZVAL(&php_v8_callback_info->php_v8_isolate->this_ptr, 1, 0);
}

static PHP_METHOD(V8CallbackInfo, GetContext) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_CALLBACK_INFO_FETCH_WITH_CHECK(getThis(), php_v8_callback_info);
    PHP_V8_V8_CALLBACK_INFO_CHECK_IN_CONTEXT(php_v8_callback_info);

    RETVAL_ZVAL(&php_v8_callback_info->php_v8_context->this_ptr, 1, 0);
}

static PHP_METHOD(V8CallbackInfo, This) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_CALLBACK_INFO_FETCH_WITH_CHECK(getThis(), php_v8_callback_info);
    PHP_V8_V8_CALLBACK_INFO_CHECK_IN_CONTEXT(php_v8_callback_info);

    v8::Isolate *isolate = php_v8_callback_info->php_v8_isolate->isolate;

    v8::Local<v8::Object> local_object = v8::Local<v8::Object>::New(isolate, *php_v8_callback_info->this_obj);

    php_v8_get_or_create_value(return_value, local_object, isolate);
}

static PHP_METHOD(V8CallbackInfo, Holder) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_CALLBACK_INFO_FETCH_WITH_CHECK(getThis(), php_v8_callback_info);
    PHP_V8_V8_CALLBACK_INFO_CHECK_IN_CONTEXT(php_v8_callback_info);

    v8::Isolate *isolate = php_v8_callback_info->php_v8_isolate->isolate;

    v8::Local<v8::Object> local_object = v8::Local<v8::Object>::New(isolate, *php_v8_callback_info->holder_obj);

    php_v8_get_or_create_value(return_value, local_object, isolate);
}

static PHP_METHOD(V8CallbackInfo, GetReturnValue) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_CALLBACK_INFO_FETCH_WITH_CHECK(getThis(), php_v8_callback_info);
    PHP_V8_V8_CALLBACK_INFO_CHECK_IN_CONTEXT(php_v8_callback_info);

    RETVAL_ZVAL(&php_v8_callback_info->php_v8_return_value->this_ptr, 1, 0);
}

static PHP_METHOD(V8CallbackInfo, InContext) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_CALLBACK_INFO_FETCH_WITH_CHECK(getThis(), php_v8_callback_info);

    RETURN_BOOL(PHP_V8_V8_CALLBACK_INFO_IN_CONTEXT(php_v8_callback_info));
}


ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_callback_info_GetIsolate, ZEND_RETURN_VALUE, 0, IS_OBJECT, PHP_V8_NS "\\Isolate", 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_callback_info_GetContext, ZEND_RETURN_VALUE, 0, IS_OBJECT, PHP_V8_NS "\\Context", 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_callback_info_This, ZEND_RETURN_VALUE, 0, IS_OBJECT, PHP_V8_NS "\\ObjectValue", 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_callback_info_Holder, ZEND_RETURN_VALUE, 0, IS_OBJECT, PHP_V8_NS "\\ObjectValue", 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_callback_info_GetReturnValue, ZEND_RETURN_VALUE, 0, IS_OBJECT, PHP_V8_NS "\\ReturnValue", 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_callback_info_InContext, ZEND_RETURN_VALUE, 0, _IS_BOOL, NULL, 0)
ZEND_END_ARG_INFO()


static const zend_function_entry php_v8_callback_info_methods[] = {
        PHP_ME(V8CallbackInfo, This, arginfo_v8_callback_info_This, ZEND_ACC_PUBLIC)
        PHP_ME(V8CallbackInfo, Holder, arginfo_v8_callback_info_Holder, ZEND_ACC_PUBLIC)
        PHP_ME(V8CallbackInfo, GetIsolate, arginfo_v8_callback_info_GetIsolate, ZEND_ACC_PUBLIC)
        PHP_ME(V8CallbackInfo, GetContext, arginfo_v8_callback_info_GetContext, ZEND_ACC_PUBLIC)
        PHP_ME(V8CallbackInfo, GetReturnValue, arginfo_v8_callback_info_GetReturnValue, ZEND_ACC_PUBLIC)
        PHP_ME(V8CallbackInfo, InContext, arginfo_v8_callback_info_InContext, ZEND_ACC_PUBLIC)
        PHP_FE_END
};

PHP_MINIT_FUNCTION (php_v8_callback_info) {
    zend_class_entry ce;

    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS, "CallbackInfo", php_v8_callback_info_methods);
    this_ce = zend_register_internal_class(&ce);
    this_ce->create_object = php_v8_callback_info_ctor;

    memcpy(&php_v8_callback_info_object_handlers, zend_get_std_object_handlers(), sizeof(zend_object_handlers));

    php_v8_callback_info_object_handlers.offset   = XtOffsetOf(php_v8_callback_info_t, std);
    php_v8_callback_info_object_handlers.free_obj = php_v8_callback_info_free;
    php_v8_callback_info_object_handlers.get_gc   = php_v8_callback_info_gc;

    return SUCCESS;
}
