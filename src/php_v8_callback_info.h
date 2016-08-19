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

#ifndef PHP_V8_CALLBACK_INFO_H
#define PHP_V8_CALLBACK_INFO_H

typedef struct _php_v8_callback_info_t php_v8_callback_info_t;

#include "php_v8_return_value.h"
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

extern zend_class_entry* php_v8_callback_info_class_entry;


extern php_v8_callback_info_t * php_v8_callback_info_fetch_object(zend_object *obj);
extern void php_v8_callback_info_invalidate(php_v8_callback_info_t *php_v8_callback_info);

#define PHP_V8_CALLBACK_INFO_FETCH(zv) php_v8_callback_info_fetch_object(Z_OBJ_P(zv))
#define PHP_V8_CALLBACK_INFO_FETCH_INTO(pzval, into) php_v8_callback_info_t *(into) = PHP_V8_CALLBACK_INFO_FETCH((pzval));


#define PHP_V8_EMPTY_CALLBACK_INFO_MSG "CallbackInfo" PHP_V8_EMPTY_HANDLER_MSG_PART
#define PHP_V8_CHECK_EMPTY_CALLBACK_INFO_HANDLER_MSG(val, message) if (NULL == (val)->php_v8_isolate) { PHP_V8_THROW_EXCEPTION(message); return; }
#define PHP_V8_CHECK_EMPTY_CALLBACK_INFO_HANDLER(val) PHP_V8_CHECK_EMPTY_CALLBACK_INFO_HANDLER_MSG((val), PHP_V8_EMPTY_CALLBACK_INFO_MSG)

#define PHP_V8_CALLBACK_INFO_FETCH_WITH_CHECK(pzval, into) \
    PHP_V8_CALLBACK_INFO_FETCH_INTO(pzval, into); \
    PHP_V8_CHECK_EMPTY_CALLBACK_INFO_HANDLER(into);


// TODO: suggest better naming
#define PHP_V8_V8_CALLBACK_INFO_CHECK_IN_CONTEXT(value) \
    if ((value)->php_v8_return_value == NULL || PHP_V8_RETVAL_ACCEPTS_INVALID == (value)->php_v8_return_value->accepts) { \
        PHP_V8_THROW_EXCEPTION("Attempt to use callback info object out of callback context"); \
        return; \
    }


struct _php_v8_callback_info_t {
    php_v8_isolate_t *php_v8_isolate;
    php_v8_context_t *php_v8_context;
    uint32_t isolate_handle;

    int length;
    // TODO: find something for V8_INLINE Local<Value> operator[](int i) const;
    v8::Persistent<v8::Value> **arguments;
    v8::Persistent<v8::Object> *this_obj;
    v8::Persistent<v8::Object> *holder_obj;
    bool is_construct_call;

    php_v8_return_value_t *php_v8_return_value;
    zval args;

    zval *gc_data;
    int gc_data_count;

    zend_object std;
};

PHP_MINIT_FUNCTION(php_v8_callback_info);

#endif //PHP_V8_CALLBACK_INFO_H
