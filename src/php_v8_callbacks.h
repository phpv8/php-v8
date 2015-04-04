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

#ifndef PHP_V8_CALLBACKS_H
#define PHP_V8_CALLBACKS_H

#include <v8.h>
#include <map>

extern "C" {
#include "php.h"

#ifdef ZTS
#include "TSRM.h"
#endif
}

typedef struct _php_v8_callback_t php_v8_callback_t;
typedef struct _php_v8_callbacks_bucket_t php_v8_callbacks_bucket_t;

struct cmp_str;
typedef std::map<char *, php_v8_callbacks_bucket_t*, cmp_str> php_v8_callbacks_t;


extern php_v8_callbacks_bucket_t *php_v8_callback_create_bucket(size_t size);
extern void php_v8_callback_destroy_bucket(php_v8_callbacks_bucket_t *bucket);

extern php_v8_callbacks_bucket_t *php_v8_callback_get_or_create_bucket(size_t size,
                                                                       const char *prefix,
                                                                       bool is_symbol,
                                                                       const char *name,
                                                                       php_v8_callbacks_t *callbacks);

extern void php_v8_callbacks_copy_bucket(php_v8_callbacks_bucket_t *from, php_v8_callbacks_bucket_t *to);
extern php_v8_callback_t *php_v8_callback_add(size_t index, zend_fcall_info fci, zend_fcall_info_cache fci_cache, php_v8_callbacks_bucket_t *bucket);
extern void php_v8_callbacks_cleanup(php_v8_callbacks_t *callbacks);

extern void php_v8_callbacks_gc(php_v8_callbacks_t *callbacks, zval **gc_data, int * gc_data_count, zval **table, int *n);
extern int php_v8_weak_callbacks_get_count(php_v8_callbacks_t *callbacks);
extern void php_v8_weak_callbacks_get_zvals(php_v8_callbacks_t *callbacks, zval *& zv);
extern void php_v8_bucket_gc(php_v8_callbacks_bucket_t *bucket, zval **gc_data, int * gc_data_count, zval **table, int *n);

extern void php_v8_callback_function(const v8::FunctionCallbackInfo<v8::Value>& info);
extern void php_v8_callback_accessor_name_getter(v8::Local<v8::Name> property, const v8::PropertyCallbackInfo<v8::Value>& info);
extern void php_v8_callback_accessor_name_setter(v8::Local<v8::Name> property, v8::Local<v8::Value> value, const v8::PropertyCallbackInfo<void>& info);

extern void php_v8_callback_generic_named_property_getter(v8::Local<v8::Name> property, const v8::PropertyCallbackInfo<v8::Value>& info);
extern void php_v8_callback_generic_named_property_setter(v8::Local<v8::Name> property, v8::Local<v8::Value> value, const v8::PropertyCallbackInfo<v8::Value>& info);
extern void php_v8_callback_generic_named_property_query(v8::Local<v8::Name> property, const v8::PropertyCallbackInfo<v8::Integer>& info);
extern void php_v8_callback_generic_named_property_deleter(v8::Local<v8::Name> property, const v8::PropertyCallbackInfo<v8::Boolean>& info);
extern void php_v8_callback_generic_named_property_enumerator( const v8::PropertyCallbackInfo<v8::Array>& info);

extern void php_v8_callback_indexed_property_getter(uint32_t index, const v8::PropertyCallbackInfo<v8::Value>& info);
extern void php_v8_callback_indexed_property_setter(uint32_t index, v8::Local<v8::Value> value, const v8::PropertyCallbackInfo<v8::Value>& info);
extern void php_v8_callback_indexed_property_query(uint32_t index, const v8::PropertyCallbackInfo<v8::Integer>& info);
extern void php_v8_callback_indexed_property_deleter(uint32_t index, const v8::PropertyCallbackInfo<v8::Boolean>& info);
extern void php_v8_callback_indexed_property_enumerator(const v8::PropertyCallbackInfo<v8::Array>& info);

extern bool php_v8_callback_access_check(v8::Local<v8::Context> accessing_context, v8::Local<v8::Object> accessed_object, v8::Local<v8::Value> data);


struct _php_v8_callback_t {
    zval object;
    zend_fcall_info fci;
    zend_fcall_info_cache fci_cache;
};

struct _php_v8_callbacks_bucket_t {
    size_t size;
    php_v8_callback_t **cb;
};

struct cmp_str {
    bool operator()(char const *a, char const *b) const {
        return strcmp(a, b) < 0;
    }
};

#endif //PHP_V8_CALLBACKS_H

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: noet sw=4 ts=4 fdm=marker
 * vim<600: noet sw=4 ts=4
 */
