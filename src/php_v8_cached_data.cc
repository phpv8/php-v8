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

#include "php_v8_cached_data.h"
#include "php_v8_string.h"
#include "php_v8.h"


zend_class_entry * php_v8_cached_data_class_entry;
#define this_ce php_v8_cached_data_class_entry

static zend_object_handlers php_v8_cached_data_object_handlers;


php_v8_cached_data_t * php_v8_create_cached_data(zval *return_value, const v8::ScriptCompiler::CachedData *cached_data) {

    object_init_ex(return_value, this_ce);
    PHP_V8_FETCH_CACHED_DATA_INTO(return_value, php_v8_cached_data);

    int length = cached_data->length;
    uint8_t* data = new uint8_t[length];
    memcpy(data, cached_data->data, static_cast<size_t>(length));

    php_v8_cached_data->cached_data = new v8::ScriptCompiler::CachedData(data, length, v8::ScriptCompiler::CachedData::BufferPolicy::BufferOwned);

    return php_v8_cached_data;
}

static void php_v8_cached_data_free(zend_object *object)
{
    php_v8_cached_data_t *php_v8_cached_data = php_v8_cached_data_fetch_object(object);

    if (php_v8_cached_data->cached_data) {
        delete php_v8_cached_data->cached_data;
    }

    zend_object_std_dtor(&php_v8_cached_data->std);
}

static zend_object * php_v8_cached_data_ctor(zend_class_entry *ce)
{
    php_v8_cached_data_t *php_v8_cached_data;

    php_v8_cached_data = (php_v8_cached_data_t *) ecalloc(1, sizeof(php_v8_cached_data_t) + zend_object_properties_size(ce));

    zend_object_std_init(&php_v8_cached_data->std, ce);
    object_properties_init(&php_v8_cached_data->std, ce);

    php_v8_cached_data->std.handlers = &php_v8_cached_data_object_handlers;

    return &php_v8_cached_data->std;
}

static PHP_METHOD(V8CachedData, __construct)
{
    zend_string *string = NULL;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "S", &string) == FAILURE) {
        return;
    }

    PHP_V8_CHECK_CACHE_DATA_STRING_RANGE(string, "CachedData data string is too long");

    PHP_V8_FETCH_CACHED_DATA_INTO(getThis(), php_v8_cached_data);

    int length = static_cast<int>(MAYBE_ZSTR_LEN(string));
    uint8_t* data = new uint8_t[length];
    memcpy(data, MAYBE_ZSTR_VAL(string), static_cast<size_t>(length));

    php_v8_cached_data->cached_data = new v8::ScriptCompiler::CachedData(data, length, v8::ScriptCompiler::CachedData::BufferPolicy::BufferOwned);
}

static PHP_METHOD(V8CachedData, GetData)
{
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_FETCH_CACHED_DATA_WITH_CHECK(getThis(), php_v8_cached_data);

    RETVAL_STRINGL(reinterpret_cast<const char*>(php_v8_cached_data->cached_data->data), php_v8_cached_data->cached_data->length);
}

static PHP_METHOD(V8CachedData, IsRejected)
{
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_FETCH_CACHED_DATA_WITH_CHECK(getThis(), php_v8_cached_data);

    RETVAL_BOOL(php_v8_cached_data->cached_data->rejected);
}


ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_cached_data___construct, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 1)
                ZEND_ARG_TYPE_INFO(0, data, IS_STRING, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_cached_data_GetData, ZEND_RETURN_VALUE, 0, IS_STRING, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_cached_data_IsRejected, ZEND_RETURN_VALUE, 0, _IS_BOOL, 0)
ZEND_END_ARG_INFO()


static const zend_function_entry php_v8_cached_data_methods[] = {
    PHP_ME(V8CachedData, __construct,   arginfo_v8_cached_data___construct, ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)
    PHP_ME(V8CachedData, GetData,       arginfo_v8_cached_data_GetData,     ZEND_ACC_PUBLIC)
    PHP_ME(V8CachedData, IsRejected,    arginfo_v8_cached_data_IsRejected,  ZEND_ACC_PUBLIC)

    PHP_FE_END
};


PHP_MINIT_FUNCTION(php_v8_cached_data)
{
    zend_class_entry ce;

    INIT_NS_CLASS_ENTRY(ce, "V8\\ScriptCompiler", "CachedData", php_v8_cached_data_methods);
    this_ce = zend_register_internal_class(&ce);
    this_ce->create_object = php_v8_cached_data_ctor;

    memcpy(&php_v8_cached_data_object_handlers, zend_get_std_object_handlers(), sizeof(zend_object_handlers));

    php_v8_cached_data_object_handlers.offset = XtOffsetOf(php_v8_cached_data_t, std);
    php_v8_cached_data_object_handlers.free_obj = php_v8_cached_data_free;

    return SUCCESS;
}
