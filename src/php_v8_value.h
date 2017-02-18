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

#ifndef PHP_V8_VALUE_H
#define PHP_V8_VALUE_H

typedef struct _php_v8_value_t php_v8_value_t;

#include "php_v8_exceptions.h"
#include "php_v8_context.h"
#include "php_v8_isolate.h"
#include "php_v8_object_template.h"
#include <v8.h>

extern "C" {
#include "php.h"

#ifdef ZTS
#include "TSRM.h"
#endif
}

extern zend_class_entry *php_v8_value_class_entry;


extern v8::Local<v8::Value> php_v8_value_get_value_local(v8::Isolate *isolate, php_v8_value_t *php_v8_value);
extern php_v8_value_t *php_v8_value_fetch_object(zend_object *obj);

extern zend_class_entry *php_v8_get_class_entry_from_value(v8::Local<v8::Value> value);
extern php_v8_value_t *php_v8_create_value(zval *return_value, v8::Local<v8::Value> value, php_v8_isolate_t *php_v8_isolate);
extern php_v8_value_t *php_v8_get_or_create_value(zval *return_value, v8::Local<v8::Value> local_value, php_v8_isolate_t *php_v8_isolate);

#define PHP_V8_VALUE_FETCH(zv) php_v8_value_fetch_object(Z_OBJ_P(zv))
#define PHP_V8_VALUE_FETCH_INTO(pzval, into) php_v8_value_t *(into) = PHP_V8_VALUE_FETCH((pzval));

#define PHP_V8_EMPTY_VALUE_MSG "Value" PHP_V8_EMPTY_HANDLER_MSG_PART
#define PHP_V8_CHECK_EMPTY_VALUE_HANDLER(val) PHP_V8_CHECK_EMPTY_HANDLER((val), PHP_V8_EMPTY_VALUE_MSG)

#define PHP_V8_VALUE_FETCH_WITH_CHECK(pzval, into) \
    PHP_V8_VALUE_FETCH_INTO(pzval, into); \
    PHP_V8_CHECK_EMPTY_VALUE_HANDLER(into);

#define PHP_V8_VALUE_STORE_ISOLATE(to_zval, from_isolate_zv) zend_update_property(php_v8_value_class_entry, (to_zval), ZEND_STRL("isolate"), (from_isolate_zv));
#define PHP_V8_VALUE_READ_ISOLATE(from_zval) zend_read_property(php_v8_value_class_entry, (from_zval), ZEND_STRL("isolate"), 0, &rv)


#define PHP_V8_VALUE_CONSTRUCT(this, isolate_zv, isolate_v8, value_v8) \
    PHP_V8_ISOLATE_FETCH_WITH_CHECK((isolate_zv), (isolate_v8)); \
    PHP_V8_VALUE_STORE_ISOLATE((this), (isolate_zv)) \
    PHP_V8_VALUE_FETCH_INTO(getThis(), (value_v8)); \
    PHP_V8_STORE_POINTER_TO_ISOLATE((value_v8), (isolate_v8)); \
    PHP_V8_ENTER_ISOLATE((isolate_v8));


#define PHP_V8_OBJECT_CONSTRUCT(this, context_zv, context_v8, value_v8) \
    PHP_V8_CONTEXT_FETCH_WITH_CHECK((context_zv), (context_v8)); \
    PHP_V8_OBJECT_STORE_CONTEXT((this), (context_zv)); \
    PHP_V8_VALUE_FETCH_INTO((this), (value_v8)); \
    PHP_V8_VALUE_STORE_ISOLATE((this), PHP_V8_CONTEXT_READ_ISOLATE(context_zv)); \
    PHP_V8_COPY_POINTER_TO_ISOLATE((value_v8), (context_v8)); \
    PHP_V8_STORE_POINTER_TO_CONTEXT((value_v8), (context_v8)); \
    PHP_V8_ENTER_STORED_ISOLATE((value_v8)); \
    PHP_V8_ENTER_STORED_CONTEXT((value_v8)); \

// TODO: move to better place or is it fine here too?
#define PHP_V8_CONVERT_UTF8VALUE_TO_STRING(from, to) const char *(to) = (const char*) *(from);
#define PHP_V8_CONVERT_UTF8VALUE_TO_STRING_NODECL(from, to) (to) = (const char*) *(from);

#define PHP_V8_CONVERT_UTF8VALUE_TO_STRING_WITH_CHECK(from, to) \
    PHP_V8_CONVERT_UTF8VALUE_TO_STRING((from), (to)); \
    if ((to) == NULL) { \
        PHP_V8_THROW_EXCEPTION("Internal error: Failed to convert Utf8Value to string"); \
        return; \
    }

#define PHP_V8_CONVERT_UTF8VALUE_TO_STRING_WITH_CHECK_NODECL(from, to) \
    PHP_V8_CONVERT_UTF8VALUE_TO_STRING_NODECL((from), (to)); \
    if ((to) == NULL) { \
        PHP_V8_THROW_EXCEPTION("Internal error: Failed to convert Utf8Value to string"); \
        return; \
    }

#define PHP_V8_CONVERT_FROM_V8_STRING_TO_STRING(cstr, v8_local_string_from) \
    v8::String::Utf8Value _v8_utf8_str_##cstr((v8_local_string_from)); \
    PHP_V8_CONVERT_UTF8VALUE_TO_STRING_WITH_CHECK(_v8_utf8_str_##cstr, cstr); \

#define PHP_V8_CONVERT_FROM_V8_STRING_TO_STRING_NODECL(cstr, v8_local_string_from) { \
    v8::String::Utf8Value _v8_utf8_str_##cstr((v8_local_string_from)); \
    PHP_V8_CONVERT_UTF8VALUE_TO_STRING_WITH_CHECK_NODECL(_v8_utf8_str_##cstr, cstr); \
}

// TODO: string length check?
#define PHP_V8_SET_ZVAL_STRING_FROM_V8_STRING(zval_to, v8_local_string_from) { \
    v8::String::Utf8Value _v8_utf8_str((v8_local_string_from)); \
    PHP_V8_CONVERT_UTF8VALUE_TO_STRING_WITH_CHECK(_v8_utf8_str, _v8_utf8_cstr); \
    ZVAL_STRINGL((zval_to), _v8_utf8_cstr, _v8_utf8_str.length()); \
}

struct _php_v8_value_t {
    php_v8_isolate_t *php_v8_isolate;
    php_v8_context_t *php_v8_context;

    uint32_t isolate_handle;

    bool is_weak;
    v8::Persistent<v8::Value> *persistent;
    phpv8::PersistentData *persistent_data;

    zval *gc_data;
    int   gc_data_count;

    zval this_ptr; // makes sense for objects only
    zend_object std;
};


PHP_MINIT_FUNCTION (php_v8_value);

#endif //PHP_V8_VALUE_H
