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

#include "php_v8_source.h"
#include "php_v8_cached_data.h"
#include "php_v8_string.h"
#include "php_v8.h"

zend_class_entry* php_v8_source_class_entry;
#define this_ce php_v8_source_class_entry

void php_v8_update_source_cached_data(zval *src_zv, v8::ScriptCompiler::Source *source) {
    if (!source->GetCachedData()) {
        return;
    }

    zval tmp;
    zval rv;

    zval *cached_data_zv = zend_read_property(this_ce, src_zv, ZEND_STRL("cached_data"), 0, &rv);

    if (!ZVAL_IS_NULL(cached_data_zv)) {
        return;
    }

    php_v8_create_cached_data(&tmp, source->GetCachedData());

    zend_update_property(this_ce, src_zv, ZEND_STRL("cached_data"), &tmp);
    Z_DELREF(tmp);
}

static PHP_METHOD(Source, __construct)
{
    zval *source_string_zv = NULL;
    zval *origin_zv = NULL;
    zval *cached_data_zv = NULL;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o|o!o!", &source_string_zv, &origin_zv, &cached_data_zv) == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(source_string_zv, php_v8_string);

    zend_update_property(this_ce, getThis(), ZEND_STRL("source_string"), source_string_zv);

    if (origin_zv != NULL) {
        zend_update_property(this_ce, getThis(), ZEND_STRL("origin"), origin_zv);
    }

    if (cached_data_zv != NULL) {
        zend_update_property(this_ce, getThis(), ZEND_STRL("cached_data"), cached_data_zv);
    }
}

static PHP_METHOD(Source, getSourceString)
{
    zval rv;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    RETVAL_ZVAL(zend_read_property(this_ce, getThis(), ZEND_STRL("source_string"), 0, &rv), 1, 0);
}

static PHP_METHOD(Source, getScriptOrigin)
{
    zval rv;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    RETVAL_ZVAL(zend_read_property(this_ce, getThis(), ZEND_STRL("origin"), 0, &rv), 1, 0);
}

static PHP_METHOD(Source, getCachedData)
{
    zval rv;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    RETVAL_ZVAL(zend_read_property(this_ce, getThis(), ZEND_STRL("cached_data"), 0, &rv), 1, 0);
}


ZEND_BEGIN_ARG_INFO_EX(arginfo___construct, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 1)
                ZEND_ARG_OBJ_INFO(0, source_string, V8\\StringValue, 0)
                ZEND_ARG_OBJ_INFO(0, origin, V8\\ScriptOrigin, 1)
                ZEND_ARG_OBJ_INFO(0, cached_data, V8\\ScriptCompiler\\CachedData, 1)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_getSourceString, ZEND_RETURN_VALUE, 0, V8\\StringValue, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_getScriptOrigin, ZEND_RETURN_VALUE, 0, V8\\ScriptOrigin, 1)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_getCachedData, ZEND_RETURN_VALUE, 0, V8\\ScriptCompiler\\CachedData, 1)
ZEND_END_ARG_INFO()


static const zend_function_entry php_v8_source_methods[] = {
    PHP_V8_ME(Source, __construct,     ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)
    PHP_V8_ME(Source, getSourceString, ZEND_ACC_PUBLIC)
    PHP_V8_ME(Source, getScriptOrigin, ZEND_ACC_PUBLIC)
    PHP_V8_ME(Source, getCachedData,   ZEND_ACC_PUBLIC)

    PHP_FE_END
};


PHP_MINIT_FUNCTION(php_v8_source)
{
    zend_class_entry ce;

    INIT_NS_CLASS_ENTRY(ce, "V8\\ScriptCompiler", "Source", php_v8_source_methods);
    this_ce = zend_register_internal_class(&ce);

    zend_declare_property_null(this_ce, ZEND_STRL("source_string"), ZEND_ACC_PRIVATE);
    zend_declare_property_null(this_ce, ZEND_STRL("origin"), ZEND_ACC_PRIVATE);
    zend_declare_property_null(this_ce, ZEND_STRL("cached_data"), ZEND_ACC_PRIVATE);

    return SUCCESS;
}
