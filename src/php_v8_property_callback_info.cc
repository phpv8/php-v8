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

#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include "php_v8_property_callback_info.h"
#include "php_v8_callback_info.h"
#include "php_v8_return_value.h"
#include "php_v8.h"

zend_class_entry *php_v8_property_callback_info_class_entry;
#define this_ce php_v8_property_callback_info_class_entry


template<class T>
php_v8_callback_info_t *php_v8_callback_info_create_from_info_meta(zval *this_ptr, const v8::PropertyCallbackInfo<T> &info, int accepts);


php_v8_callback_info_t *php_v8_callback_info_create_from_info(zval *this_ptr, const v8::PropertyCallbackInfo<v8::Value> &info) {
    return php_v8_callback_info_create_from_info_meta(this_ptr, info, PHP_V8_RETVAL_ACCEPTS_ANY);
}

php_v8_callback_info_t *php_v8_callback_info_create_from_info(zval *this_ptr, const v8::PropertyCallbackInfo<v8::Array> &info) {
    return php_v8_callback_info_create_from_info_meta(this_ptr, info, PHP_V8_RETVAL_ACCEPTS_ARRAY);
}

php_v8_callback_info_t *php_v8_callback_info_create_from_info(zval *this_ptr, const v8::PropertyCallbackInfo<v8::Integer> &info) {
    return php_v8_callback_info_create_from_info_meta(this_ptr, info, PHP_V8_RETVAL_ACCEPTS_INTEGER);
}

php_v8_callback_info_t *php_v8_callback_info_create_from_info(zval *this_ptr, const v8::PropertyCallbackInfo<v8::Boolean> &info) {
    return php_v8_callback_info_create_from_info_meta(this_ptr, info, PHP_V8_RETVAL_ACCEPTS_BOOLEAN);
}

php_v8_callback_info_t *php_v8_callback_info_create_from_info(zval *this_ptr, const v8::PropertyCallbackInfo<void> &info) {
    return php_v8_callback_info_create_from_info_meta(this_ptr, info, PHP_V8_RETVAL_ACCEPTS_VOID);
}

template<class T>
php_v8_callback_info_t *php_v8_callback_info_create_from_info_meta(zval *this_ptr, const v8::PropertyCallbackInfo<T> &info, int accepts) {
    zval retval;
    v8::Isolate *isolate = info.GetIsolate();
    v8::Local<v8::Context> context = isolate->GetCurrentContext();

    if (context.IsEmpty()) {
        PHP_V8_THROW_EXCEPTION("Internal exception: no calling context found");
        return NULL;
    }

    object_init_ex(this_ptr, this_ce);
    PHP_V8_CALLBACK_INFO_FETCH_INTO(this_ptr, php_v8_callback_info);

    php_v8_callback_info->php_v8_isolate = PHP_V8_ISOLATE_FETCH_REFERENCE(isolate);
    php_v8_callback_info->php_v8_context = php_v8_context_get_reference(context);

    php_v8_callback_info->this_obj->Reset(isolate, info.This());
    php_v8_callback_info->holder_obj->Reset(isolate, info.Holder());

    php_v8_callback_info->php_v8_return_value = php_v8_return_value_create_from_return_value(
            &retval,
            php_v8_callback_info->php_v8_isolate,
            php_v8_callback_info->php_v8_context,
            accepts
    );

    return php_v8_callback_info;
}

static const zend_function_entry php_v8_property_callback_info_methods[] = {
        PHP_FE_END
};

PHP_MINIT_FUNCTION (php_v8_property_callback_info) {
    zend_class_entry ce;
    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS, "PropertyCallbackInfo", php_v8_property_callback_info_methods);
    this_ce = zend_register_internal_class_ex(&ce, php_v8_callback_info_class_entry);

    return SUCCESS;
}


/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: noet sw=4 ts=4 fdm=marker
 * vim<600: noet sw=4 ts=4
 */


