/*
 * This file is part of the pinepain/php-v8 PHP extension.
 *
 * Copyright (c) 2015-2018 Bogdan Padalko <pinepain@gmail.com>
 *
 * Licensed under the MIT license: http://opensource.org/licenses/MIT
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source or visit
 * http://opensource.org/licenses/MIT
 */

#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include "php_v8_indexed_property_handler_configuration.h"
#include "php_v8_named_property_handler_configuration.h"
#include "php_v8_enums.h"
#include "php_v8.h"

zend_class_entry* php_v8_indexed_property_handler_configuration_class_entry;
#define this_ce php_v8_indexed_property_handler_configuration_class_entry

static zend_object_handlers php_v8_indexed_property_handler_configuration_object_handlers;


static HashTable * php_v8_indexed_property_handler_configuration_gc(zval *object, zval **table, int *n) {
    PHP_V8_INDEXED_PROPERTY_HANDLER_FETCH_INTO(object, php_v8_handlers);

    php_v8_bucket_gc(php_v8_handlers->bucket, &php_v8_handlers->gc_data, &php_v8_handlers->gc_data_count, table, n);

    return zend_std_get_properties(object);
}

static void php_v8_indexed_property_handler_configuration_free(zend_object *object) {
    php_v8_indexed_property_handler_configuration_t *php_v8_handler = php_v8_indexed_property_handler_configuration_fetch_object(object);

    if (php_v8_handler->bucket) {
        delete php_v8_handler->bucket;
        php_v8_handler->bucket = NULL;
    }

    if (php_v8_handler->gc_data) {
        efree(php_v8_handler->gc_data);
    }

    zend_object_std_dtor(&php_v8_handler->std);
}


static zend_object * php_v8_indexed_property_handler_configuration_ctor(zend_class_entry *ce) {

    php_v8_indexed_property_handler_configuration_t *php_v8_handler;

    php_v8_handler = (php_v8_indexed_property_handler_configuration_t *) ecalloc(1, sizeof(php_v8_indexed_property_handler_configuration_t) + zend_object_properties_size(ce));

    zend_object_std_init(&php_v8_handler->std, ce);
    object_properties_init(&php_v8_handler->std, ce);

    php_v8_handler->bucket = new phpv8::CallbacksBucket();

    php_v8_handler->std.handlers = &php_v8_indexed_property_handler_configuration_object_handlers;

    return &php_v8_handler->std;
}

static PHP_METHOD(IndexedPropertyHandlerConfiguration, __construct) {
    zend_fcall_info fci_getter = empty_fcall_info;
    zend_fcall_info_cache fci_cache_getter = empty_fcall_info_cache;

    zend_fcall_info fci_setter = empty_fcall_info;
    zend_fcall_info_cache fci_cache_setter = empty_fcall_info_cache;

    zend_fcall_info fci_query = empty_fcall_info;
    zend_fcall_info_cache fci_cache_query = empty_fcall_info_cache;

    zend_fcall_info fci_deleter = empty_fcall_info;
    zend_fcall_info_cache fci_cache_deleter = empty_fcall_info_cache;

    zend_fcall_info fci_enumerator = empty_fcall_info;
    zend_fcall_info_cache fci_cache_enumerator = empty_fcall_info_cache;

    zend_long flags = 0;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "f|f!f!f!f!l",
                              &fci_getter, &fci_cache_getter,
                              &fci_setter, &fci_cache_setter,
                              &fci_query, &fci_cache_query,
                              &fci_deleter, &fci_cache_deleter,
                              &fci_enumerator, &fci_cache_enumerator,
                              &flags
    ) == FAILURE) {
        return;
    }

    PHP_V8_INDEXED_PROPERTY_HANDLER_FETCH_INTO(getThis(), php_v8_handlers);

    php_v8_handlers->bucket->add(phpv8::CallbacksBucket::Index::Getter, fci_getter, fci_cache_getter);
    php_v8_handlers->getter = php_v8_callback_indexed_property_getter;

    if (fci_setter.size) {
        php_v8_handlers->bucket->add(phpv8::CallbacksBucket::Index::Setter, fci_setter, fci_cache_setter);
        php_v8_handlers->setter = php_v8_callback_indexed_property_setter;
    }

    if (fci_query.size) {
        php_v8_handlers->bucket->add(phpv8::CallbacksBucket::Index::Query, fci_query, fci_cache_query);
        php_v8_handlers->query = php_v8_callback_indexed_property_query;
    }

    if (fci_deleter.size) {
        php_v8_handlers->bucket->add(phpv8::CallbacksBucket::Index::Deleter, fci_deleter, fci_cache_deleter);
        php_v8_handlers->deleter = php_v8_callback_indexed_property_deleter;
    }

    if (fci_enumerator.size) {
        php_v8_handlers->bucket->add(phpv8::CallbacksBucket::Index::Enumerator, fci_enumerator, fci_cache_enumerator);
        php_v8_handlers->enumerator = php_v8_callback_indexed_property_enumerator;
    }

    php_v8_handlers->flags = static_cast<long>(flags ? flags & PHP_V8_PROPERTY_HANDLER_FLAGS : flags);
}


PHP_V8_ZEND_BEGIN_ARG_WITH_CONSTRUCTOR_INFO_EX(arginfo___construct, 1)
                ZEND_ARG_CALLABLE_INFO(0, getter, 0)
                ZEND_ARG_CALLABLE_INFO(0, setter, 1)
                ZEND_ARG_CALLABLE_INFO(0, query, 1)
                ZEND_ARG_CALLABLE_INFO(0, deleter, 1)
                ZEND_ARG_CALLABLE_INFO(0, enumerator, 1)
                ZEND_ARG_TYPE_INFO(0, flags, IS_LONG, 0)
ZEND_END_ARG_INFO()


static const zend_function_entry php_v8_indexed_property_handler_configuration_methods[] = {
        PHP_V8_ME(IndexedPropertyHandlerConfiguration, __construct, ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)

        PHP_FE_END
};


PHP_MINIT_FUNCTION(php_v8_indexed_property_handler_configuration) {
    zend_class_entry ce;
    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS, "IndexedPropertyHandlerConfiguration", php_v8_indexed_property_handler_configuration_methods);
    this_ce = zend_register_internal_class(&ce);
    this_ce->create_object = php_v8_indexed_property_handler_configuration_ctor;

    memcpy(&php_v8_indexed_property_handler_configuration_object_handlers, zend_get_std_object_handlers(), sizeof(zend_object_handlers));

    php_v8_indexed_property_handler_configuration_object_handlers.offset    = XtOffsetOf(php_v8_indexed_property_handler_configuration_t, std);
    php_v8_indexed_property_handler_configuration_object_handlers.free_obj  = php_v8_indexed_property_handler_configuration_free;
    php_v8_indexed_property_handler_configuration_object_handlers.get_gc    = php_v8_indexed_property_handler_configuration_gc;
    php_v8_indexed_property_handler_configuration_object_handlers.clone_obj = NULL;

    return SUCCESS;
}
