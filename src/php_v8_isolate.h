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

#ifndef PHP_V8_ISOLATE_H
#define PHP_V8_ISOLATE_H

typedef struct _php_v8_isolate_t php_v8_isolate_t;

#include "php_v8_isolate_limits.h"
#include "php_v8_exceptions.h"
#include "php_v8_callbacks.h"
#include <v8.h>
#include <map>

extern "C" {
#include "php.h"

#ifdef ZTS
#include "TSRM.h"
#endif
}

extern zend_class_entry *php_v8_isolate_class_entry;

inline php_v8_isolate_t * php_v8_isolate_fetch_object(zend_object *obj);
inline v8::Local<v8::Private> php_v8_isolate_get_key_local(php_v8_isolate_t *php_v8_isolate);

// TODO: remove or cleanup to use for debug reasons
#define SX(x) #x
#define SX_(x) S(x)
//#define S__LINE__ SX_(__LINE__)
//#define S__FILE__ SX_(__FILE__)
//#define PHP_V8_ISOLATES_CHECK(first, second) if ((first)->isolate != (second)->isolate) { PHP_V8_THROW_EXCEPTION("Isolates mismatch: " S__FILE__ ":" S__LINE__); return; }
//#define PHP_V8_ISOLATES_CHECK_USING_ISOLATE(first, using_isolate) if ((first)->isolate != (using_isolate)) { PHP_V8_THROW_EXCEPTION("Isolates mismatch: " S__FILE__ ":" S__LINE__); return; }

#define PHP_V8_ISOLATES_MISMATCH_MSG "Isolates mismatch"

#define PHP_V8_ISOLATE_FETCH(zv) php_v8_isolate_fetch_object(Z_OBJ_P(zv))
#define PHP_V8_ISOLATE_FETCH_INTO(pzval, into) php_v8_isolate_t *(into) = PHP_V8_ISOLATE_FETCH((pzval))


#define PHP_V8_EMPTY_ISOLATE_MSG "Isolate" PHP_V8_EMPTY_HANDLER_MSG_PART
#define PHP_V8_CHECK_EMPTY_ISOLATE_HANDLER_MSG(val, message) if (NULL == (val)->isolate) { PHP_V8_THROW_EXCEPTION(message); return; }
#define PHP_V8_CHECK_EMPTY_ISOLATE_HANDLER(val) PHP_V8_CHECK_EMPTY_ISOLATE_HANDLER_MSG((val), PHP_V8_EMPTY_ISOLATE_MSG)

#define PHP_V8_ISOLATE_FETCH_WITH_CHECK(pzval, into) \
    PHP_V8_ISOLATE_FETCH_INTO(pzval, into); \
    PHP_V8_CHECK_EMPTY_ISOLATE_HANDLER(into);


#define PHP_V8_ISOLATES_CHECK(first, second) if ((first)->isolate != (second)->isolate) { PHP_V8_THROW_EXCEPTION(PHP_V8_ISOLATES_MISMATCH_MSG); return; }
#define PHP_V8_ISOLATES_CHECK_USING_ISOLATE(first, using_isolate) if ((first)->isolate != (using_isolate)) { PHP_V8_THROW_EXCEPTION(PHP_V8_ISOLATES_MISMATCH_MSG); return; }

//#define PHP_V8_STORE_POINTER_TO_ISOLATE(to, isolate_ptr) (to)->php_v8_isolate = (isolate_ptr);
//#define PHP_V8_COPY_POINTER_TO_ISOLATE(to, from) PHP_V8_STORE_POINTER_TO_ISOLATE((to), (from)->php_v8_isolate);

#define PHP_V8_DECLARE_ISOLATE_LOCAL_ALIAS(i) v8::Isolate *isolate = (i);

#define PHP_V8_DECLARE_ISOLATE(php_v8_isolate) \
    v8::Isolate *isolate = (php_v8_isolate)->isolate;

#define PHP_V8_ISOLATE_ENTER(isolate) \
    v8::Locker locker(isolate); \
    v8::Isolate::Scope isolate_scope(isolate); \
    v8::HandleScope handle_scope(isolate);

#define PHP_V8_ENTER_ISOLATE(php_v8_isolate) \
    PHP_V8_DECLARE_ISOLATE(php_v8_isolate); \
    PHP_V8_ISOLATE_ENTER(isolate); \

#define PHP_V8_ENTER_STORED_ISOLATE(stored) PHP_V8_ENTER_ISOLATE((stored)->php_v8_isolate);

#define PHP_V8_ISOLATE_STORE_REFERENCE(php_v8_isolate) (php_v8_isolate)->isolate->SetData(0, (void *) (php_v8_isolate));
#define PHP_V8_ISOLATE_FETCH_REFERENCE(isolate) ((php_v8_isolate_t*) (isolate)->GetData(0))

#define PHP_V8_STORE_ISOLATE_OBJECT_HANDLE(isolate_handle_value, object_to_store) \
    (object_to_store)->isolate_handle = (isolate_handle_value); \

#define PHP_V8_COPY_ISOLATE_OBJECT_HANDLE(from, to) PHP_V8_STORE_ISOLATE_OBJECT_HANDLE((from)->isolate_handle, (to));

#define PHP_V8_ISOLATE_HAS_VALID_HANDLE(object_that_has_stored) ((object_that_has_stored)->isolate_handle && IS_OBJ_VALID(EG(objects_store).object_buckets[(object_that_has_stored)->isolate_handle]))


#define PHP_V8_STORE_POINTER_TO_ISOLATE(to, isolate_ptr) \
    (to)->php_v8_isolate = (isolate_ptr); \
    PHP_V8_COPY_ISOLATE_OBJECT_HANDLE((isolate_ptr), (to));


#define PHP_V8_COPY_POINTER_TO_ISOLATE(to, from) PHP_V8_STORE_POINTER_TO_ISOLATE((to), (from)->php_v8_isolate);

#define PHP_V8_DATA_ISOLATES_CHECK(first, second) PHP_V8_ISOLATES_CHECK((first)->php_v8_isolate, (second)->php_v8_isolate);
#define PHP_V8_DATA_ISOLATES_CHECK_USING(first, php_v8_isolate) PHP_V8_ISOLATES_CHECK((first)->php_v8_isolate, (php_v8_isolate));
#define PHP_V8_DATA_ISOLATES_CHECK_USING_ISOLATE(first, using_isolate) PHP_V8_ISOLATES_CHECK_USING_ISOLATE((first)->php_v8_isolate, (using_isolate));


#define PHP_V8_ISOLATE_REQUIRE_IN_ISOLATE() \
    if (v8::Isolate::GetCurrent() == NULL) {        \
        PHP_V8_THROW_EXCEPTION("Not in isolate!");  \
        return;                                     \
    }                                               \

#define PHP_V8_ISOLATE_REQUIRE_IN_CONTEXT(isolate) \
    if (!isolate->InContext()) {                    \
        PHP_V8_THROW_EXCEPTION("Not in context!");  \
        return;                                     \
    }                                               \



struct _php_v8_isolate_t {
    v8::Isolate *isolate;
    v8::Isolate::CreateParams *create_params;

    phpv8::PersistentCollection<v8::FunctionTemplate> *weak_function_templates;
    phpv8::PersistentCollection<v8::ObjectTemplate> *weak_object_templates;
    phpv8::PersistentCollection<v8::Value> *weak_values;

    v8::Persistent<v8::Private> key;

    uint32_t isolate_handle;
    php_v8_isolate_limits_t limits;

    zval *gc_data;
    int   gc_data_count;

    zend_object std;
};

inline php_v8_isolate_t *php_v8_isolate_fetch_object(zend_object *obj) {
    return (php_v8_isolate_t *) ((char *) obj - XtOffsetOf(php_v8_isolate_t, std));
}

inline v8::Local<v8::Private> php_v8_isolate_get_key_local(php_v8_isolate_t *php_v8_isolate) {
    return v8::Local<v8::Private>::New(php_v8_isolate->isolate, php_v8_isolate->key);
}

PHP_MINIT_FUNCTION(php_v8_isolate);

#endif //PHP_V8_ISOLATE_H
