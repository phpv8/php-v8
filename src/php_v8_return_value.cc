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

#include "php_v8_return_value.h"
#include "php_v8_integer.h"
#include "php_v8_value.h"
#include "php_v8.h"

zend_class_entry *php_v8_return_value_class_entry;
#define this_ce php_v8_return_value_class_entry

static zend_object_handlers php_v8_return_value_object_handlers;

php_v8_return_value_t * php_v8_return_value_fetch_object(zend_object *obj) {
    return (php_v8_return_value_t *)((char *)obj - XtOffsetOf(php_v8_return_value_t, std));
}

static HashTable * php_v8_return_value_gc(zval *object, zval **table, int *n) {
    PHP_V8_RETURN_VALUE_FETCH_INTO(object, php_v8_return_value);

    int size = 0;

    if (php_v8_return_value->type & PHP_V8_RETVAL_ZVAL && !Z_ISUNDEF(php_v8_return_value->value.php_v8_value_zv)) {
        size ++;
    }

    if (php_v8_return_value->gc_data_count < size) {
        php_v8_return_value->gc_data = (zval *)safe_erealloc(php_v8_return_value->gc_data, size, sizeof(zval), 0);
    }

    php_v8_return_value->gc_data_count = size;

    if (size) {
        ZVAL_COPY_VALUE(&php_v8_return_value->gc_data[0], &php_v8_return_value->value.php_v8_value_zv);
    }

    *table = php_v8_return_value->gc_data;
    *n     = php_v8_return_value->gc_data_count;

    return zend_std_get_properties(object);
}

static void php_v8_return_value_free(zend_object *object) {

    php_v8_return_value_t *php_v8_return_value = php_v8_return_value_fetch_object(object);

    zend_object_std_dtor(&php_v8_return_value->std);

    if (php_v8_return_value->type & PHP_V8_RETVAL_ZVAL && !Z_ISUNDEF(php_v8_return_value->value.php_v8_value_zv)) {
        zval_ptr_dtor(&php_v8_return_value->value.php_v8_value_zv);
    }

    if (php_v8_return_value->gc_data) {
        efree(php_v8_return_value->gc_data);
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


void php_v8_return_value_create_from_return_value(zval *this_ptr, php_v8_isolate_t *php_v8_isolate, php_v8_context_t *php_v8_context, int accepts) {
    object_init_ex(this_ptr, this_ce);

    PHP_V8_RETURN_VALUE_FETCH_INTO(this_ptr, php_v8_return_value);

    php_v8_return_value->php_v8_isolate = php_v8_isolate;
    php_v8_return_value->php_v8_context = php_v8_context;
    php_v8_return_value->accepts = accepts;
}

void php_v8_return_value_mark_expired(zval *this_ptr) {
    PHP_V8_RETURN_VALUE_FETCH_INTO(this_ptr, php_v8_return_value);

    php_v8_return_value->accepts = PHP_V8_RETVAL_ACCEPTS_INVALID;
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

    v8::Local<v8::Value> local_value = php_v8_value_get_value_local(php_v8_return_value->php_v8_isolate->isolate, php_v8_value);

    if (local_value->IsUndefined()) {
        PHP_V8_CHECK_ACCEPTS(php_v8_return_value, PHP_V8_RETVAL_UNDEFINED);
        return;
    }

    if (local_value->IsNull()) {
        PHP_V8_CHECK_ACCEPTS(php_v8_return_value, PHP_V8_RETVAL_NULL);
        return;
    }

    if (local_value->IsBoolean()) {
        PHP_V8_CHECK_ACCEPTS(php_v8_return_value, PHP_V8_RETVAL_BOOL);
        php_v8_return_value->value.set_bool = static_cast<bool>(local_value->IsTrue());
        return;
    }

    if (local_value->IsInt32()) {
        PHP_V8_CHECK_ACCEPTS(php_v8_return_value, PHP_V8_RETVAL_INT32);
        php_v8_return_value->value.set_int32 = local_value.As<v8::Int32>()->Value();
        return;
    }

    if (local_value->IsUint32()) {
        PHP_V8_CHECK_ACCEPTS(php_v8_return_value, PHP_V8_RETVAL_UINT32);
        php_v8_return_value->value.set_uint32 = local_value.As<v8::Uint32>()->Value();
        return;
    }

    if (local_value->IsNumber()) {
        PHP_V8_CHECK_ACCEPTS(php_v8_return_value, PHP_V8_RETVAL_DOUBLE);
        php_v8_return_value->value.set_double = local_value.As<v8::Number>()->Value();
        return;
    }

    if (local_value->IsArray()) {
        PHP_V8_CHECK_ACCEPTS(php_v8_return_value, PHP_V8_RETVAL_ACCEPTS_ARRAY);
    } else {
        PHP_V8_CHECK_ACCEPTS(php_v8_return_value, PHP_V8_RETVAL_V8_VALUE);
    }

    php_v8_return_value->type = PHP_V8_RETVAL_V8_VALUE;
    ZVAL_COPY(&php_v8_return_value->value.php_v8_value_zv, php_v8_value_zv);
}

// Fast JS primitive setters
static PHP_METHOD(V8ReturnValue, SetNull) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_FETCH_RETURN_VALUE_WITH_CHECK(getThis(), php_v8_return_value);
    PHP_V8_RETURN_VALUE_CHECK_IN_CONTEXT(php_v8_return_value);

    PHP_V8_CHECK_ACCEPTS(php_v8_return_value, PHP_V8_RETVAL_NULL);
}

static PHP_METHOD(V8ReturnValue, SetUndefined) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_FETCH_RETURN_VALUE_WITH_CHECK(getThis(), php_v8_return_value);
    PHP_V8_RETURN_VALUE_CHECK_IN_CONTEXT(php_v8_return_value);

    PHP_V8_CHECK_ACCEPTS(php_v8_return_value, PHP_V8_RETVAL_UNDEFINED);
}

static PHP_METHOD(V8ReturnValue, SetEmptyString) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_FETCH_RETURN_VALUE_WITH_CHECK(getThis(), php_v8_return_value);
    PHP_V8_RETURN_VALUE_CHECK_IN_CONTEXT(php_v8_return_value);

    PHP_V8_CHECK_ACCEPTS(php_v8_return_value, PHP_V8_RETVAL_EMPTY_STRING);
}

// Non-standard primitive setters
static PHP_METHOD(V8ReturnValue, SetBool) {
    zend_bool value;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "b", &value) == FAILURE) {
        return;
    }

    PHP_V8_FETCH_RETURN_VALUE_WITH_CHECK(getThis(), php_v8_return_value);
    PHP_V8_RETURN_VALUE_CHECK_IN_CONTEXT(php_v8_return_value);

    PHP_V8_CHECK_ACCEPTS(php_v8_return_value, PHP_V8_RETVAL_BOOL);
    php_v8_return_value->value.set_bool = static_cast<bool>(value);
}

static PHP_METHOD(V8ReturnValue, SetInteger) {
    zend_long value;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "l", &value) == FAILURE) {
        return;
    }

    PHP_V8_CHECK_INTEGER_RANGE(value, "Integer value to set is out of range");

    PHP_V8_FETCH_RETURN_VALUE_WITH_CHECK(getThis(), php_v8_return_value);
    PHP_V8_RETURN_VALUE_CHECK_IN_CONTEXT(php_v8_return_value);

    if (value > INT32_MAX) {
        PHP_V8_CHECK_ACCEPTS(php_v8_return_value, PHP_V8_RETVAL_UINT32);
        php_v8_return_value->value.set_uint32 = static_cast<uint32_t>(value);
    }

    PHP_V8_CHECK_ACCEPTS(php_v8_return_value, PHP_V8_RETVAL_INT32);
    php_v8_return_value->value.set_int32 = static_cast<int32_t>(value);
}

static PHP_METHOD(V8ReturnValue, SetFloat) {
    double value;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "d", &value) == FAILURE) {
        return;
    }

    PHP_V8_FETCH_RETURN_VALUE_WITH_CHECK(getThis(), php_v8_return_value);
    PHP_V8_RETURN_VALUE_CHECK_IN_CONTEXT(php_v8_return_value);

    PHP_V8_CHECK_ACCEPTS(php_v8_return_value, PHP_V8_RETVAL_DOUBLE);
    php_v8_return_value->value.set_double = value;
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

ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_return_value_Set, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 1)
                ZEND_ARG_OBJ_INFO(0, value, v8\\Value, 0)
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

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_return_value_GetIsolate, ZEND_RETURN_VALUE, 0, IS_OBJECT, "v8\\Isolate", 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_return_value_GetContext, ZEND_RETURN_VALUE, 0, IS_OBJECT, "v8\\Context", 0)
ZEND_END_ARG_INFO()

static const zend_function_entry php_v8_return_value_methods[] = {
        PHP_ME(V8ReturnValue, Set, arginfo_v8_return_value_Set, ZEND_ACC_PUBLIC)
        PHP_ME(V8ReturnValue, SetNull, arginfo_v8_return_value_SetNull, ZEND_ACC_PUBLIC)
        PHP_ME(V8ReturnValue, SetUndefined, arginfo_v8_return_value_SetUndefined, ZEND_ACC_PUBLIC)
        PHP_ME(V8ReturnValue, SetEmptyString, arginfo_v8_return_value_SetEmptyString, ZEND_ACC_PUBLIC)
        PHP_ME(V8ReturnValue, SetBool, arginfo_v8_return_value_SetBool, ZEND_ACC_PUBLIC)
        PHP_ME(V8ReturnValue, SetInteger, arginfo_v8_return_value_SetInteger, ZEND_ACC_PUBLIC)
        PHP_ME(V8ReturnValue, SetFloat, arginfo_v8_return_value_SetFloat, ZEND_ACC_PUBLIC)

        PHP_ME(V8ReturnValue, GetIsolate, arginfo_v8_return_value_GetIsolate, ZEND_ACC_PUBLIC)
        PHP_ME(V8ReturnValue, GetContext, arginfo_v8_return_value_GetContext, ZEND_ACC_PUBLIC)

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
    php_v8_return_value_object_handlers.get_gc   = php_v8_return_value_gc;

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


