/*
 * This file is part of the pinepain/php-v8 PHP extension.
 *
 * Copyright (c) 2015-2018 Bogdan Padalko <pinepain@gmail.com>
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

#include "php_v8_heap_statistics.h"
#include "php_v8_value.h"
#include "php_v8.h"

zend_class_entry *php_v8_heap_statistics_class_entry;
#define this_ce php_v8_heap_statistics_class_entry


void php_v8_heap_statistics_create_from_heap_statistics(zval *return_value, v8::HeapStatistics *hs) {
    assert(NULL != hs);

    object_init_ex(return_value, this_ce);

    zend_update_property_double(this_ce, return_value, ZEND_STRL("total_heap_size"), hs->total_heap_size());
    zend_update_property_double(this_ce, return_value, ZEND_STRL("total_heap_size_executable"), hs->total_heap_size_executable());
    zend_update_property_double(this_ce, return_value, ZEND_STRL("total_physical_size"), hs->total_physical_size());
    zend_update_property_double(this_ce, return_value, ZEND_STRL("total_available_size"), hs->total_available_size());
    zend_update_property_double(this_ce, return_value, ZEND_STRL("used_heap_size"), hs->used_heap_size());
    zend_update_property_double(this_ce, return_value, ZEND_STRL("heap_size_limit"), hs->heap_size_limit());
    zend_update_property_double(this_ce, return_value, ZEND_STRL("malloced_memory"), hs->malloced_memory());
    zend_update_property_double(this_ce, return_value, ZEND_STRL("peak_malloced_memory"), hs->peak_malloced_memory());

    zend_update_property_bool(this_ce, return_value, ZEND_STRL("does_zap_garbage"), static_cast<zend_long>(hs->does_zap_garbage()));
}

static PHP_METHOD(HeapStatistics, __construct) {
    double total_heap_size = 0;
    double total_heap_size_executable = 0;
    double total_physical_size = 0;
    double total_available_size = 0;
    double used_heap_size = 0;
    double heap_size_limit = 0;
    double malloced_memory = 0;
    double peak_malloced_memory = 0;

    zend_bool does_zap_garbage = '\0';

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "|" "dddd" "dddd" "b",
                              &total_heap_size, &total_heap_size_executable, &total_physical_size, &total_available_size,
                              &used_heap_size, &heap_size_limit, &malloced_memory, &peak_malloced_memory,
                              &does_zap_garbage) == FAILURE) {
        return;
    }

    zend_update_property_double(this_ce, getThis(), ZEND_STRL("total_heap_size"), total_heap_size);
    zend_update_property_double(this_ce, getThis(), ZEND_STRL("total_heap_size_executable"), total_heap_size_executable);
    zend_update_property_double(this_ce, getThis(), ZEND_STRL("total_physical_size"), total_physical_size);
    zend_update_property_double(this_ce, getThis(), ZEND_STRL("total_available_size"), total_available_size);
    zend_update_property_double(this_ce, getThis(), ZEND_STRL("used_heap_size"), used_heap_size);
    zend_update_property_double(this_ce, getThis(), ZEND_STRL("heap_size_limit"), heap_size_limit);
    zend_update_property_double(this_ce, getThis(), ZEND_STRL("malloced_memory"), malloced_memory);
    zend_update_property_double(this_ce, getThis(), ZEND_STRL("peak_malloced_memory"), peak_malloced_memory);

    zend_update_property_bool(this_ce, getThis(), ZEND_STRL("does_zap_garbage"), does_zap_garbage);
}

static PHP_METHOD(HeapStatistics, getTotalHeapSize) {
    zval rv;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    RETVAL_ZVAL(zend_read_property(this_ce, getThis(), ZEND_STRL("total_heap_size"), 0, &rv), 1, 0);
}

static PHP_METHOD(HeapStatistics, getTotalHeapSizeExecutable) {
    zval rv;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    RETVAL_ZVAL(zend_read_property(this_ce, getThis(), ZEND_STRL("total_heap_size_executable"), 0, &rv), 1, 0);
}

static PHP_METHOD(HeapStatistics, getTotalPhysicalSize) {
    zval rv;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    RETVAL_ZVAL(zend_read_property(this_ce, getThis(), ZEND_STRL("total_physical_size"), 0, &rv), 1, 0);
}

static PHP_METHOD(HeapStatistics, getTotalAvailableSize) {
    zval rv;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    RETVAL_ZVAL(zend_read_property(this_ce, getThis(), ZEND_STRL("total_available_size"), 0, &rv), 1, 0);
}

static PHP_METHOD(HeapStatistics, getUsedHeapSize) {
    zval rv;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    RETVAL_ZVAL(zend_read_property(this_ce, getThis(), ZEND_STRL("used_heap_size"), 0, &rv), 1, 0);
}

static PHP_METHOD(HeapStatistics, getHeapSizeLimit) {
    zval rv;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    RETVAL_ZVAL(zend_read_property(this_ce, getThis(), ZEND_STRL("heap_size_limit"), 0, &rv), 1, 0);
}

static PHP_METHOD(HeapStatistics, getMallocedMemory) {
    zval rv;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    RETVAL_ZVAL(zend_read_property(this_ce, getThis(), ZEND_STRL("malloced_memory"), 0, &rv), 1, 0);
}

static PHP_METHOD(HeapStatistics, getPeakMallocedMemory) {
    zval rv;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    RETVAL_ZVAL(zend_read_property(this_ce, getThis(), ZEND_STRL("peak_malloced_memory"), 0, &rv), 1, 0);
}

static PHP_METHOD(HeapStatistics, doesZapGarbage) {
    zval rv;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    RETVAL_ZVAL(zend_read_property(this_ce, getThis(), ZEND_STRL("does_zap_garbage"), 0, &rv), 1, 0);
}


PHP_V8_ZEND_BEGIN_ARG_WITH_CONSTRUCTOR_INFO_EX(arginfo___construct, 0)
                ZEND_ARG_TYPE_INFO(0, total_heap_size, IS_DOUBLE, 0)
                ZEND_ARG_TYPE_INFO(0, total_heap_size_executable, IS_DOUBLE, 0)
                ZEND_ARG_TYPE_INFO(0, total_physical_size, IS_DOUBLE, 0)
                ZEND_ARG_TYPE_INFO(0, total_available_size, IS_DOUBLE, 0)

                ZEND_ARG_TYPE_INFO(0, used_heap_size, IS_DOUBLE, 0)
                ZEND_ARG_TYPE_INFO(0, heap_size_limit, IS_DOUBLE, 0)
                ZEND_ARG_TYPE_INFO(0, malloced_memory, IS_DOUBLE, 0)
                ZEND_ARG_TYPE_INFO(0, peak_malloced_memory, IS_DOUBLE, 0)

                ZEND_ARG_TYPE_INFO(0, does_zap_garbage, _IS_BOOL, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_getTotalHeapSize, ZEND_RETURN_VALUE, 0, IS_DOUBLE, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_getTotalHeapSizeExecutable, ZEND_RETURN_VALUE, 0, IS_DOUBLE, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_getTotalPhysicalSize, ZEND_RETURN_VALUE, 0, IS_DOUBLE, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_getTotalAvailableSize, ZEND_RETURN_VALUE, 0, IS_DOUBLE, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_getUsedHeapSize, ZEND_RETURN_VALUE, 0, IS_DOUBLE, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_getHeapSizeLimit, ZEND_RETURN_VALUE, 0, IS_DOUBLE, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_getMallocedMemory, ZEND_RETURN_VALUE, 0, IS_DOUBLE, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_getPeakMallocedMemory, ZEND_RETURN_VALUE, 0, IS_DOUBLE, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_doesZapGarbage, ZEND_RETURN_VALUE, 0, _IS_BOOL, 0)
ZEND_END_ARG_INFO()


static const zend_function_entry php_v8_heap_statistics_methods[] = {
        PHP_V8_ME(HeapStatistics, __construct, ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)

        PHP_V8_ME(HeapStatistics, getTotalHeapSize, ZEND_ACC_PUBLIC)
        PHP_V8_ME(HeapStatistics, getTotalHeapSizeExecutable, ZEND_ACC_PUBLIC)
        PHP_V8_ME(HeapStatistics, getTotalPhysicalSize, ZEND_ACC_PUBLIC)
        PHP_V8_ME(HeapStatistics, getTotalAvailableSize, ZEND_ACC_PUBLIC)
        PHP_V8_ME(HeapStatistics, getUsedHeapSize, ZEND_ACC_PUBLIC)
        PHP_V8_ME(HeapStatistics, getHeapSizeLimit, ZEND_ACC_PUBLIC)
        PHP_V8_ME(HeapStatistics, getMallocedMemory, ZEND_ACC_PUBLIC)
        PHP_V8_ME(HeapStatistics, getPeakMallocedMemory, ZEND_ACC_PUBLIC)
        PHP_V8_ME(HeapStatistics, doesZapGarbage, ZEND_ACC_PUBLIC)

        PHP_FE_END
};


PHP_MINIT_FUNCTION (php_v8_heap_statistics) {
    zend_class_entry ce;
    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS, "HeapStatistics", php_v8_heap_statistics_methods);
    this_ce = zend_register_internal_class(&ce);

    zend_declare_property_double(this_ce, ZEND_STRL("total_heap_size"),      0, ZEND_ACC_PRIVATE);
    zend_declare_property_double(this_ce, ZEND_STRL("total_heap_size_executable"), 0, ZEND_ACC_PRIVATE);
    zend_declare_property_double(this_ce, ZEND_STRL("total_physical_size"),  0, ZEND_ACC_PRIVATE);
    zend_declare_property_double(this_ce, ZEND_STRL("total_available_size"), 0, ZEND_ACC_PRIVATE);
    zend_declare_property_double(this_ce, ZEND_STRL("used_heap_size"),       0, ZEND_ACC_PRIVATE);
    zend_declare_property_double(this_ce, ZEND_STRL("heap_size_limit"),      0, ZEND_ACC_PRIVATE);
    zend_declare_property_double(this_ce, ZEND_STRL("malloced_memory"),      0, ZEND_ACC_PRIVATE);
    zend_declare_property_double(this_ce, ZEND_STRL("peak_malloced_memory"), 0, ZEND_ACC_PRIVATE);

    zend_declare_property_bool(this_ce, ZEND_STRL("does_zap_garbage"), false, ZEND_ACC_PRIVATE);

    return SUCCESS;
}
