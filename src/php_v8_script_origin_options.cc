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

#include "php_v8_script_origin_options.h"
#include "php_v8.h"

zend_class_entry* php_v8_script_origin_options_class_entry;
#define this_ce php_v8_script_origin_options_class_entry


void php_v8_create_script_origin_options(zval * return_value, v8::ScriptOriginOptions options) {
    object_init_ex(return_value, this_ce);

    zend_update_property_long(this_ce, return_value, ZEND_STRL("flags"), static_cast<zend_long >(options.Flags()));
}


static PHP_METHOD(ScriptOriginOptions, __construct) {
    zend_long flags = 0;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "|l", &flags) == FAILURE) {
        return;
    }

    zend_update_property_long(this_ce, getThis(), ZEND_STRL("flags"), flags & PHP_V8_SCRIPT_ORIGIN_OPTIONS);
}

static PHP_METHOD(ScriptOriginOptions, getFlags) {
    zval rv;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    RETVAL_ZVAL(zend_read_property(this_ce, getThis(), ZEND_STRL("flags"), 0, &rv), 1, 0);
}

static PHP_METHOD(ScriptOriginOptions, isSharedCrossOrigin) {
    zval rv;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    zval *tmp = zend_read_property(this_ce, getThis(), ZEND_STRL("flags"), 0, &rv);

    RETVAL_BOOL(Z_LVAL_P(tmp) & PHP_V8_SCRIPT_ORIGIN_OPTION_IS_SHARED_CROSS_ORIGIN);
}

static PHP_METHOD(ScriptOriginOptions, isOpaque) {
    zval rv;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    zval *tmp = zend_read_property(this_ce, getThis(), ZEND_STRL("flags"), 0, &rv);

    RETVAL_BOOL(Z_LVAL_P(tmp) & PHP_V8_SCRIPT_ORIGIN_OPTION_IS_OPAQUE);
}

static PHP_METHOD(ScriptOriginOptions, isWasm) {
    zval rv;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    zval *tmp = zend_read_property(this_ce, getThis(), ZEND_STRL("flags"), 0, &rv);

    RETVAL_BOOL(Z_LVAL_P(tmp) & PHP_V8_SCRIPT_ORIGIN_OPTION_IS_WASM);
}

static PHP_METHOD(ScriptOriginOptions, isModule) {
    zval rv;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    zval *tmp = zend_read_property(this_ce, getThis(), ZEND_STRL("flags"), 0, &rv);

    RETVAL_BOOL(Z_LVAL_P(tmp) & PHP_V8_SCRIPT_ORIGIN_OPTION_IS_MODULE);
}


PHP_V8_ZEND_BEGIN_ARG_WITH_CONSTRUCTOR_INFO_EX(arginfo___construct, 0)
                ZEND_ARG_TYPE_INFO(0, options, IS_LONG, 0)
ZEND_END_ARG_INFO()


PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_getFlags, ZEND_RETURN_VALUE, 0, IS_LONG, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_isSharedCrossOrigin, ZEND_RETURN_VALUE, 0, _IS_BOOL, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_isOpaque, ZEND_RETURN_VALUE, 0, _IS_BOOL, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_isWasm, ZEND_RETURN_VALUE, 0, _IS_BOOL, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_isModule, ZEND_RETURN_VALUE, 0, _IS_BOOL, 0)
ZEND_END_ARG_INFO()


static const zend_function_entry php_v8_script_origin_options_methods[] = {
        PHP_V8_ME(ScriptOriginOptions, __construct,         ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)
        PHP_V8_ME(ScriptOriginOptions, getFlags,          ZEND_ACC_PUBLIC)
        PHP_V8_ME(ScriptOriginOptions, isSharedCrossOrigin, ZEND_ACC_PUBLIC)
        PHP_V8_ME(ScriptOriginOptions, isOpaque,            ZEND_ACC_PUBLIC)
        PHP_V8_ME(ScriptOriginOptions, isWasm,              ZEND_ACC_PUBLIC)
        PHP_V8_ME(ScriptOriginOptions, isModule,            ZEND_ACC_PUBLIC)

        PHP_FE_END
};


PHP_MINIT_FUNCTION(php_v8_script_origin_options) {
    zend_class_entry ce;
    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS, "ScriptOriginOptions", php_v8_script_origin_options_methods);
    this_ce = zend_register_internal_class(&ce);

    zend_declare_class_constant_long(this_ce, ZEND_STRL("IS_SHARED_CROSS_ORIGIN"), PHP_V8_SCRIPT_ORIGIN_OPTION_IS_SHARED_CROSS_ORIGIN);
    zend_declare_class_constant_long(this_ce, ZEND_STRL("IS_OPAQUE"),              PHP_V8_SCRIPT_ORIGIN_OPTION_IS_OPAQUE);
    zend_declare_class_constant_long(this_ce, ZEND_STRL("IS_WASM"),                PHP_V8_SCRIPT_ORIGIN_OPTION_IS_WASM);
    zend_declare_class_constant_long(this_ce, ZEND_STRL("IS_MODULE"),              PHP_V8_SCRIPT_ORIGIN_OPTION_IS_MODULE);

    zend_declare_property_long(this_ce, ZEND_STRL("flags"), 0, ZEND_ACC_PRIVATE);

    return SUCCESS;
}
