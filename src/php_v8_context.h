/*
  +----------------------------------------------------------------------+
  | This file is part of the pinepain/php-v8 PHP extension.              |
  |                                                                      |
  | Copyright (c) 2015-2016 Bogdan Padalko <pinepain@gmail.com>          |
  |                                                                      |
  | Licensed under the MIT license: http://opensource.org/licenses/MIT   |
  |                                                                      |
  | For the full copyright and license information, please view the      |
  | LICENSE file that was distributed with this source or visit          |
  | http://opensource.org/licenses/MIT                                   |
  +----------------------------------------------------------------------+
*/

#ifndef PHP_V8_CONTEXT_H
#define PHP_V8_CONTEXT_H

typedef struct _php_v8_context_t php_v8_context_t;

#include "php_v8_isolate.h"
#include <v8.h>

extern "C" {
#include "php.h"

#ifdef ZTS
#include "TSRM.h"
#endif
}

extern zend_class_entry *php_v8_context_class_entry;

extern v8::Local<v8::Context> php_v8_context_get_local(v8::Isolate *isolate, php_v8_context_t *php_v8_context);

extern php_v8_context_t *php_v8_context_fetch_object(zend_object *obj);

extern void php_v8_context_store_reference(v8::Isolate *isolate, v8::Local<v8::Context> context,
                                           php_v8_context_t *php_v8_context);

extern php_v8_context_t *php_v8_context_get_reference(v8::Local<v8::Context> context);


#define PHP_V8_CONTEXT_FETCH(zv) php_v8_context_fetch_object(Z_OBJ_P(zv))
#define PHP_V8_CONTEXT_FETCH_INTO(pzval, into) php_v8_context_t* (into) = PHP_V8_CONTEXT_FETCH((pzval))


#define PHP_V8_EMPTY_CONTEXT_MSG "Context" PHP_V8_EMPTY_HANDLER_MSG_PART
#define PHP_V8_CHECK_EMPTY_CONTEXT_HANDLER_MSG(val, message) if (NULL == (val)->php_v8_isolate) { PHP_V8_THROW_EXCEPTION(message); return; }
#define PHP_V8_CHECK_EMPTY_CONTEXT_HANDLER(val) PHP_V8_CHECK_EMPTY_CONTEXT_HANDLER_MSG((val), PHP_V8_EMPTY_CONTEXT_MSG)

#define PHP_V8_CONTEXT_FETCH_WITH_CHECK(pzval, into) \
    PHP_V8_CONTEXT_FETCH_INTO(pzval, into); \
    PHP_V8_CHECK_EMPTY_CONTEXT_HANDLER(into);


#define PHP_V8_STORE_POINTER_TO_CONTEXT(to, context_ptr) (to)->php_v8_context = (context_ptr);
#define PHP_V8_COPY_POINTER_TO_CONTEXT(to, from) PHP_V8_STORE_POINTER_TO_CONTEXT((to), (from)->php_v8_context);

#define PHP_V8_CONTEXT_STORE_ISOLATE(to_zval, from_isolate_zv) zend_update_property(php_v8_context_class_entry, (to_zval), ZEND_STRL("isolate"), (from_isolate_zv));
#define PHP_V8_CONTEXT_READ_ISOLATE(from_zval) zend_read_property(php_v8_context_class_entry, (from_zval), ZEND_STRL("isolate"), 0, &rv)


#define PHP_V8_DECLARE_CONTEXT(php_v8_context) \
    v8::Local<v8::Context> context = v8::Local<v8::Context>::New(isolate, *(php_v8_context)->context);

#define PHP_V8_CONTEXT_ENTER(context) \
    v8::Context::Scope context_scope(context);

#define PHP_V8_ENTER_CONTEXT(php_v8_context) \
    v8::Local<v8::Context> context = php_v8_context_get_local(isolate, (php_v8_context)); \
    PHP_V8_CONTEXT_ENTER(context);

#define PHP_V8_ENTER_STORED_CONTEXT(stored) PHP_V8_ENTER_CONTEXT((stored)->php_v8_context);


struct _php_v8_context_t {
    php_v8_isolate_t *php_v8_isolate;
    v8::Persistent<v8::Context> *context;

    uint32_t isolate_handle;
    zval this_ptr;
    zend_object std;
};


PHP_MINIT_FUNCTION(php_v8_context);


#endif //PHP_V8_CONTEXT_H
