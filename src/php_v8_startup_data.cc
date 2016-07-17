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

#include "php_v8_startup_data.h"
#include "php_v8_exceptions.h"
#include "php_v8_a.h"
#include "php_v8.h"


zend_class_entry *php_v8_startup_data_class_entry;
#define this_ce php_v8_startup_data_class_entry

static zend_object_handlers php_v8_startup_data_object_handlers;


php_v8_startup_data_t * php_v8_startup_data_fetch_object(zend_object *obj) {
    return (php_v8_startup_data_t *) ((char *) obj - XtOffsetOf(php_v8_startup_data_t, std));
}

void php_v8_startup_data_create(zval *return_value, v8::StartupData *blob) {
    object_init_ex(return_value, this_ce);

    PHP_V8_STARTUP_DATA_FETCH_INTO(return_value, php_v8_startup_data);

    php_v8_startup_data->blob = blob;
}

static void php_v8_startup_data_free(zend_object *object) {
    php_v8_startup_data_t *php_v8_startup_data = php_v8_startup_data_fetch_object(object);

    if (php_v8_startup_data->blob) {
        delete php_v8_startup_data->blob;
    }

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

static PHP_METHOD(V8StartupData, __construct) {
    zend_string *blob = NULL;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "S", &blob) == FAILURE) {
        return;
    }

    if (ZSTR_LEN(blob) > INT_MAX) {
        zend_throw_exception(php_v8_generic_exception_class_entry, "Failed to create startup blob due to blob size integer overflow", 0);
        return;
    }

    if (!ZSTR_LEN(blob)) {
        return;
    }

    PHP_V8_STARTUP_DATA_FETCH_INTO(getThis(), php_v8_startup_data);

    php_v8_startup_data->blob = new v8::StartupData();

    php_v8_startup_data->blob->data = (const char *) estrndup(ZSTR_VAL(blob), ZSTR_LEN(blob));
    php_v8_startup_data->blob->raw_size = static_cast<int>(ZSTR_LEN(blob));
}

static PHP_METHOD(V8StartupData, GetData) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_STARTUP_DATA_FETCH_INTO(getThis(), php_v8_startup_data);

    if (php_v8_startup_data->blob->data != NULL) {
        zend_string *out = zend_string_init(php_v8_startup_data->blob->data, static_cast<size_t>(php_v8_startup_data->blob->raw_size), 0);
        RETURN_STR(out);
    }

    RETURN_EMPTY_STRING();
}

static PHP_METHOD(V8StartupData, GetRawSize) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_STARTUP_DATA_FETCH_INTO(getThis(), php_v8_startup_data);

    RETVAL_LONG(static_cast<zend_long>(php_v8_startup_data->blob->raw_size));
}

static PHP_METHOD(V8StartupData, CreateFromSource) {
    zend_string *blob = NULL;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "S", &blob) == FAILURE) {
        return;
    }
    /* we can't try-catch here while we have no isolate yet */
    // TODO: test to create blob from invalid source

    const char *source = ZSTR_VAL(blob);
    php_v8_init();

    v8::StartupData *startup_blob = new v8::StartupData;

    *startup_blob = v8::V8::CreateSnapshotDataBlob(source);

    if (startup_blob->data == NULL) {
        PHP_V8_THROW_EXCEPTION("Failed to create startup blob");
        return;
    }

    php_v8_startup_data_create(return_value, startup_blob);
}


ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_startup_data___construct, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 1)
                ZEND_ARG_TYPE_INFO(0, blob, IS_STRING, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_startup_data_GetData, ZEND_RETURN_VALUE, 0, IS_STRING, NULL, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_startup_data_GetRawSize, ZEND_RETURN_VALUE, 0, IS_LONG, NULL, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_startup_data_CreateFromSource, ZEND_RETURN_VALUE, 1, IS_OBJECT, PHP_V8_NS "\\StartupData", 0)
                ZEND_ARG_TYPE_INFO(0, source, IS_STRING, 0)
ZEND_END_ARG_INFO()


static const zend_function_entry php_v8_startup_data_methods[] = {
        PHP_ME(V8StartupData, __construct, arginfo_v8_startup_data___construct, ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)

        PHP_ME(V8StartupData, GetData, arginfo_v8_startup_data_GetData, ZEND_ACC_PUBLIC)
        PHP_ME(V8StartupData, GetRawSize, arginfo_v8_startup_data_GetRawSize, ZEND_ACC_PUBLIC)

        PHP_ME(V8StartupData, CreateFromSource, arginfo_v8_startup_data_CreateFromSource, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)

        PHP_FE_END
};


PHP_MINIT_FUNCTION (php_v8_startup_data) {
    zend_class_entry ce;
    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS, "StartupData", php_v8_startup_data_methods);
    this_ce = zend_register_internal_class(&ce);
    this_ce->create_object = php_v8_startup_data_ctor;

    memcpy(&php_v8_startup_data_object_handlers, zend_get_std_object_handlers(), sizeof(zend_object_handlers));

    php_v8_startup_data_object_handlers.offset   = XtOffsetOf(php_v8_startup_data_t, std);
    php_v8_startup_data_object_handlers.free_obj = php_v8_startup_data_free;

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
