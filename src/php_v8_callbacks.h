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

namespace phpv8 {
    class Callback;
    class CallbacksBucket;
    class PersistentData;
    template <class T> class PersistentCollection;
}


#include <v8.h>
#include <limits>
#include <map>
#include <string>
#include <utility>

extern "C" {
#include "php.h"

#ifdef ZTS
#include "TSRM.h"
#endif
}


extern void php_v8_callbacks_gc(phpv8::PersistentData *data, zval **gc_data, int * gc_data_count, zval **table, int *n);

extern void php_v8_bucket_gc(phpv8::CallbacksBucket *bucket, zval **gc_data, int * gc_data_count, zval **table, int *n);

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

//#define PHP_V8_DEBUG_EXTERNAL_MEM 1

#ifdef PHP_V8_DEBUG_EXTERNAL_MEM
#define php_v8_debug_external_mem(format, ...) fprintf(stderr, (format), ##__VA_ARGS__);
#else
#define php_v8_debug_external_mem(format, ...)
#endif

namespace phpv8 {

    class Callback {
    public:
        Callback(zend_fcall_info fci, zend_fcall_info_cache fci_cache);
        ~Callback();
        int getGcCount();
        void collectGcZvals(zval *& zv);

        inline zend_fcall_info fci() {
            return fci_;
        }

        inline zend_fcall_info_cache fci_cache() {
            return fci_cache_;
        }

    private:
        zval object_;
        zend_fcall_info fci_;
        zend_fcall_info_cache fci_cache_;
    };


    class CallbacksBucket {
    public:
        phpv8::Callback *get(size_t index);
        void reset(CallbacksBucket *bucket);

        void add(size_t index, zend_fcall_info fci, zend_fcall_info_cache fci_cache);
        int getGcCount();

        void collectGcZvals(zval *& zv);

        inline bool empty() {
            return callbacks.empty();
        }

        inline int64_t calculateSize() {
            return sizeof(*this) + (sizeof(std::shared_ptr<Callback>) + sizeof(Callback)) * callbacks.size();
        }

    private:
        std::map<size_t, std::shared_ptr<Callback>> callbacks;
    };


    class PersistentData {
    public:
        int getGcCount();
        void collectGcZvals(zval *& zv);
        CallbacksBucket *bucket(const char *prefix, bool is_symbol, const char *name);

        inline CallbacksBucket *bucket(const char *name) {
            return bucket("", false, name);
        }

        inline bool empty() {
            return buckets.empty();
        }

        inline int64_t getTotalSize() {
            if (!size_) {
                // TODO: if adjusted_size_ is going to be much larger than estimated calculateSize() value,
                //       we can ignore calculateSize() without loosing idea to notify v8 about
                //       significant external memory pressure
                size_ = calculateSize() + adjusted_size_;

                if (size_ < 0) {
                    size_ = std::numeric_limits<int64_t>::max();
                }
            }

            return size_;
        }

        inline int64_t getAdjustedSize() {
            return adjusted_size_;
        }

        int64_t adjustSize(int64_t change_in_bytes);

    protected:
        int64_t calculateSize();
    private:
        int64_t size_;
        int64_t adjusted_size_;
        std::map<std::string, std::shared_ptr<CallbacksBucket>> buckets;
    };


    template <class T>
    class PersistentCollection {
    public:
        ~PersistentCollection() {
            for (auto const &item : collection) {
                item.first->Reset();
                delete item.first;
            }
        }

        int getGcCount() {
            int size = 0;

            for (auto const &item : collection) {
                size += item.second->getGcCount();
            }

            return size;
        }

        void collectGcZvals(zval *& zv) {
            for (auto const &item : collection) {
                item.second->collectGcZvals(zv);
            }
        }

        void add(v8::Persistent<T> *persistent, phpv8::PersistentData *data) {
            collection[persistent] = std::shared_ptr<phpv8::PersistentData>(data);
        }

        phpv8::PersistentData *get(v8::Persistent<T> *persistent) {
            auto it = collection.find(persistent);

            if (it != collection.end()) {
                return it->second.get();
            }

            return nullptr;
        }

        void remove(v8::Persistent<T, v8::NonCopyablePersistentTraits<T>> *persistent) {
            collection.erase(persistent);
        }
    private:
        std::map<v8::Persistent<T> *, std::shared_ptr<phpv8::PersistentData>> collection;
    };
}


#endif //PHP_V8_CALLBACKS_H
