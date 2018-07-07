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

#include "php_v8_json.h"
#include "php_v8_value.h"
#include "php_v8_context.h"
#include "php_v8.h"

zend_class_entry *php_v8_json_class_entry;
#define this_ce php_v8_json_class_entry


static PHP_METHOD(JSON, parse) {
    zval *php_v8_context_zv;
    zval *php_v8_string_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "oo", &php_v8_context_zv, &php_v8_string_zv) == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_string_zv, php_v8_string);
    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_context, php_v8_string);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    v8::Local<v8::String> local_string = php_v8_value_get_local_as<v8::String>(php_v8_string);

    PHP_V8_TRY_CATCH(isolate);
    PHP_V8_INIT_ISOLATE_LIMITS_ON_CONTEXT(php_v8_context);

    v8::MaybeLocal<v8::Value> maybe_local_value = v8::JSON::Parse(context, local_string);

    PHP_V8_MAYBE_CATCH(php_v8_context, try_catch);
    PHP_V8_THROW_EXCEPTION_WHEN_EMPTY(maybe_local_value, "Failed to parse");

    php_v8_get_or_create_value(return_value, maybe_local_value.ToLocalChecked(), php_v8_context->php_v8_isolate);
}

static PHP_METHOD(JSON, stringify) {
    zval *php_v8_context_zv;
    zval *php_v8_value_zv = NULL;
    zval *php_v8_gap_zv = NULL;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "oo|o", &php_v8_context_zv, &php_v8_value_zv, &php_v8_gap_zv) == FAILURE) {
        return;
    }

    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);
    PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_value_zv, php_v8_value);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_context, php_v8_value);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    v8::Local<v8::String> local_gap;

    if (NULL != php_v8_gap_zv) {
        PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_gap_zv, php_v8_gap);
        PHP_V8_DATA_ISOLATES_CHECK(php_v8_context, php_v8_gap);
        local_gap = php_v8_value_get_local_as<v8::String>(php_v8_gap);
    }

    v8::Local<v8::Value> local_value = php_v8_value_get_local_as<v8::Value>(php_v8_value);

    PHP_V8_TRY_CATCH(isolate);
    PHP_V8_INIT_ISOLATE_LIMITS_ON_CONTEXT(php_v8_context);

    v8::MaybeLocal<v8::String> maybe_local_string = v8::JSON::Stringify(context, local_value, local_gap);

    PHP_V8_MAYBE_CATCH(php_v8_context, try_catch);
    PHP_V8_THROW_EXCEPTION_WHEN_EMPTY(maybe_local_string, "Failed to stringify");

    v8::String::Utf8Value str(isolate, maybe_local_string.ToLocalChecked());

    PHP_V8_CONVERT_UTF8VALUE_TO_STRING_WITH_CHECK(str, cstr);

    RETVAL_STRINGL(cstr, str.length());
}


PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_parse, ZEND_RETURN_VALUE, 2, V8\\Value, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_OBJ_INFO(0, json_string, V8\\StringValue, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_stringify, ZEND_RETURN_VALUE, 2, IS_STRING, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_OBJ_INFO(0, json_value, V8\\Value, 0)
                ZEND_ARG_OBJ_INFO(0, gap, V8\\StringValue, 1)
ZEND_END_ARG_INFO()


static const zend_function_entry php_v8_json_methods[] = {
        PHP_V8_ME(JSON, parse,     ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
        PHP_V8_ME(JSON, stringify, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)

        PHP_FE_END
};


PHP_MINIT_FUNCTION(php_v8_json) {
    zend_class_entry ce;
    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS, "JSON", php_v8_json_methods);
    this_ce = zend_register_internal_class(&ce);

    return SUCCESS;
}
