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

#ifndef PHP_V8_STARTUP_DATA_H
#define PHP_V8_STARTUP_DATA_H

typedef struct _php_v8_startup_data_t php_v8_startup_data_t;

#include <v8.h>

extern "C" {
#include "php.h"

#ifdef ZTS
#include "TSRM.h"
#endif
}

extern zend_class_entry* php_v8_stack_frame_class_entry;

inline php_v8_startup_data_t * php_v8_startup_data_fetch_object(zend_object *obj);

#define PHP_V8_STARTUP_DATA_FETCH(zv) php_v8_startup_data_fetch_object(Z_OBJ_P(zv))
#define PHP_V8_STARTUP_DATA_FETCH_INTO(pzval, into) php_v8_startup_data_t *(into) = PHP_V8_STARTUP_DATA_FETCH((pzval))


struct _php_v8_startup_data_t {
    v8::StartupData *blob;
    zend_object std;
};

inline php_v8_startup_data_t * php_v8_startup_data_fetch_object(zend_object *obj) {
    return (php_v8_startup_data_t *) ((char *) obj - XtOffsetOf(php_v8_startup_data_t, std));
}

PHP_MINIT_FUNCTION(php_v8_startup_data);

#endif //PHP_V8_STARTUP_DATA_H
