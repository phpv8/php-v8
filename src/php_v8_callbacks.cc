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

#include "php_v8_callbacks.h"
#include "php_v8_callback_info.h"
#include "php_v8_property_callback_info.h"
#include "php_v8_function_callback_info.h"
#include "php_v8_return_value.h"
#include "php_v8_object.h"

/* begin of type listing */
#include "php_v8_array.h"
#include "php_v8_boolean.h"
#include "php_v8_integer.h"
/* end of type listing */

#include "php_v8_value.h"
#include "php_v8_isolate.h"


php_v8_callbacks_bucket_t *php_v8_callback_create_bucket(size_t size) {

    assert (size > 0);

    php_v8_callbacks_bucket_t *bucket;

    bucket = (php_v8_callbacks_bucket_t *) ecalloc(1, sizeof(*bucket));

    bucket->size = size;
    bucket->cb = (php_v8_callback_t **) ecalloc(size, sizeof(*bucket->cb));

    return bucket;
}


void php_v8_callback_cleanup_bucket(php_v8_callbacks_bucket_t *bucket, size_t index) {
    assert(bucket->size >= index);

    if (bucket->cb[index] == NULL) {
        return;
    }

    if (bucket->cb[index]->fci.size) {
        zval_ptr_dtor(&bucket->cb[index]->fci.function_name);

        if (!Z_ISUNDEF(bucket->cb[index]->object)) {
            zval_ptr_dtor(&bucket->cb[index]->object);
        }
    }

    efree(bucket->cb[index]);
    bucket->cb[index] = NULL;
}

void php_v8_callback_destroy_bucket(php_v8_callbacks_bucket_t *bucket) {
    for (size_t i = 0; i < bucket->size; i++) {
        php_v8_callback_cleanup_bucket(bucket, i);
    }

    efree(bucket->cb);
    efree(bucket);
}

php_v8_callbacks_bucket_t *php_v8_callback_get_or_create_bucket(size_t size, const char *prefix, bool is_symbol, const char *name, php_v8_callbacks_t *callbacks) {
    char *internal_name;

    spprintf(&internal_name, 0, "%s%s%s", prefix, (is_symbol ? "sym_" : "str_"), name);

    php_v8_callbacks_t::iterator it = callbacks->find(internal_name);

    if (it != callbacks->end()) {
        efree(internal_name);

        return it->second;
    }

    php_v8_callbacks_bucket_t *bucket = php_v8_callback_create_bucket(size);

    (*callbacks)[internal_name] = bucket;

    return bucket;
}

void php_v8_callbacks_copy_bucket(php_v8_callbacks_bucket_t *from, php_v8_callbacks_bucket_t *to) {
    for (size_t i = 0; i < from->size; i++) {
        if (from->cb[i]) {
            php_v8_callback_add(i, from->cb[i]->fci, from->cb[i]->fci_cache, to);
        }
    }
}


php_v8_callback_t *php_v8_callback_add(size_t index, zend_fcall_info fci, zend_fcall_info_cache fci_cache, php_v8_callbacks_bucket_t *bucket) {
    assert(bucket->size >= index);

    php_v8_callback_cleanup_bucket(bucket, index);

    php_v8_callback_t *callback = (php_v8_callback_t *) ecalloc(1, sizeof(*callback));

    callback->fci = fci;
    callback->fci_cache = fci_cache;

    if (fci.size) {
        Z_ADDREF(callback->fci.function_name);

        if (fci.object) {
            ZVAL_OBJ(&callback->object, fci.object);
            Z_ADDREF(callback->object);
        }
    }

    bucket->cb[index] = callback;

    return callback;
}

void php_v8_callbacks_cleanup(php_v8_callbacks_t *callbacks) {
    if (callbacks == NULL) {
        return;
    }

    for (php_v8_callbacks_t::iterator it = callbacks->begin(); it != callbacks->end(); ++it) {
        php_v8_callback_destroy_bucket(it->second);
        efree(it->first);
    }
}

void php_v8_callbacks_gc(php_v8_callbacks_t *callbacks, zval **gc_data, int * gc_data_count, zval **table, int *n) {

    int size = php_v8_weak_callbacks_get_count(callbacks);

    if (*gc_data_count < size) {
        *gc_data = (zval *)safe_erealloc(*gc_data, size, sizeof(zval), 0);
    }

    *gc_data_count = size;

    zval *local_gc_data = *gc_data;

    php_v8_weak_callbacks_get_zvals(callbacks, local_gc_data);

    *table = *gc_data;
    *n     = *gc_data_count;
}


int php_v8_callback_get_callback_count(php_v8_callback_t *cb) {
    int size = 0;

    if (!cb) {
        return size;
    }

    if (cb->fci.size) {
        size += 1;

        if (!Z_ISUNDEF(cb->object)) {
            size += 1;
        }
    }

    return size;
}

int php_v8_callback_get_bucket_count(php_v8_callbacks_bucket_t *bucket) {
    int size = 0;

    if (!bucket) {
        return size;
    }

    for (size_t i = 0; i < bucket->size; i++) {
        size += php_v8_callback_get_callback_count(bucket->cb[i]);
    }

    return size;
}

int php_v8_weak_callbacks_get_count(php_v8_callbacks_t *callbacks) {
    int size = 0;

    if (callbacks == NULL || callbacks->empty()) {
        return size;
    }

    for (auto it = callbacks->begin(); it != callbacks->end(); ++it) {
        size += php_v8_callback_get_bucket_count(it->second);
    }

    return size;
}

void php_v8_callback_get_callback_zvals(php_v8_callback_t *cb, zval *& zv) {
    if (!cb) {
        return;
    }

    if (cb->fci.size) {
        ZVAL_COPY_VALUE(zv++, &cb->fci.function_name);

        if (!Z_ISUNDEF(cb->object)) {
            ZVAL_COPY_VALUE(zv++, &cb->object);
        }
    }
}


void php_v8_callback_get_bucket_zvals(php_v8_callbacks_bucket_t *bucket, zval *& zv) {
    if (!bucket) {
        return;
    }

    for (size_t i = 0; i < bucket->size; i++) {
        php_v8_callback_get_callback_zvals(bucket->cb[i], zv);
    }
}

void php_v8_weak_callbacks_get_zvals(php_v8_callbacks_t *callbacks, zval *& zv) {
    if (callbacks == NULL) {
        return;
    }

    for (php_v8_callbacks_t::iterator it = callbacks->begin(); it != callbacks->end(); ++it) {
        php_v8_callback_get_bucket_zvals(it->second, zv);
    }
}

void php_v8_bucket_gc(php_v8_callbacks_bucket_t *bucket, zval **gc_data, int * gc_data_count, zval **table, int *n) {

    int size = php_v8_callback_get_bucket_count(bucket);

    if (*gc_data_count < size) {
        *gc_data = (zval *)safe_erealloc(*gc_data, size, sizeof(zval), 0);
    }

    *gc_data_count = size;

    zval *local_gc_data = *gc_data;

    php_v8_callback_get_bucket_zvals(bucket, local_gc_data);

    *table = *gc_data;
    *n     = *gc_data_count;
}

void php_v8_callback_set_retval_from_callback_info(v8::ReturnValue<void> retval, php_v8_return_value_t *return_value) {
    if (!return_value->type) {
        return;
    }

    switch (return_value->type) {
        default:
            // should never get here, just in case new types will be added in future
            PHP_V8_THROW_EXCEPTION("Failed to set returned value: unsupported type");
            return;
            break;
    }
}

void php_v8_callback_set_retval_from_callback_info(v8::ReturnValue<v8::Value> retval, php_v8_return_value_t *return_value) {
    if (!return_value->type) {
        return;
    }

    switch (return_value->type) {
        case PHP_V8_RETVAL_UNDEFINED:
            retval.SetUndefined();
            break;
        case PHP_V8_RETVAL_NULL:
            retval.SetNull();
            break;
        case PHP_V8_RETVAL_EMPTY_STRING:
            retval.SetEmptyString();
            break;
        case PHP_V8_RETVAL_BOOL:
            retval.Set(return_value->value.set_bool);
            break;
        case PHP_V8_RETVAL_INT32:
            retval.Set(return_value->value.set_int32);
            break;
        case PHP_V8_RETVAL_UINT32:
            retval.Set(return_value->value.set_uint32);
            break;
        case PHP_V8_RETVAL_LONG:
            retval.Set(static_cast<double>(return_value->value.set_long));

            break;
        case PHP_V8_RETVAL_DOUBLE:
            retval.Set(return_value->value.set_double);
            break;
        case PHP_V8_RETVAL_V8_VALUE:
            retval.Set(php_v8_value_get_value_local(retval.GetIsolate(),
                                                    PHP_V8_VALUE_FETCH(&return_value->value.php_v8_value_zv)));
            break;
        default:
            // should never get here, just in case new types will be added in future

            // TODO: maybe value exception?
            PHP_V8_THROW_EXCEPTION("Failed to set returned value: unsupported type");
            return;
            break;
    }
}

void php_v8_callback_set_retval_from_callback_info(v8::ReturnValue<v8::Array> retval, php_v8_return_value_t *return_value) {
    if (!return_value->type) {
        return;
    }

    switch (return_value->type) {
        case PHP_V8_RETVAL_V8_VALUE:
            retval.Set(php_v8_value_get_array_local(retval.GetIsolate(),
                                                   PHP_V8_VALUE_FETCH(&return_value->value.php_v8_value_zv)));
            break;
        default:
            // should never get here, just in case new types will be added in future
            PHP_V8_THROW_EXCEPTION("Failed to set returned value: unsupported type");
            return;
            break;
    }
}

void php_v8_callback_set_retval_from_callback_info(v8::ReturnValue<v8::Integer> retval, php_v8_return_value_t *return_value) {
    if (!return_value->type) {
        return;
    }

    switch (return_value->type) {
        case PHP_V8_RETVAL_INT32:
            retval.Set(return_value->value.set_int32);
            break;
        case PHP_V8_RETVAL_UINT32:
            retval.Set(return_value->value.set_uint32);
            break;
        case PHP_V8_RETVAL_V8_VALUE:
            retval.Set(php_v8_value_get_integer_local(retval.GetIsolate(),
                                                     PHP_V8_VALUE_FETCH(&return_value->value.php_v8_value_zv)));
            break;
        default:
            // should never get here, just in case new types will be added in future
            PHP_V8_THROW_EXCEPTION("Failed to set returned value: unsupported type");
            return;
            break;
    }
}

void php_v8_callback_set_retval_from_callback_info(v8::ReturnValue<v8::Boolean> retval, php_v8_return_value_t *return_value) {
    if (!return_value->type) {
        return;
    }

    switch (return_value->type) {
        case PHP_V8_RETVAL_BOOL:
            retval.Set(return_value->value.set_bool);
            break;
        case PHP_V8_RETVAL_V8_VALUE:
            retval.Set(php_v8_value_get_boolean_local(retval.GetIsolate(),
                                                     PHP_V8_VALUE_FETCH(&return_value->value.php_v8_value_zv)));
            break;
        default:
            // should never get here, just in case new types will be added in future
            PHP_V8_THROW_EXCEPTION("Failed to set returned value: unsupported type");
            return;
            break;
    }
}



void php_v8_callback_call_from_bucket_with_zargs(size_t index, v8::Local<v8::Value> data, zval *args, zval *retval) {
    php_v8_callbacks_bucket_t *bucket;

    if (data.IsEmpty() || !data->IsExternal()) {
        PHP_V8_THROW_EXCEPTION("Callback has no stored callback function");
        return;
    }

    bucket = static_cast<php_v8_callbacks_bucket_t *>(v8::Local<v8::External>::Cast(data)->Value());
    assert(bucket->size > index);

    php_v8_callback_t *cb = bucket->cb[index];

    zend_fcall_info fci = cb->fci;
    zend_fcall_info_cache fci_cache = cb->fci_cache;

    /* Convert everything to be callable */
    zend_fcall_info_args(&fci, args);

    /* Initialize the return persistent pointer */
    zval retval_tmp;
    fci.retval = &retval_tmp;

    /* Call the function */
    if (zend_call_function(&fci, &fci_cache) == SUCCESS && fci.retval && retval != NULL) {
        ZVAL_ZVAL(retval, fci.retval, 1, 1);
    }

    // TODO: what about exceptions? - we let user handle any case of exceptions for themself

    /* Clean up our mess */
    zend_fcall_info_args_clear(&fci, 1);
}

template<class T>
void php_v8_callback_call_from_bucket_with_zargs(size_t index, const T &info, zval *args) {
    zval callback_info;
    php_v8_callback_info_t *php_v8_callback_info;
    // Wrap callback info
    php_v8_callback_info = php_v8_callback_info_create_from_info(&callback_info, info);

    if (!php_v8_callback_info) {
        return;
    }

    add_next_index_zval(args, &callback_info);

    php_v8_callback_call_from_bucket_with_zargs(index, info.Data(), args, NULL);

    php_v8_callback_set_retval_from_callback_info(info.GetReturnValue(), php_v8_callback_info->php_v8_return_value);

    php_v8_callback_info_invalidate(php_v8_callback_info);
}



void php_v8_callback_function(const v8::FunctionCallbackInfo<v8::Value> &info) {
    PHP_V8_DECLARE_ISOLATE_LOCAL_ALIAS(info.GetIsolate());

    zval args;

    /* Build the parameter array */
    array_init_size(&args, 1);

    php_v8_callback_call_from_bucket_with_zargs(0, info, &args);

    zval_ptr_dtor(&args);
}

void php_v8_callback_accessor_name_getter(v8::Local<v8::Name> property, const v8::PropertyCallbackInfo<v8::Value> &info) {
    PHP_V8_DECLARE_ISOLATE_LOCAL_ALIAS(info.GetIsolate());

    zval args;
    zval property_name;

    /* Build the parameter array */
    array_init_size(&args, 2);

    php_v8_get_or_create_value(&property_name, property, isolate);
    add_index_zval(&args, 0, &property_name);

    php_v8_callback_call_from_bucket_with_zargs(0, info, &args);

    zval_ptr_dtor(&args);
}

void php_v8_callback_accessor_name_setter(v8::Local<v8::Name> property, v8::Local<v8::Value> value, const v8::PropertyCallbackInfo<void> &info) {
    PHP_V8_DECLARE_ISOLATE_LOCAL_ALIAS(info.GetIsolate());

    zval args;
    zval property_name;
    zval property_value;

    /* Build the parameter array */
    array_init_size(&args, 3);

    php_v8_get_or_create_value(&property_name, property, isolate);
    php_v8_get_or_create_value(&property_value, value, isolate);

    add_index_zval(&args, 0, &property_name);
    add_index_zval(&args, 1, &property_value);

    php_v8_callback_call_from_bucket_with_zargs(1, info, &args);

    zval_ptr_dtor(&args);
}


void php_v8_callback_generic_named_property_getter(v8::Local<v8::Name> property, const v8::PropertyCallbackInfo<v8::Value> &info) {
    PHP_V8_DECLARE_ISOLATE_LOCAL_ALIAS(info.GetIsolate());

    zval args;
    zval property_name;

    /* Build the parameter array */
    array_init_size(&args, 2);

    php_v8_get_or_create_value(&property_name, property, isolate);
    add_index_zval(&args, 0, &property_name);

    php_v8_callback_call_from_bucket_with_zargs(0, info, &args);

    zval_ptr_dtor(&args);
}

void php_v8_callback_generic_named_property_setter(v8::Local<v8::Name> property, v8::Local<v8::Value> value, const v8::PropertyCallbackInfo<v8::Value> &info) {
    PHP_V8_DECLARE_ISOLATE_LOCAL_ALIAS(info.GetIsolate());

    zval args;
    zval property_name;
    zval property_value;

    /* Build the parameter array */
    array_init_size(&args, 3);

    php_v8_get_or_create_value(&property_name, property, isolate);
    php_v8_get_or_create_value(&property_value, value, isolate);

    add_index_zval(&args, 0, &property_name);
    add_index_zval(&args, 1, &property_value);

    php_v8_callback_call_from_bucket_with_zargs(1, info, &args);

    zval_ptr_dtor(&args);
}

void php_v8_callback_generic_named_property_query(v8::Local<v8::Name> property, const v8::PropertyCallbackInfo<v8::Integer> &info) {
    PHP_V8_DECLARE_ISOLATE_LOCAL_ALIAS(info.GetIsolate());

    zval args;
    zval property_name;

    /* Build the parameter array */
    array_init_size(&args, 2);

    php_v8_get_or_create_value(&property_name, property, isolate);
    add_index_zval(&args, 0, &property_name);

    php_v8_callback_call_from_bucket_with_zargs(2, info, &args);

    zval_ptr_dtor(&args);
}

void php_v8_callback_generic_named_property_deleter(v8::Local<v8::Name> property, const v8::PropertyCallbackInfo<v8::Boolean> &info) {
    PHP_V8_DECLARE_ISOLATE_LOCAL_ALIAS(info.GetIsolate());

    zval args;
    zval property_name;

    /* Build the parameter array */
    array_init_size(&args, 2);

    php_v8_get_or_create_value(&property_name, property, isolate);
    add_index_zval(&args, 0, &property_name);

    php_v8_callback_call_from_bucket_with_zargs(3, info, &args);

    zval_ptr_dtor(&args);
}

void php_v8_callback_generic_named_property_enumerator(const v8::PropertyCallbackInfo<v8::Array> &info) {
    PHP_V8_DECLARE_ISOLATE_LOCAL_ALIAS(info.GetIsolate());

    zval args;

    /* Build the parameter array */
    array_init_size(&args, 1);

    php_v8_callback_call_from_bucket_with_zargs(4, info, &args);

    zval_ptr_dtor(&args);
}


void php_v8_callback_indexed_property_getter(uint32_t index, const v8::PropertyCallbackInfo<v8::Value> &info) {
    PHP_V8_DECLARE_ISOLATE_LOCAL_ALIAS(info.GetIsolate());

    zval args;
    zval property_name;

    /* Build the parameter array */
    array_init_size(&args, 2);

    // Wrap property name
    ZVAL_LONG(&property_name, index);
    add_index_zval(&args, 0, &property_name);

    php_v8_callback_call_from_bucket_with_zargs(0, info, &args);

    zval_ptr_dtor(&args);
}

void php_v8_callback_indexed_property_setter(uint32_t index, v8::Local<v8::Value> value, const v8::PropertyCallbackInfo<v8::Value> &info) {
    PHP_V8_DECLARE_ISOLATE_LOCAL_ALIAS(info.GetIsolate());

    zval args;
    zval property_name;
    zval property_value;

    /* Build the parameter array */
    array_init_size(&args, 3);

    ZVAL_LONG(&property_name, index);
    php_v8_get_or_create_value(&property_value, value, isolate);

    add_index_zval(&args, 0, &property_name);
    add_index_zval(&args, 1, &property_value);

    php_v8_callback_call_from_bucket_with_zargs(1, info, &args);

    zval_ptr_dtor(&args);
}

void php_v8_callback_indexed_property_query(uint32_t index, const v8::PropertyCallbackInfo<v8::Integer> &info) {
    PHP_V8_DECLARE_ISOLATE_LOCAL_ALIAS(info.GetIsolate());

    zval args;
    zval property_name;

    /* Build the parameter array */
    array_init_size(&args, 2);

    ZVAL_LONG(&property_name, index);
    add_index_zval(&args, 0, &property_name);

    php_v8_callback_call_from_bucket_with_zargs(2, info, &args);

    zval_ptr_dtor(&args);
}

void php_v8_callback_indexed_property_deleter(uint32_t index, const v8::PropertyCallbackInfo<v8::Boolean> &info) {
    PHP_V8_DECLARE_ISOLATE_LOCAL_ALIAS(info.GetIsolate());

    zval args;
    zval property_name;

    /* Build the parameter array */
    array_init_size(&args, 2);

    ZVAL_LONG(&property_name, index);
    add_index_zval(&args, 0, &property_name);

    php_v8_callback_call_from_bucket_with_zargs(3, info, &args);

    zval_ptr_dtor(&args);
}

void php_v8_callback_indexed_property_enumerator(const v8::PropertyCallbackInfo<v8::Array> &info) {
    PHP_V8_DECLARE_ISOLATE_LOCAL_ALIAS(info.GetIsolate());

    zval args;

    /* Build the parameter array */
    array_init_size(&args, 1);

    php_v8_callback_call_from_bucket_with_zargs(4, info, &args);

    zval_ptr_dtor(&args);
}

bool php_v8_callback_access_check(v8::Local<v8::Context> accessing_context, v8::Local<v8::Object> accessed_object, v8::Local<v8::Value> data) {
    PHP_V8_DECLARE_ISOLATE_LOCAL_ALIAS(v8::Isolate::GetCurrent());

    PHP_V8_THROW_EXCEPTION("Broken due to problem (see https://groups.google.com/forum/?fromgroups#!topic/v8-dev/c7LhW2bNabY)");
    return false;

    zval args;
    zval accessed_object_zv;
    zval retval;

    bool security_retval = false;

    ZVAL_BOOL(&retval, false);

    array_init_size(&args, 2);

    php_v8_context_t *php_v8_context = php_v8_context_get_reference(accessing_context);

    assert(NULL != php_v8_context);

    php_v8_get_or_create_value(&accessed_object_zv, accessed_object, isolate);

    add_index_zval(&args, 0, &php_v8_context->this_ptr);
    add_index_zval(&args, 1, &accessed_object_zv);

    php_v8_callback_call_from_bucket_with_zargs(0, data, &args, &retval);

    if (Z_TYPE(retval) == IS_TRUE) {
        security_retval = true;
    }

    zval_ptr_dtor(&args);
    zval_ptr_dtor(&retval);

    return security_retval;
}
