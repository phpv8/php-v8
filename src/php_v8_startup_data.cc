/*
 * This file is part of the phpv8/php-v8 PHP extension.
 *
 * Copyright (c) 2015-2018 Bogdan Padalko <thepinepain@gmail.com>
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

#include "php_v8_startup_data.h"
#include "php_v8_exceptions.h"
#include "php_v8_script_compiler.h"
#include "php_v8_a.h"
#include "php_v8.h"
#include "zend_smart_str.h"


zend_class_entry *php_v8_startup_data_class_entry;
#define this_ce php_v8_startup_data_class_entry

static zend_object_handlers php_v8_startup_data_object_handlers;

extern script_compiler_tag php_v8_startup_data_get_current_tag() {
    script_compiler_tag tag = {PHP_V8_SCRIPT_COMPILER_TAG_MAGIC, v8::ScriptCompiler::CachedDataVersionTag()};
    return tag;
}

void php_v8_startup_data_create(zval *return_value, v8::StartupData *blob) {
    object_init_ex(return_value, this_ce);

    PHP_V8_STARTUP_DATA_FETCH_INTO(return_value, php_v8_startup_data);

    script_compiler_tag version = php_v8_startup_data_get_current_tag();

    const char *blob_data = blob->data;

    blob->data = (const char *) estrndup(blob->data, blob->raw_size);

    delete []blob_data;

    php_v8_startup_data->blob = new phpv8::StartupData(blob, version);
}

static void php_v8_startup_data_free(zend_object *object) {
    php_v8_startup_data_t *php_v8_startup_data = php_v8_startup_data_fetch_object(object);

    if (php_v8_startup_data->blob && php_v8_startup_data->blob->release()) {
        delete php_v8_startup_data->blob;
    }
    php_v8_startup_data->blob = nullptr;

    zend_object_std_dtor(&php_v8_startup_data->std);
}


static zend_object *php_v8_startup_data_ctor(zend_class_entry *ce) {
    php_v8_startup_data_t *php_v8_startup_data;

    php_v8_startup_data = (php_v8_startup_data_t *) ecalloc(1, sizeof(php_v8_startup_data_t) + zend_object_properties_size(ce));

    zend_object_std_init(&php_v8_startup_data->std, ce);
    object_properties_init(&php_v8_startup_data->std, ce);

    php_v8_init();

    php_v8_startup_data->std.handlers = &php_v8_startup_data_object_handlers;

    return &php_v8_startup_data->std;
}

static PHP_METHOD(StartupData, __construct) {
    zend_string *blob_string = NULL;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "S", &blob_string) == FAILURE) {
        return;
    }

    if (ZSTR_LEN(blob_string) > INT_MAX + sizeof(script_compiler_tag)) {
        PHP_V8_THROW_EXCEPTION("Invalid startup blob (too large)");
        return;
    }

    if (ZSTR_LEN(blob_string) < sizeof(script_compiler_tag)) {
        PHP_V8_THROW_EXCEPTION("Invalid startup blob (too small)");
        return;
    }

    script_compiler_tag version = {};
    memcpy(&version, &ZSTR_VAL(blob_string)[0], sizeof(script_compiler_tag));

    PHP_V8_STARTUP_DATA_FETCH_INTO(getThis(), php_v8_startup_data);

    v8::StartupData *blob = new v8::StartupData();

    blob->data     = (const char *) estrndup(&ZSTR_VAL(blob_string)[sizeof(script_compiler_tag)], ZSTR_LEN(blob_string) - sizeof(script_compiler_tag));
    blob->raw_size = static_cast<int>(ZSTR_LEN(blob_string) - sizeof(script_compiler_tag));

    php_v8_startup_data->blob = new phpv8::StartupData(blob, version);
}

static PHP_METHOD(StartupData, getData) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_STARTUP_DATA_FETCH_INTO(getThis(), php_v8_startup_data);

    if (php_v8_startup_data->blob && php_v8_startup_data->blob->hasData()) {
        script_compiler_tag loaded = php_v8_startup_data->blob->version();
        smart_str my_str = {0};

        smart_str_appendl(&my_str, (char *)&loaded, sizeof(script_compiler_tag));
        smart_str_appendl(&my_str, php_v8_startup_data->blob->data()->data, static_cast<size_t>(php_v8_startup_data->blob->data()->raw_size));
        smart_str_0(&my_str);

        zend_string *out = zend_string_copy(my_str.s);
        smart_str_free(&my_str);

        RETURN_STR(out);
    }

    RETURN_EMPTY_STRING();
}

static PHP_METHOD(StartupData, isRejected) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_STARTUP_DATA_FETCH_INTO(getThis(), php_v8_startup_data);

    if (php_v8_startup_data->blob) {
        RETURN_BOOL(php_v8_startup_data->blob->rejected());
    }

    RETURN_BOOL(false);
}

static PHP_METHOD(StartupData, createFromSource) {
    zend_string *blob = NULL;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "S", &blob) == FAILURE) {
        return;
    }

    /* we can't try-catch here while we have no isolate yet */

    const char *source = ZSTR_VAL(blob);
    php_v8_init();

    v8::StartupData *startup_blob = new v8::StartupData();

    *startup_blob = v8::V8::CreateSnapshotDataBlob(source);

    if (startup_blob->data == NULL) {
        PHP_V8_THROW_EXCEPTION("Failed to create startup blob");
        return;
    }

    php_v8_startup_data_create(return_value, startup_blob);
}

static PHP_METHOD(StartupData, warmUpSnapshotDataBlob) {
    zval *php_cold_data_zv;
    zend_string *source = NULL;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "oS", &php_cold_data_zv, &source) == FAILURE) {
        return;
    }

    PHP_V8_STARTUP_DATA_FETCH_INTO(php_cold_data_zv, php_v8_cold_data);

    /* we can't try-catch here while we have no isolate yet */

    const char *warmup_source = ZSTR_VAL(source);
    php_v8_init();

    v8::StartupData *warm_data = new v8::StartupData();

    *warm_data = v8::V8::WarmUpSnapshotDataBlob(*php_v8_cold_data->blob->data(), warmup_source);

    if (warm_data->data == NULL) {
        PHP_V8_THROW_EXCEPTION("Failed to warm up snapshot");
        return;
    }

    php_v8_startup_data_create(return_value, warm_data);
}


PHP_V8_ZEND_BEGIN_ARG_WITH_CONSTRUCTOR_INFO_EX(arginfo___construct, 1)
                ZEND_ARG_TYPE_INFO(0, blob, IS_STRING, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_getData, ZEND_RETURN_VALUE, 0, IS_STRING, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_isRejected, ZEND_RETURN_VALUE, 0, _IS_BOOL, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_createFromSource, ZEND_RETURN_VALUE, 1, V8\\StartupData, 0)
                ZEND_ARG_TYPE_INFO(0, source, IS_STRING, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_warmUpSnapshotDataBlob, ZEND_RETURN_VALUE, 2, V8\\StartupData, 0)
                ZEND_ARG_OBJ_INFO(0, cold_startup_data, V8\\StartupData, 0)
                ZEND_ARG_TYPE_INFO(0, warmup_source, IS_STRING, 0)
ZEND_END_ARG_INFO()


static const zend_function_entry php_v8_startup_data_methods[] = {
        PHP_V8_ME(StartupData, __construct,            ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)
        PHP_V8_ME(StartupData, getData,                ZEND_ACC_PUBLIC)
        PHP_V8_ME(StartupData, isRejected,             ZEND_ACC_PUBLIC)
        PHP_V8_ME(StartupData, createFromSource,       ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
        PHP_V8_ME(StartupData, warmUpSnapshotDataBlob, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)

        PHP_FE_END
};


PHP_MINIT_FUNCTION (php_v8_startup_data) {
    zend_class_entry ce;
    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS, "StartupData", php_v8_startup_data_methods);
    this_ce = zend_register_internal_class(&ce);
    this_ce->create_object = php_v8_startup_data_ctor;

    memcpy(&php_v8_startup_data_object_handlers, zend_get_std_object_handlers(), sizeof(zend_object_handlers));

    php_v8_startup_data_object_handlers.offset    = XtOffsetOf(php_v8_startup_data_t, std);
    php_v8_startup_data_object_handlers.free_obj  = php_v8_startup_data_free;
    php_v8_startup_data_object_handlers.clone_obj = NULL;

    return SUCCESS;
}
