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

#ifndef PHP_V8_UNBOUND_SCRIPT_H
#define PHP_V8_UNBOUND_SCRIPT_H

typedef struct _php_v8_unbound_script_t php_v8_unbound_script_t;

#include "php_v8_exception.h"
#include "php_v8_isolate.h"
#include <v8.h>

extern "C" {
#include "php.h"

#ifdef ZTS
#include "TSRM.h"
#endif
}

extern zend_class_entry *php_v8_unbound_script_class_entry;

inline php_v8_unbound_script_t * php_v8_unbound_script_fetch_object(zend_object *obj);
extern v8::Local<v8::UnboundScript> php_v8_unbound_script_get_local(php_v8_unbound_script_t *php_v8_unbound_script);
extern php_v8_unbound_script_t * php_v8_create_unbound_script(zval *return_value, php_v8_isolate_t *php_v8_isolate, v8::Local<v8::UnboundScript> unbound_script);


#define PHP_V8_FETCH_UNBOUND_SCRIPT(zv) php_v8_unbound_script_fetch_object(Z_OBJ_P(zv))
#define PHP_V8_FETCH_UNBOUND_SCRIPT_INTO(pzval, into) php_v8_unbound_script_t *(into) = PHP_V8_FETCH_UNBOUND_SCRIPT((pzval))

#define PHP_V8_EMPTY_UNBOUND_SCRIPT_MSG "UnboundScript" PHP_V8_EMPTY_HANDLER_MSG_PART
#define PHP_V8_CHECK_EMPTY_UNBOUND_SCRIPT_HANDLER(val) PHP_V8_CHECK_EMPTY_HANDLER((val), PHP_V8_EMPTY_UNBOUND_SCRIPT_MSG)

#define PHP_V8_FETCH_UNBOUND_SCRIPT_WITH_CHECK(pzval, into) \
    PHP_V8_FETCH_UNBOUND_SCRIPT_INTO(pzval, into); \
    PHP_V8_CHECK_EMPTY_UNBOUND_SCRIPT_HANDLER(into);


#define PHP_V8_UNBOUND_SCRIPT_STORE_ISOLATE(to_zval, isolate_zv) zend_update_property(php_v8_unbound_script_class_entry, (to_zval), ZEND_STRL("isolate"), (isolate_zv));
#define PHP_V8_UNBOUND_SCRIPT_READ_ISOLATE(from_zval) zend_read_property(php_v8_unbound_script_class_entry, (from_zval), ZEND_STRL("isolate"), 0, &rv)

#define PHP_V8_CHECK_CODE_POS_RANGE(val, message) \
    if ((val) > INT_MAX || (val) < INT_MIN) { \
        PHP_V8_THROW_VALUE_EXCEPTION(message); \
        return; \
    }

struct _php_v8_unbound_script_t {
  php_v8_isolate_t *php_v8_isolate;
  uint32_t isolate_handle;

  v8::Persistent<v8::UnboundScript> *persistent;

  zend_object std;
};

inline php_v8_unbound_script_t * php_v8_unbound_script_fetch_object(zend_object *obj) {
    return (php_v8_unbound_script_t *)((char *)obj - XtOffsetOf(php_v8_unbound_script_t, std));
}

inline v8::Local<v8::UnboundScript> php_v8_unbound_script_get_local(php_v8_unbound_script_t *php_v8_unbound_script) {
    return v8::Local<v8::UnboundScript>::New(php_v8_unbound_script->php_v8_isolate->isolate, *php_v8_unbound_script->persistent);
}


PHP_MINIT_FUNCTION(php_v8_unbound_script);

#endif //PHP_V8_UNBOUND_SCRIPT_H
