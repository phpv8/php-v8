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

#ifndef PHP_V8_SCRIPT_H
#define PHP_V8_SCRIPT_H

typedef struct _php_v8_script_t php_v8_script_t;

#include "php_v8_exception.h"
#include "php_v8_context.h"
#include "php_v8_isolate.h"
#include <v8.h>

extern "C" {
#include "php.h"

#ifdef ZTS
#include "TSRM.h"
#endif
}

extern zend_class_entry *php_v8_script_class_entry;

extern php_v8_script_t * php_v8_script_fetch_object(zend_object *obj);
extern php_v8_script_t *php_v8_create_script(zval *return_value, v8::Local<v8::Script> local_script, php_v8_context_t *php_v8_context);

#define PHP_V8_FETCH_SCRIPT(zv) php_v8_script_fetch_object(Z_OBJ_P(zv))
#define PHP_V8_FETCH_SCRIPT_INTO(pzval, into) php_v8_script_t *(into) = PHP_V8_FETCH_SCRIPT((pzval))

#define PHP_V8_EMPTY_SCRIPT_MSG "Script" PHP_V8_EMPTY_HANDLER_MSG_PART
#define PHP_V8_CHECK_EMPTY_SCRIPT_HANDLER(val) PHP_V8_CHECK_EMPTY_HANDLER((val), PHP_V8_EMPTY_SCRIPT_MSG)

#define PHP_V8_FETCH_SCRIPT_WITH_CHECK(pzval, into) \
    PHP_V8_FETCH_SCRIPT_INTO(pzval, into); \
    PHP_V8_CHECK_EMPTY_SCRIPT_HANDLER(into);


#define PHP_V8_SCRIPT_STORE_CONTEXT(to_zval, context_zv) zend_update_property(php_v8_script_class_entry, (to_zval), ZEND_STRL("context"), (context_zv));
#define PHP_V8_SCRIPT_READ_CONTEXT(from_zval) zend_read_property(php_v8_script_class_entry, (from_zval), ZEND_STRL("context"), 0, &rv)

#define PHP_V8_SCRIPT_STORE_ISOLATE(to_zval, isolate_zv) zend_update_property(php_v8_script_class_entry, (to_zval), ZEND_STRL("isolate"), (isolate_zv));
#define PHP_V8_SCRIPT_READ_ISOLATE(from_zval) zend_read_property(php_v8_script_class_entry, (from_zval), ZEND_STRL("isolate"), 0, &rv)


struct _php_v8_script_t {
  php_v8_isolate_t *php_v8_isolate;
  php_v8_context_t *php_v8_context;

  uint32_t isolate_handle;

  v8::Persistent<v8::Script> *persistent;

  zend_object std;
};

inline v8::Local<v8::Script> php_v8_script_get_local(php_v8_script_t *php_v8_script) {
    return v8::Local<v8::Script>::New(php_v8_script->php_v8_isolate->isolate, *php_v8_script->persistent);
}

PHP_MINIT_FUNCTION(php_v8_script);

#endif //PHP_V8_SCRIPT_H
