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

#ifndef PHP_V8_CACHED_DATA_H
#define PHP_V8_CACHED_DATA_H

typedef struct _php_v8_cached_data_t php_v8_cached_data_t;

#include "php_v8_exceptions.h"
#include <v8.h>

extern "C" {
#include "php.h"

#ifdef ZTS
#include "TSRM.h"
#endif
}

extern zend_class_entry *php_v8_cached_data_class_entry;

inline php_v8_cached_data_t * php_v8_cached_data_fetch_object(zend_object *obj);
extern php_v8_cached_data_t * php_v8_create_cached_data(zval *return_value, const v8::ScriptCompiler::CachedData *cached_data);


#define PHP_V8_FETCH_CACHED_DATA(zv) php_v8_cached_data_fetch_object(Z_OBJ_P(zv))
#define PHP_V8_FETCH_CACHED_DATA_INTO(pzval, into) php_v8_cached_data_t *(into) = PHP_V8_FETCH_CACHED_DATA((pzval))

#define PHP_V8_EMPTY_CACHED_DATA_MSG "CachedData" PHP_V8_EMPTY_HANDLER_MSG_PART
#define PHP_V8_CHECK_EMPTY_CACHED_DATA_HANDLER(val) if (NULL == (val)->cached_data) { PHP_V8_THROW_EXCEPTION( PHP_V8_EMPTY_CACHED_DATA_MSG); return; }


#define PHP_V8_FETCH_CACHED_DATA_WITH_CHECK(pzval, into) \
    PHP_V8_FETCH_CACHED_DATA_INTO(pzval, into); \
    PHP_V8_CHECK_EMPTY_CACHED_DATA_HANDLER(into);


#define PHP_V8_CHECK_CACHE_DATA_STRING_RANGE(str, message) \
    if (MAYBE_ZSTR_LEN(str) > INT_MAX) { \
        PHP_V8_THROW_VALUE_EXCEPTION(message); \
        return; \
    }


struct _php_v8_cached_data_t {
    v8::ScriptCompiler::CachedData *cached_data;

  zend_object std;
};

inline php_v8_cached_data_t * php_v8_cached_data_fetch_object(zend_object *obj) {
    return (php_v8_cached_data_t *)((char *)obj - XtOffsetOf(php_v8_cached_data_t, std));
}

PHP_MINIT_FUNCTION(php_v8_cached_data);

#endif //PHP_V8_CACHED_DATA_H
