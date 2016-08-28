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
#include <string>
#include <algorithm>

namespace phpv8 {

    Callback::Callback(zend_fcall_info fci, zend_fcall_info_cache fci_cache) : fci_(fci), fci_cache_(fci_cache) {
        if (fci_.size) {
            Z_ADDREF(fci_.function_name);

            if (fci_.object) {
                ZVAL_OBJ(&object_, fci_.object);
                Z_ADDREF(object_);
            } else {
                ZVAL_UNDEF(&object_);
            }
        }
    }

    Callback::~Callback() {
        if (fci_.size) {
            zval_ptr_dtor(&fci_.function_name);

            if (!Z_ISUNDEF(object_)) {
                zval_ptr_dtor(&object_);
            }
        }
    }

    int Callback::getGcCount() {
        int size = 0;

        if (fci_.size) {
            size += 1;

            if (!Z_ISUNDEF(object_)) {
                size += 1;
            }
        }

        return size;
    }

    void Callback::collectGcZvals(zval *&zv) {
        if (fci_.size) {
            ZVAL_COPY_VALUE(zv++, &fci_.function_name);

            if (!Z_ISUNDEF(object_)) {
                ZVAL_COPY_VALUE(zv++, &object_);
            }
        }
    }

    void CallbacksBucket::reset(CallbacksBucket *bucket) {
        callbacks.clear();

        for (auto const &item : bucket->callbacks) {
            callbacks[item.first] = item.second;
        }
    }

    phpv8::Callback *CallbacksBucket::get(size_t index) {
        auto it = callbacks.find(index);

        if (it != callbacks.end()) {
            return it->second.get();
        }

        return NULL;
    }

    void CallbacksBucket::add(size_t index, zend_fcall_info fci, zend_fcall_info_cache fci_cache) {
        callbacks[index] = std::make_shared<Callback>(fci, fci_cache);
    }

    int CallbacksBucket::getGcCount() {
        int size = 0;

        for (auto const &item : callbacks) {
            size += item.second->getGcCount();
        }

        return size;
    }

    void CallbacksBucket::collectGcZvals(zval *&zv) {
        for (auto const &item : callbacks) {
            item.second->collectGcZvals(zv);
        }
    }

    int PersistentData::getGcCount() {
        int size = 0;

        for (auto const &item : buckets) {
            size += item.second->getGcCount();
        }

        return size;
    }

    void PersistentData::collectGcZvals(zval *&zv) {
        for (auto const &item : buckets) {
            item.second->collectGcZvals(zv);
        }
    }

    CallbacksBucket *PersistentData::bucket(const char *prefix, bool is_symbol, const char *name) {
        char *internal_name;

        size_t size = spprintf(&internal_name, 0, "%s%s%s", prefix, (is_symbol ? "sym_" : "str_"), name);

        std::string str_name(internal_name, size);
        efree(internal_name);

        auto it = buckets.find(str_name);

        if (it != buckets.end()) {
            return it->second.get();
        }

        auto bucket = std::make_shared<CallbacksBucket>();
        buckets[str_name] = bucket;

        return bucket.get();
    }

    int64_t PersistentData::calculateSize() {
        int64_t size = sizeof(*this);

        for (auto const &item : buckets) {
            size += sizeof(std::shared_ptr<CallbacksBucket>);
            size += item.first.capacity();
            size += item.second->calculateSize();
        }

        return size;
    }

    int64_t PersistentData::adjustSize(int64_t change_in_bytes) {
        adjusted_size_ = std::max(static_cast<int64_t>(0), adjusted_size_ + change_in_bytes);
        return adjusted_size_;
    }
}

void php_v8_callbacks_gc(phpv8::PersistentData *data, zval **gc_data, int * gc_data_count, zval **table, int *n) {

    int size = data->getGcCount();

    if (*gc_data_count < size) {
        *gc_data = (zval *)safe_erealloc(*gc_data, size, sizeof(zval), 0);
    }

    *gc_data_count = size;

    zval *local_gc_data = *gc_data;

    data->collectGcZvals(local_gc_data);

    *table = *gc_data;
    *n     = *gc_data_count;
}

void php_v8_bucket_gc(phpv8::CallbacksBucket *bucket, zval **gc_data, int * gc_data_count, zval **table, int *n) {

    int size = bucket->getGcCount();

    if (*gc_data_count < size) {
        *gc_data = (zval *)safe_erealloc(*gc_data, size, sizeof(zval), 0);
    }

    *gc_data_count = size;

    zval *local_gc_data = *gc_data;

    bucket->collectGcZvals(local_gc_data);

    *table = *gc_data;
    *n     = *gc_data_count;
}

static inline void php_v8_callback_set_retval_from_callback_info(v8::ReturnValue<void> *rv, php_v8_return_value_t *php_v8_return_value) {
    php_v8_return_value->accepts = PHP_V8_RETVAL_ACCEPTS_VOID;
    php_v8_return_value->rv_void = rv;
}

static inline void php_v8_callback_set_retval_from_callback_info(v8::ReturnValue<v8::Value> *rv, php_v8_return_value_t *php_v8_return_value) {
    php_v8_return_value->accepts = PHP_V8_RETVAL_ACCEPTS_ANY;
    php_v8_return_value->rv_any = rv;
}

static inline void php_v8_callback_set_retval_from_callback_info(v8::ReturnValue<v8::Integer> *rv, php_v8_return_value_t *php_v8_return_value) {
    php_v8_return_value->accepts = PHP_V8_RETVAL_ACCEPTS_INTEGER;
    php_v8_return_value->rv_integer = rv;
}

static inline void php_v8_callback_set_retval_from_callback_info(v8::ReturnValue<v8::Boolean> *rv, php_v8_return_value_t *php_v8_return_value) {
    php_v8_return_value->accepts = PHP_V8_RETVAL_ACCEPTS_BOOLEAN;
    php_v8_return_value->rv_boolean = rv;
}

static inline void php_v8_callback_set_retval_from_callback_info(v8::ReturnValue<v8::Array> *rv, php_v8_return_value_t *php_v8_return_value) {
    php_v8_return_value->accepts = PHP_V8_RETVAL_ACCEPTS_ARRAY;
    php_v8_return_value->rv_array = rv;
}


void php_v8_callback_call_from_bucket_with_zargs(size_t index, v8::Local<v8::Value> data, zval *args, zval *retval) {
    phpv8::CallbacksBucket *bucket;

    if (data.IsEmpty() || !data->IsExternal()) {
        PHP_V8_THROW_EXCEPTION("Callback has no stored callback function");
        return;
    }

    bucket = static_cast<phpv8::CallbacksBucket *>(v8::Local<v8::External>::Cast(data)->Value());

    phpv8::Callback *cb = bucket->get(index);

    // highly unlikely, but to play safe
    if (!cb) {
        PHP_V8_THROW_EXCEPTION("Callback has no stored callback function");
        return;
    }

    zend_fcall_info fci = cb->fci();
    zend_fcall_info_cache fci_cache = cb->fci_cache();

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

template<class T, class M>
void php_v8_callback_call_from_bucket_with_zargs(size_t index, const T &info, M rv, zval *args) {
    zval callback_info;
    php_v8_callback_info_t *php_v8_callback_info;
    // Wrap callback info
    php_v8_callback_info = php_v8_callback_info_create_from_info(&callback_info, info);

    if (!php_v8_callback_info) {
        return;
    }

    add_next_index_zval(args, &callback_info);

    php_v8_callback_set_retval_from_callback_info(&rv, php_v8_callback_info->php_v8_return_value);

    php_v8_callback_call_from_bucket_with_zargs(index, info.Data(), args, NULL);

    php_v8_callback_info_invalidate(php_v8_callback_info);
}


void php_v8_callback_function(const v8::FunctionCallbackInfo<v8::Value> &info) {
    PHP_V8_DECLARE_ISOLATE_LOCAL_ALIAS(info.GetIsolate());

    zval args;

    /* Build the parameter array */
    array_init_size(&args, 1);

    php_v8_callback_call_from_bucket_with_zargs(0, info, info.GetReturnValue(), &args);

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

    php_v8_callback_call_from_bucket_with_zargs(0, info, info.GetReturnValue(), &args);

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

    php_v8_callback_call_from_bucket_with_zargs(1, info, info.GetReturnValue(), &args);

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

    php_v8_callback_call_from_bucket_with_zargs(0, info, info.GetReturnValue(), &args);

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

    php_v8_callback_call_from_bucket_with_zargs(1, info, info.GetReturnValue(), &args);

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

    php_v8_callback_call_from_bucket_with_zargs(2, info, info.GetReturnValue(), &args);

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

    php_v8_callback_call_from_bucket_with_zargs(3, info, info.GetReturnValue(), &args);

    zval_ptr_dtor(&args);
}

void php_v8_callback_generic_named_property_enumerator(const v8::PropertyCallbackInfo<v8::Array> &info) {
    PHP_V8_DECLARE_ISOLATE_LOCAL_ALIAS(info.GetIsolate());

    zval args;

    /* Build the parameter array */
    array_init_size(&args, 1);

    php_v8_callback_call_from_bucket_with_zargs(4, info, info.GetReturnValue(), &args);

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

    php_v8_callback_call_from_bucket_with_zargs(0, info, info.GetReturnValue(), &args);

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

    php_v8_callback_call_from_bucket_with_zargs(1, info, info.GetReturnValue(), &args);

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

    php_v8_callback_call_from_bucket_with_zargs(2, info, info.GetReturnValue(), &args);

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

    php_v8_callback_call_from_bucket_with_zargs(3, info, info.GetReturnValue(), &args);

    zval_ptr_dtor(&args);
}

void php_v8_callback_indexed_property_enumerator(const v8::PropertyCallbackInfo<v8::Array> &info) {
    PHP_V8_DECLARE_ISOLATE_LOCAL_ALIAS(info.GetIsolate());

    zval args;

    /* Build the parameter array */
    array_init_size(&args, 1);

    php_v8_callback_call_from_bucket_with_zargs(4, info, info.GetReturnValue(), &args);

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
