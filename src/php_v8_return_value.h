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

extern php_v8_return_value_t *php_v8_return_value_create_from_return_value(zval *this_ptr, php_v8_isolate_t *php_v8_isolate, php_v8_context_t *php_v8_context, int accepts);
extern void php_v8_return_value_mark_expired(php_v8_return_value_t *php_v8_return_value);
extern php_v8_return_value_t *php_v8_return_value_fetch_object(zend_object *obj);


#define PHP_V8_RETURN_VALUE_FETCH(zv) php_v8_return_value_fetch_object(Z_OBJ_P(zv))
#define PHP_V8_RETURN_VALUE_FETCH_INTO(pzval, into) php_v8_return_value_t *(into) = PHP_V8_RETURN_VALUE_FETCH((pzval));



#define PHP_V8_EMPTY_RETURN_VALUE_MSG "ReturnValue" PHP_V8_EMPTY_HANDLER_MSG_PART
#define PHP_V8_CHECK_EMPTY_RETURN_VALUE_HANDLER(val, message) if (NULL == (val)->php_v8_isolate) { PHP_V8_THROW_EXCEPTION(message); return; }
#define PHP_V8_CHECK_EMPTY_RETURN_VALUE(val) PHP_V8_CHECK_EMPTY_RETURN_VALUE_HANDLER((val), PHP_V8_EMPTY_RETURN_VALUE_MSG)

#define PHP_V8_FETCH_RETURN_VALUE_WITH_CHECK(pzval, into) \
    PHP_V8_RETURN_VALUE_FETCH_INTO(pzval, into); \
    PHP_V8_CHECK_EMPTY_RETURN_VALUE(into);


#define PHP_V8_RETURN_VALUE_CHECK_IN_CONTEXT(value) \
    if ((value)->accepts < 0) { \
        PHP_V8_THROW_EXCEPTION("Attempt to use ReturnValue out of calling function context"); \
        return; \
    }

#define PHP_V8_RETVAL_ACCEPTS_INVALID  -1
#define PHP_V8_RETVAL_ACCEPTS_VOID      0
#define PHP_V8_RETVAL_ACCEPTS_ANY       (1 << 0)
#define PHP_V8_RETVAL_ACCEPTS_INTEGER   (1 << 1)
#define PHP_V8_RETVAL_ACCEPTS_BOOLEAN   (1 << 2)
#define PHP_V8_RETVAL_ACCEPTS_ARRAY     (1 << 3)

#define PHP_V8_RETVAL_ZVAL          (1 << 4  | PHP_V8_RETVAL_ACCEPTS_ANY)
#define PHP_V8_RETVAL_UNDEFINED     (1 << 5  | PHP_V8_RETVAL_ACCEPTS_ANY)
#define PHP_V8_RETVAL_NULL          (1 << 6  | PHP_V8_RETVAL_ACCEPTS_ANY)
#define PHP_V8_RETVAL_EMPTY_STRING  (1 << 7  | PHP_V8_RETVAL_ACCEPTS_ANY)
#define PHP_V8_RETVAL_BOOL          (1 << 8  | PHP_V8_RETVAL_ACCEPTS_BOOLEAN)
#define PHP_V8_RETVAL_INT32         (1 << 9  | PHP_V8_RETVAL_ACCEPTS_INTEGER)
#define PHP_V8_RETVAL_UINT32        (1 << 10 | PHP_V8_RETVAL_ACCEPTS_INTEGER)
// DEPRECATED
//#define PHP_V8_RETVAL_STRING        (1 << 11 | PHP_V8_RETVAL_ACCEPTS_ANY | PHP_V8_RETVAL_ZVAL)
#define PHP_V8_RETVAL_LONG          (1 << 12 | PHP_V8_RETVAL_ACCEPTS_ANY)
#define PHP_V8_RETVAL_DOUBLE        (1 << 13 | PHP_V8_RETVAL_ACCEPTS_ANY)
#define PHP_V8_RETVAL_V8_VALUE      (1 << 14 | PHP_V8_RETVAL_ACCEPTS_ANY | PHP_V8_RETVAL_ZVAL)

#define PHP_V8_CHECK_ACCEPTS(retval, val_type) { \
    if (!(val_type)) { \
        return; \
    } \
    \
    if((retval)->accepts == PHP_V8_RETVAL_ACCEPTS_VOID) { \
        PHP_V8_THROW_EXCEPTION("ReturnValue doesn't accept any return value"); \
        return; \
    } \
     \
    if((retval)->accepts == PHP_V8_RETVAL_ACCEPTS_INTEGER && !((val_type) & PHP_V8_RETVAL_ACCEPTS_INTEGER)) { \
        PHP_V8_THROW_EXCEPTION("ReturnValue accepts only integers"); \
        return; \
    } \
    \
    if((retval)->accepts == PHP_V8_RETVAL_ACCEPTS_BOOLEAN && !((val_type) & PHP_V8_RETVAL_ACCEPTS_BOOLEAN)) { \
        PHP_V8_THROW_EXCEPTION("ReturnValue accepts only boolean"); \
        return; \
    } \
    \
    if((retval)->accepts == PHP_V8_RETVAL_ACCEPTS_ARRAY && !((val_type) & PHP_V8_RETVAL_ACCEPTS_ARRAY)) { \
        PHP_V8_THROW_EXCEPTION("ReturnValue accepts only instances of \\V8\\ArrayObject class"); \
        return; \
    } \
    \
    if ((retval)->type & PHP_V8_RETVAL_ZVAL && !Z_ISUNDEF((retval)->value.php_v8_value_zv)) { \
        zval_ptr_dtor(&(retval)->value.php_v8_value_zv); \
    }\
    (retval)->type = (val_type); \
}

struct _php_v8_return_value_t {
    php_v8_isolate_t *php_v8_isolate;
    php_v8_context_t *php_v8_context;

    int accepts;

    int type;

    union {
        bool        set_bool;
        int32_t     set_int32;
        uint32_t    set_uint32;
        zend_long   set_long;
        double      set_double;
        zval        php_v8_value_zv;
    } value;

    zval *gc_data;
    int   gc_data_count;

    zval this_ptr;
    zend_object std;
};


PHP_MINIT_FUNCTION (php_v8_return_value);

#endif //PHP_V8_RETURN_VALUE_H
/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: noet sw=4 ts=4 fdm=marker
 * vim<600: noet sw=4 ts=4
 */
