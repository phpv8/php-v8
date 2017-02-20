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

#include "php_v8_return_value.h"
#include "php_v8_integer.h"
#include "php_v8_null.h"
#include "php_v8_value.h"
#include "php_v8.h"

zend_class_entry *php_v8_return_value_class_entry;
#define this_ce php_v8_return_value_class_entry

static zend_object_handlers php_v8_return_value_object_handlers;


static void php_v8_return_value_free(zend_object *object) {

    php_v8_return_value_t *php_v8_return_value = php_v8_return_value_fetch_object(object);

    zend_object_std_dtor(&php_v8_return_value->std);

    if (!Z_ISUNDEF(php_v8_return_value->this_ptr)) {
        zval_ptr_dtor(&php_v8_return_value->this_ptr);
    }
}

static zend_object * php_v8_return_value_ctor(zend_class_entry *ce) {

    php_v8_return_value_t *php_v8_return_value;

    php_v8_return_value = (php_v8_return_value_t *) ecalloc(1, sizeof(php_v8_return_value_t) + zend_object_properties_size(ce));

    zend_object_std_init(&php_v8_return_value->std, ce);
    object_properties_init(&php_v8_return_value->std, ce);

    php_v8_return_value->accepts = PHP_V8_RETVAL_ACCEPTS_INVALID;

    php_v8_return_value->std.handlers = &php_v8_return_value_object_handlers;

    return &php_v8_return_value->std;
}


php_v8_return_value_t *php_v8_return_value_create_from_return_value(zval *this_ptr, php_v8_isolate_t *php_v8_isolate, php_v8_context_t *php_v8_context, int accepts) {
    object_init_ex(this_ptr, this_ce);

    PHP_V8_RETURN_VALUE_FETCH_INTO(this_ptr, php_v8_return_value);

    php_v8_return_value->php_v8_isolate = php_v8_isolate;
    php_v8_return_value->php_v8_context = php_v8_context;
    php_v8_return_value->accepts = accepts;

    ZVAL_COPY_VALUE(&php_v8_return_value->this_ptr, this_ptr);

    return php_v8_return_value;
}

void php_v8_return_value_mark_expired(php_v8_return_value_t *php_v8_return_value) {
    php_v8_return_value->accepts = PHP_V8_RETVAL_ACCEPTS_INVALID;
}

//static inline void php_v8_return_value(php_v8_return_value_t *php_v8_return_value) {
//    assert(PHP_V8_RETVAL_ACCEPTS_INVALID != php_v8_return_value->accepts);
//
//    switch (php_v8_return_value->accepts) {
//        case PHP_V8_RETVAL_ACCEPTS_VOID:
//            php_v8_return_value->rv_void;
//            break;
//        case PHP_V8_RETVAL_ACCEPTS_ANY:
//            php_v8_return_value->rv_any;
//            break;
//        case PHP_V8_RETVAL_ACCEPTS_INTEGER:
//            php_v8_return_value->rv_integer;
//            break;
//        case PHP_V8_RETVAL_ACCEPTS_BOOLEAN:
//            php_v8_return_value->rv_boolean;
//            break;
//        case PHP_V8_RETVAL_ACCEPTS_ARRAY:
//            php_v8_return_value->rv_array;
//            break;
//        default:
//            assert(false);
//            break;
//    }
//}

static inline v8::Local<v8::Value> php_v8_return_value_get(php_v8_return_value_t *php_v8_return_value) {
    assert(PHP_V8_RETVAL_ACCEPTS_INVALID != php_v8_return_value->accepts);

    switch (php_v8_return_value->accepts) {
        case PHP_V8_RETVAL_ACCEPTS_VOID:
            return php_v8_return_value->rv_void->Get();
        case PHP_V8_RETVAL_ACCEPTS_ANY:
            return php_v8_return_value->rv_any->Get();
        case PHP_V8_RETVAL_ACCEPTS_INTEGER:
            return php_v8_return_value->rv_integer->Get();
        case PHP_V8_RETVAL_ACCEPTS_BOOLEAN:
            return php_v8_return_value->rv_boolean->Get();
        case PHP_V8_RETVAL_ACCEPTS_ARRAY:
            return php_v8_return_value->rv_array->Get();
        default:
            assert(false);
    }

    assert(false);
    return v8::Undefined(php_v8_return_value->php_v8_isolate->isolate);
}


static PHP_METHOD(V8ReturnValue, Get) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_FETCH_RETURN_VALUE_WITH_CHECK(getThis(), php_v8_return_value);
    PHP_V8_RETURN_VALUE_CHECK_IN_CONTEXT(php_v8_return_value);

    v8::Local<v8::Value> local_value = php_v8_return_value_get(php_v8_return_value);

    php_v8_get_or_create_value(return_value, local_value, php_v8_return_value->php_v8_isolate);
}


static PHP_METHOD(V8ReturnValue, Set) {
    zval *php_v8_value_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o", &php_v8_value_zv) == FAILURE) {
        return;
    }

    PHP_V8_FETCH_RETURN_VALUE_WITH_CHECK(getThis(), php_v8_return_value);
    PHP_V8_RETURN_VALUE_CHECK_IN_CONTEXT(php_v8_return_value);

    PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_value_zv, php_v8_value);
    PHP_V8_DATA_ISOLATES_CHECK(php_v8_return_value, php_v8_value);

    v8::Local<v8::Value> local_value = php_v8_value_get_local(php_v8_value);

    if (PHP_V8_RETVAL_ACCEPTS_VOID == php_v8_return_value->accepts) {
        PHP_V8_THROW_EXCEPTION("ReturnValue doesn't accept any value");
        return;
    }

    if (PHP_V8_RETVAL_ACCEPTS_ANY == php_v8_return_value->accepts) {
        php_v8_return_value->rv_any->Set(local_value);
        return;
    }

    if (PHP_V8_RETVAL_ACCEPTS_INTEGER == php_v8_return_value->accepts) {
        if (local_value->IsInt32() || local_value->IsUint32()) {
            php_v8_return_value->rv_integer->Set(local_value.As<v8::Integer>());
            return;
        }
        PHP_V8_THROW_EXCEPTION("ReturnValue accepts only integers");
    }

    if (PHP_V8_RETVAL_ACCEPTS_BOOLEAN == php_v8_return_value->accepts && !local_value->IsBoolean()) {
        if (local_value->IsBoolean()) {
            php_v8_return_value->rv_boolean->Set(local_value.As<v8::Boolean>());
            return;
        }
        PHP_V8_THROW_EXCEPTION("ReturnValue accepts only boolean");
    }

    if (PHP_V8_RETVAL_ACCEPTS_ARRAY == php_v8_return_value->accepts && local_value->IsArray()) {
        if (local_value->IsArray()) {
            php_v8_return_value->rv_array->Set(local_value.As<v8::Array>());
            return;
        }
        PHP_V8_THROW_EXCEPTION("ReturnValue accepts only instances of \\V8\\ArrayObject class");
    }


    // should never go here
    PHP_V8_THROW_EXCEPTION("Invalid ReturnValue to set");
}

// Fast JS primitive setters
static PHP_METHOD(V8ReturnValue, SetNull) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_FETCH_RETURN_VALUE_WITH_CHECK(getThis(), php_v8_return_value);
    PHP_V8_RETURN_VALUE_CHECK_IN_CONTEXT(php_v8_return_value);

    if (PHP_V8_RETVAL_ACCEPTS_VOID == php_v8_return_value->accepts) {
        PHP_V8_THROW_EXCEPTION("ReturnValue doesn't accept any value");
        return;
    }

    if (PHP_V8_RETVAL_ACCEPTS_ANY == php_v8_return_value->accepts) {
        php_v8_return_value->rv_any->SetNull();
        return;
    }

    PHP_V8_THROW_EXCEPTION("Invalid ReturnValue to set");
}

static PHP_METHOD(V8ReturnValue, SetUndefined) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_FETCH_RETURN_VALUE_WITH_CHECK(getThis(), php_v8_return_value);
    PHP_V8_RETURN_VALUE_CHECK_IN_CONTEXT(php_v8_return_value);

    if (PHP_V8_RETVAL_ACCEPTS_VOID == php_v8_return_value->accepts) {
        PHP_V8_THROW_EXCEPTION("ReturnValue doesn't accept any value");
        return;
    }

    if (PHP_V8_RETVAL_ACCEPTS_ANY == php_v8_return_value->accepts) {
        php_v8_return_value->rv_any->SetUndefined();
        return;
    }

    PHP_V8_THROW_EXCEPTION("Invalid ReturnValue to set");
}

static PHP_METHOD(V8ReturnValue, SetEmptyString) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_FETCH_RETURN_VALUE_WITH_CHECK(getThis(), php_v8_return_value);
    PHP_V8_RETURN_VALUE_CHECK_IN_CONTEXT(php_v8_return_value);

    if (PHP_V8_RETVAL_ACCEPTS_VOID == php_v8_return_value->accepts) {
        PHP_V8_THROW_EXCEPTION("ReturnValue doesn't accept any value");
        return;
    }

    if (PHP_V8_RETVAL_ACCEPTS_ANY == php_v8_return_value->accepts) {
        php_v8_return_value->rv_any->SetEmptyString();
        return;
    }

    PHP_V8_THROW_EXCEPTION("Invalid ReturnValue to set");

}

// Non-standard primitive setters
static PHP_METHOD(V8ReturnValue, SetBool) {
    zend_bool value;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "b", &value) == FAILURE) {
        return;
    }

    PHP_V8_FETCH_RETURN_VALUE_WITH_CHECK(getThis(), php_v8_return_value);
    PHP_V8_RETURN_VALUE_CHECK_IN_CONTEXT(php_v8_return_value);

    if (PHP_V8_RETVAL_ACCEPTS_VOID == php_v8_return_value->accepts) {
        PHP_V8_THROW_EXCEPTION("ReturnValue doesn't accept any value");
        return;
    }

    if (PHP_V8_RETVAL_ACCEPTS_ANY == php_v8_return_value->accepts) {
        php_v8_return_value->rv_any->Set(static_cast<bool>(value));
        return;
    }

    if (PHP_V8_RETVAL_ACCEPTS_BOOLEAN == php_v8_return_value->accepts) {
        php_v8_return_value->rv_boolean->Set(static_cast<bool>(value));
        return;
    }

    PHP_V8_THROW_EXCEPTION("Invalid ReturnValue to set");
}

static PHP_METHOD(V8ReturnValue, SetInteger) {
    zend_long value;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "l", &value) == FAILURE) {
        return;
    }

    PHP_V8_CHECK_INTEGER_RANGE(value, "Integer value to set is out of range");

    PHP_V8_FETCH_RETURN_VALUE_WITH_CHECK(getThis(), php_v8_return_value);
    PHP_V8_RETURN_VALUE_CHECK_IN_CONTEXT(php_v8_return_value);

    if (PHP_V8_RETVAL_ACCEPTS_VOID == php_v8_return_value->accepts) {
        PHP_V8_THROW_EXCEPTION("ReturnValue doesn't accept any value");
        return;
    }

    if (PHP_V8_RETVAL_ACCEPTS_ANY == php_v8_return_value->accepts) {
        if (value > INT32_MAX) {
            php_v8_return_value->rv_any->Set(static_cast<uint32_t>(value));
        } else {
            php_v8_return_value->rv_any->Set(static_cast<int32_t>(value));
        }
        return;
    }

    if (PHP_V8_RETVAL_ACCEPTS_INTEGER == php_v8_return_value->accepts) {
        if (value > INT32_MAX) {
            php_v8_return_value->rv_integer->Set(static_cast<uint32_t>(value));
        } else {
            php_v8_return_value->rv_integer->Set(static_cast<int32_t>(value));
        }
        return;
    }

    PHP_V8_THROW_EXCEPTION("Invalid ReturnValue to set");
}


static PHP_METHOD(V8ReturnValue, SetFloat) {
    double value;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "d", &value) == FAILURE) {
        return;
    }

    PHP_V8_FETCH_RETURN_VALUE_WITH_CHECK(getThis(), php_v8_return_value);
    PHP_V8_RETURN_VALUE_CHECK_IN_CONTEXT(php_v8_return_value);

    if (PHP_V8_RETVAL_ACCEPTS_VOID == php_v8_return_value->accepts) {
        PHP_V8_THROW_EXCEPTION("ReturnValue doesn't accept any value");
        return;
    }

    if (PHP_V8_RETVAL_ACCEPTS_ANY == php_v8_return_value->accepts) {
        php_v8_return_value->rv_any->Set(value);
        return;
    }

    PHP_V8_THROW_EXCEPTION("Invalid ReturnValue to set");
}


// Convenience getter for Isolate
static PHP_METHOD(V8ReturnValue, GetIsolate) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_FETCH_RETURN_VALUE_WITH_CHECK(getThis(), php_v8_return_value);
    PHP_V8_RETURN_VALUE_CHECK_IN_CONTEXT(php_v8_return_value);

    RETVAL_ZVAL(&php_v8_return_value->php_v8_isolate->this_ptr, 1, 0);
}

// Convenience getter for Context
static PHP_METHOD(V8ReturnValue, GetContext) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_FETCH_RETURN_VALUE_WITH_CHECK(getThis(), php_v8_return_value);
    PHP_V8_RETURN_VALUE_CHECK_IN_CONTEXT(php_v8_return_value);

    RETVAL_ZVAL(&php_v8_return_value->php_v8_context->this_ptr, 1, 0);
}

static PHP_METHOD(V8ReturnValue, InContext) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_FETCH_RETURN_VALUE_WITH_CHECK(getThis(), php_v8_return_value);

    RETURN_BOOL(PHP_V8_RETURN_VALUE_IN_CONTEXT(php_v8_return_value));
}


ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_return_value_Set, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 1)
                ZEND_ARG_OBJ_INFO(0, value, V8\\Value, 0)
ZEND_END_ARG_INFO()


ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_return_value_SetNull, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_return_value_SetUndefined, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_return_value_SetEmptyString, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_return_value_SetBool, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 1)
                ZEND_ARG_TYPE_INFO(0, value, _IS_BOOL, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_return_value_SetInteger, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 1)
                ZEND_ARG_TYPE_INFO(0, i, IS_LONG, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_return_value_SetFloat, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 1)
                ZEND_ARG_TYPE_INFO(0, i, IS_DOUBLE, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_v8_return_value_GetIsolate, ZEND_RETURN_VALUE, 0, V8\\Isolate, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_v8_return_value_GetContext, ZEND_RETURN_VALUE, 0, V8\\Context, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_return_value_InContext, ZEND_RETURN_VALUE, 0, _IS_BOOL, 0)
ZEND_END_ARG_INFO()

static const zend_function_entry php_v8_return_value_methods[] = {
        PHP_ME(V8ReturnValue, Get, arginfo_v8_return_value_Set, ZEND_ACC_PUBLIC)
        PHP_ME(V8ReturnValue, Set, arginfo_v8_return_value_Set, ZEND_ACC_PUBLIC)
        PHP_ME(V8ReturnValue, SetNull, arginfo_v8_return_value_SetNull, ZEND_ACC_PUBLIC)
        PHP_ME(V8ReturnValue, SetUndefined, arginfo_v8_return_value_SetUndefined, ZEND_ACC_PUBLIC)
        PHP_ME(V8ReturnValue, SetEmptyString, arginfo_v8_return_value_SetEmptyString, ZEND_ACC_PUBLIC)
        PHP_ME(V8ReturnValue, SetBool, arginfo_v8_return_value_SetBool, ZEND_ACC_PUBLIC)
        PHP_ME(V8ReturnValue, SetInteger, arginfo_v8_return_value_SetInteger, ZEND_ACC_PUBLIC)
        PHP_ME(V8ReturnValue, SetFloat, arginfo_v8_return_value_SetFloat, ZEND_ACC_PUBLIC)

        PHP_ME(V8ReturnValue, GetIsolate, arginfo_v8_return_value_GetIsolate, ZEND_ACC_PUBLIC)
        PHP_ME(V8ReturnValue, GetContext, arginfo_v8_return_value_GetContext, ZEND_ACC_PUBLIC)
        PHP_ME(V8ReturnValue, InContext, arginfo_v8_return_value_InContext, ZEND_ACC_PUBLIC)

        PHP_FE_END
};


PHP_MINIT_FUNCTION (php_v8_return_value) {
    zend_class_entry ce;
    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS, "ReturnValue", php_v8_return_value_methods);
    this_ce = zend_register_internal_class(&ce);
    this_ce->create_object = php_v8_return_value_ctor;

    memcpy(&php_v8_return_value_object_handlers, zend_get_std_object_handlers(), sizeof(zend_object_handlers));

    php_v8_return_value_object_handlers.offset   = XtOffsetOf(php_v8_return_value_t, std);
    php_v8_return_value_object_handlers.free_obj = php_v8_return_value_free;

    return SUCCESS;
}
