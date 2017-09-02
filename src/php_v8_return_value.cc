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
}

static zend_object *php_v8_return_value_ctor(zend_class_entry *ce) {

    php_v8_return_value_t *php_v8_return_value;

    php_v8_return_value = (php_v8_return_value_t *) ecalloc(1, sizeof(php_v8_return_value_t) + zend_object_properties_size(ce));

    zend_object_std_init(&php_v8_return_value->std, ce);
    object_properties_init(&php_v8_return_value->std, ce);

    php_v8_return_value->accepts = PHP_V8_RETVAL_ACCEPTS_INVALID;

    php_v8_return_value->std.handlers = &php_v8_return_value_object_handlers;

    return &php_v8_return_value->std;
}


php_v8_return_value_t * php_v8_return_value_create_from_return_value(zval *return_value, php_v8_context_t *php_v8_context, int accepts) {
    zval isolate_zv;
    zval context_zv;
    object_init_ex(return_value, this_ce);

    PHP_V8_RETURN_VALUE_FETCH_INTO(return_value, php_v8_return_value);

    // isolate
    ZVAL_OBJ(&isolate_zv, &php_v8_context->php_v8_isolate->std);
    zend_update_property(this_ce, return_value, ZEND_STRL("isolate"), &isolate_zv);
    // context
    ZVAL_OBJ(&context_zv, &php_v8_context->std);
    zend_update_property(this_ce, return_value, ZEND_STRL("context"), &context_zv);

    php_v8_return_value->php_v8_isolate = php_v8_context->php_v8_isolate;
    php_v8_return_value->php_v8_context = php_v8_context;
    php_v8_return_value->accepts = accepts;

    return php_v8_return_value;
}


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


static PHP_METHOD(ReturnValue, get) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_FETCH_RETURN_VALUE_WITH_CHECK(getThis(), php_v8_return_value);
    PHP_V8_RETURN_VALUE_CHECK_IN_CONTEXT(php_v8_return_value);

    v8::Local<v8::Value> local_value = php_v8_return_value_get(php_v8_return_value);

    php_v8_get_or_create_value(return_value, local_value, php_v8_return_value->php_v8_isolate);
}


static PHP_METHOD(ReturnValue, set) {
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
static PHP_METHOD(ReturnValue, setNull) {
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

static PHP_METHOD(ReturnValue, setUndefined) {
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

static PHP_METHOD(ReturnValue, setEmptyString) {
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
static PHP_METHOD(ReturnValue, setBool) {
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

static PHP_METHOD(ReturnValue, setInteger) {
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


static PHP_METHOD(ReturnValue, setFloat) {
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
static PHP_METHOD(ReturnValue, getIsolate) {
    zval rv;
    zval *tmp;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    tmp = zend_read_property(this_ce, getThis(), ZEND_STRL("isolate"), 0, &rv);
    ZVAL_COPY(return_value, tmp);
}

// Convenience getter for Context
static PHP_METHOD(ReturnValue, getContext) {
    zval rv;
    zval *tmp;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    tmp = zend_read_property(this_ce, getThis(), ZEND_STRL("context"), 0, &rv);
    ZVAL_COPY(return_value, tmp);
}

static PHP_METHOD(ReturnValue, inContext) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_FETCH_RETURN_VALUE_WITH_CHECK(getThis(), php_v8_return_value);

    RETURN_BOOL(PHP_V8_RETURN_VALUE_IN_CONTEXT(php_v8_return_value));
}

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_get, ZEND_RETURN_VALUE, 0, V8\\Value, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_VOID_INFO_EX(arginfo_set, 1)
                ZEND_ARG_OBJ_INFO(0, value, V8\\Value, 0)
ZEND_END_ARG_INFO()


PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_VOID_INFO_EX(arginfo_setNull, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_VOID_INFO_EX(arginfo_setUndefined, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_VOID_INFO_EX(arginfo_setEmptyString, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_VOID_INFO_EX(arginfo_setBool, 1)
                ZEND_ARG_TYPE_INFO(0, value, _IS_BOOL, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_VOID_INFO_EX(arginfo_setInteger, 1)
                ZEND_ARG_TYPE_INFO(0, i, IS_LONG, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_VOID_INFO_EX(arginfo_setFloat, 1)
                ZEND_ARG_TYPE_INFO(0, i, IS_DOUBLE, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_getIsolate, ZEND_RETURN_VALUE, 0, V8\\Isolate, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_getContext, ZEND_RETURN_VALUE, 0, V8\\Context, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_inContext, ZEND_RETURN_VALUE, 0, _IS_BOOL, 0)
ZEND_END_ARG_INFO()

static const zend_function_entry php_v8_return_value_methods[] = {
        PHP_V8_ME(ReturnValue, get,            ZEND_ACC_PUBLIC)
        PHP_V8_ME(ReturnValue, set,            ZEND_ACC_PUBLIC)
        PHP_V8_ME(ReturnValue, setNull,        ZEND_ACC_PUBLIC)
        PHP_V8_ME(ReturnValue, setUndefined,   ZEND_ACC_PUBLIC)
        PHP_V8_ME(ReturnValue, setEmptyString, ZEND_ACC_PUBLIC)
        PHP_V8_ME(ReturnValue, setBool,        ZEND_ACC_PUBLIC)
        PHP_V8_ME(ReturnValue, setInteger,     ZEND_ACC_PUBLIC)
        PHP_V8_ME(ReturnValue, setFloat,       ZEND_ACC_PUBLIC)
        PHP_V8_ME(ReturnValue, getIsolate,     ZEND_ACC_PUBLIC)
        PHP_V8_ME(ReturnValue, getContext,     ZEND_ACC_PUBLIC)
        PHP_V8_ME(ReturnValue, inContext,      ZEND_ACC_PUBLIC)

        PHP_FE_END
};


PHP_MINIT_FUNCTION (php_v8_return_value) {
    zend_class_entry ce;
    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS, "ReturnValue", php_v8_return_value_methods);
    this_ce = zend_register_internal_class(&ce);
    this_ce->create_object = php_v8_return_value_ctor;

    zend_declare_property_null(this_ce, ZEND_STRL("isolate"), ZEND_ACC_PRIVATE);
    zend_declare_property_null(this_ce, ZEND_STRL("context"), ZEND_ACC_PRIVATE);

    memcpy(&php_v8_return_value_object_handlers, zend_get_std_object_handlers(), sizeof(zend_object_handlers));

    php_v8_return_value_object_handlers.offset    = XtOffsetOf(php_v8_return_value_t, std);
    php_v8_return_value_object_handlers.free_obj  = php_v8_return_value_free;
    php_v8_return_value_object_handlers.clone_obj = NULL;

    return SUCCESS;
}
