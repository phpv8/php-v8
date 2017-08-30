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

#include "php_v8_ext_mem_interface.h"
#include "php_v8_object_template.h"
#include "php_v8_function_template.h"
#include "php_v8_value.h"
#include "php_v8.h"

zend_class_entry *php_v8_ext_mem_interface_ce;
#define this_ce php_v8_ext_mem_interface_ce

void php_v8_ext_mem_interface_value_AdjustExternalAllocatedMemory(INTERNAL_FUNCTION_PARAMETERS) {
    zend_long change_in_bytes;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "l", &change_in_bytes) == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_INTO(getThis(), php_v8_value);

    RETURN_LONG(php_v8_value->persistent_data->adjustSize(change_in_bytes));
}

void php_v8_ext_mem_interface_value_GetExternalAllocatedMemory(INTERNAL_FUNCTION_PARAMETERS) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_INTO(getThis(), php_v8_value);

    RETURN_LONG(php_v8_value->persistent_data->getAdjustedSize());
}


void php_v8_ext_mem_interface_function_template_AdjustExternalAllocatedMemory(INTERNAL_FUNCTION_PARAMETERS) {
    zend_long change_in_bytes;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "l", &change_in_bytes) == FAILURE) {
        return;
    }
    PHP_V8_FUNCTION_TEMPLATE_FETCH_INTO(getThis(), php_v8_function_template);

    RETURN_LONG(php_v8_function_template->persistent_data->adjustSize(change_in_bytes));
}

void php_v8_ext_mem_interface_function_template_GetExternalAllocatedMemory(INTERNAL_FUNCTION_PARAMETERS) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_FUNCTION_TEMPLATE_FETCH_INTO(getThis(), php_v8_function_template);

    RETURN_LONG(php_v8_function_template->persistent_data->getAdjustedSize());
}


void php_v8_ext_mem_interface_object_template_AdjustExternalAllocatedMemory(INTERNAL_FUNCTION_PARAMETERS) {
    zend_long change_in_bytes;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "l", &change_in_bytes) == FAILURE) {
        return;
    }

    PHP_V8_OBJECT_TEMPLATE_FETCH_INTO(getThis(), php_v8_object_template);

    RETURN_LONG(php_v8_object_template->persistent_data->adjustSize(change_in_bytes));
}

void php_v8_ext_mem_interface_object_template_GetExternalAllocatedMemory(INTERNAL_FUNCTION_PARAMETERS) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_OBJECT_TEMPLATE_FETCH_INTO(getThis(), php_v8_object_template);

    RETURN_LONG(php_v8_object_template->persistent_data->getAdjustedSize());
}


PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_adjustExternalAllocatedMemory, ZEND_RETURN_VALUE, 1, IS_LONG, 0)
                ZEND_ARG_TYPE_INFO(0, change_in_bytes, IS_LONG, 0)
ZEND_END_ARG_INFO()


PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_getExternalAllocatedMemory, ZEND_RETURN_VALUE, 0, IS_LONG, 0)
ZEND_END_ARG_INFO()


static const zend_function_entry php_v8_ext_mem_interface_methods[] = {
        PHP_V8_ABSTRACT_ME(AdjustableExternalMemoryInterface, adjustExternalAllocatedMemory)
        PHP_V8_ABSTRACT_ME(AdjustableExternalMemoryInterface, getExternalAllocatedMemory)

        PHP_FE_END
};

PHP_MINIT_FUNCTION (php_v8_ext_mem_interface) {
    zend_class_entry ce;

    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS, "AdjustableExternalMemoryInterface", php_v8_ext_mem_interface_methods);
    this_ce = zend_register_internal_interface(&ce);

    return SUCCESS;
}
