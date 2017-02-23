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

#ifndef PHP_V8_RETURN_VALUE_H
#define PHP_V8_RETURN_VALUE_H

typedef struct _php_v8_return_value_t php_v8_return_value_t;

#include "php_v8_exceptions.h"
#include "php_v8_context.h"
#include "php_v8_isolate.h"
#include <v8.h>

extern "C" {
#include "php.h"

#ifdef ZTS
#include "TSRM.h"
#endif
}

extern zend_class_entry *php_v8_return_value_class_entry;

inline php_v8_return_value_t *php_v8_return_value_fetch_object(zend_object *obj);
extern php_v8_return_value_t * php_v8_return_value_create_from_return_value(zval *return_value, php_v8_context_t *php_v8_context, int accepts);
inline void php_v8_return_value_mark_expired(php_v8_return_value_t *php_v8_return_value);


#define PHP_V8_RETURN_VALUE_FETCH(zv) php_v8_return_value_fetch_object(Z_OBJ_P(zv))
#define PHP_V8_RETURN_VALUE_FETCH_INTO(pzval, into) php_v8_return_value_t *(into) = PHP_V8_RETURN_VALUE_FETCH((pzval));

#define PHP_V8_EMPTY_RETURN_VALUE_MSG "ReturnValue" PHP_V8_EMPTY_HANDLER_MSG_PART
#define PHP_V8_CHECK_EMPTY_RETURN_VALUE_HANDLER(val, message) if (NULL == (val)->php_v8_isolate) { PHP_V8_THROW_EXCEPTION(message); return; }
#define PHP_V8_CHECK_EMPTY_RETURN_VALUE(val) PHP_V8_CHECK_EMPTY_RETURN_VALUE_HANDLER((val), PHP_V8_EMPTY_RETURN_VALUE_MSG)

#define PHP_V8_FETCH_RETURN_VALUE_WITH_CHECK(pzval, into) \
    PHP_V8_RETURN_VALUE_FETCH_INTO(pzval, into); \
    PHP_V8_CHECK_EMPTY_RETURN_VALUE(into);


#define PHP_V8_RETURN_VALUE_IN_CONTEXT(value) (PHP_V8_RETVAL_ACCEPTS_INVALID != (value)->accepts)
#define PHP_V8_RETURN_VALUE_CHECK_IN_CONTEXT(value) \
    if (!PHP_V8_RETURN_VALUE_IN_CONTEXT(value)) { \
        PHP_V8_THROW_EXCEPTION("Attempt to use return value out of calling function context"); \
        return; \
    }

#define PHP_V8_RETVAL_ACCEPTS_INVALID  -1
#define PHP_V8_RETVAL_ACCEPTS_VOID      0
#define PHP_V8_RETVAL_ACCEPTS_ANY       (1 << 0)
#define PHP_V8_RETVAL_ACCEPTS_INTEGER   (1 << 1)
#define PHP_V8_RETVAL_ACCEPTS_BOOLEAN   (1 << 2)
#define PHP_V8_RETVAL_ACCEPTS_ARRAY     (1 << 3)


struct _php_v8_return_value_t {
    php_v8_isolate_t *php_v8_isolate;
    php_v8_context_t *php_v8_context;

    int accepts;

    v8::ReturnValue<void> *rv_void;
    v8::ReturnValue<v8::Value> *rv_any;
    v8::ReturnValue<v8::Integer> *rv_integer;
    v8::ReturnValue<v8::Boolean> *rv_boolean;
    v8::ReturnValue<v8::Array> *rv_array;

    zend_object std;
};

inline php_v8_return_value_t * php_v8_return_value_fetch_object(zend_object *obj) {
    return (php_v8_return_value_t *)((char *)obj - XtOffsetOf(php_v8_return_value_t, std));
}

inline void php_v8_return_value_mark_expired(php_v8_return_value_t *php_v8_return_value) {
    php_v8_return_value->accepts = PHP_V8_RETVAL_ACCEPTS_INVALID;
}


PHP_MINIT_FUNCTION (php_v8_return_value);

#endif //PHP_V8_RETURN_VALUE_H
