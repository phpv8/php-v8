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

#ifndef PHP_V8_FUNCTION_TEMPLATE_H
#define PHP_V8_FUNCTION_TEMPLATE_H

typedef struct _php_v8_function_template_t php_v8_function_template_t;

#include "php_v8_template.h"
#include "php_v8_exceptions.h"
#include "php_v8_isolate.h"
#include <v8.h>

extern "C" {
#include "php.h"

#ifdef ZTS
#include "TSRM.h"
#endif
}

extern zend_class_entry* php_v8_function_template_class_entry;
typedef struct _php_v8_function_template_t php_v8_function_template_t;


extern v8::Local<v8::FunctionTemplate> php_v8_function_template_get_local(v8::Isolate *isolate, php_v8_function_template_t *php_v8_function_template);
extern php_v8_function_template_t * php_v8_function_template_fetch_object(zend_object *obj);


#define PHP_V8_FUNCTION_TEMPLATE_FETCH(zv) php_v8_function_template_fetch_object(Z_OBJ_P(zv))
#define PHP_V8_FUNCTION_TEMPLATE_FETCH_INTO(pzval, into) php_v8_function_template_t *(into) = PHP_V8_FUNCTION_TEMPLATE_FETCH((pzval));


#define PHP_V8_EMPTY_FUNCTION_TEMPLATE_MSG "FunctionTemplate" PHP_V8_EMPTY_HANDLER_MSG_PART
#define PHP_V8_CHECK_EMPTY_FUNCTION_TEMPLATE_HANDLER(val) PHP_V8_CHECK_EMPTY_HANDLER((val), PHP_V8_EMPTY_FUNCTION_TEMPLATE_MSG)

#define PHP_V8_FETCH_FUNCTION_TEMPLATE_WITH_CHECK(pzval, into) \
    PHP_V8_FUNCTION_TEMPLATE_FETCH_INTO(pzval, into); \
    PHP_V8_CHECK_EMPTY_FUNCTION_TEMPLATE_HANDLER(into);


struct _php_v8_function_template_t {
    php_v8_isolate_t *php_v8_isolate;

    uint32_t isolate_handle;

    bool is_weak;
    v8::Persistent<v8::FunctionTemplate> *persistent;
    php_v8_callbacks_t *callbacks;

    zval *gc_data;
    int gc_data_count;

    phpv8::TemplateNode *node;

    zend_object std;
};


PHP_MINIT_FUNCTION(php_v8_function_template);

#endif //PHP_V8_FUNCTION_TEMPLATE_H

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: noet sw=4 ts=4 fdm=marker
 * vim<600: noet sw=4 ts=4
 */
