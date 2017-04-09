/*
 * This file is part of the pinepain/php-v8 PHP extension.
 *
 * Copyright (c) 2015-2017 Bogdan Padalko <pinepain@gmail.com>
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

#include "php_v8_script_origin.h"
#include "php_v8_script_origin_options.h"
#include "php_v8_value.h"
#include "php_v8.h"

zend_class_entry *php_v8_script_origin_class_entry;
#define this_ce php_v8_script_origin_class_entry


extern void php_v8_create_script_origin(zval *return_value, v8::Local<v8::Context> context, v8::ScriptOrigin origin) {
    zval options_zv;

    object_init_ex(return_value, this_ce);

    /* v8::ScriptOrigin::ResourceName */
    if (!origin.ResourceName().IsEmpty() && !origin.ResourceName()->IsUndefined()) {
        v8::String::Utf8Value resource_name_utf8(origin.ResourceName());
        PHP_V8_CONVERT_UTF8VALUE_TO_STRING_WITH_CHECK(resource_name_utf8, resource_name_chars);
        zend_update_property_string(this_ce, return_value, ZEND_STRL("resource_name"), resource_name_chars);
    }

    /* v8::SourceMapUrl::ResourceLineOffset */
    if (!origin.ResourceLineOffset().IsEmpty() && origin.ResourceLineOffset()->NumberValue(context).IsJust()) {
        zend_update_property_long(this_ce, return_value, ZEND_STRL("resource_line_offset"), static_cast<zend_long>(origin.ResourceLineOffset()->NumberValue(context).FromJust()));
    }

    /* v8::SourceMapUrl::ResourceColumnOffset */
    if (!origin.ResourceColumnOffset().IsEmpty() && origin.ResourceColumnOffset()->NumberValue(context).IsJust()) {
        zend_update_property_long(this_ce, return_value, ZEND_STRL("resource_column_offset"), static_cast<zend_long>(origin.ResourceColumnOffset()->NumberValue(context).FromJust()));
    }

    /* v8::SourceMapUrl::Options */
    php_v8_create_script_origin_options(&options_zv, origin.Options());
    zend_update_property(this_ce, return_value, ZEND_STRL("options"), &options_zv);
    zval_ptr_dtor(&options_zv);

    /* v8::SourceMapUrl::ScriptID */
    if (!origin.ScriptID().IsEmpty() && origin.ScriptID()->NumberValue(context).IsJust()) {
        zend_update_property_long(this_ce, return_value, ZEND_STRL("script_id"), static_cast<zend_long>(origin.ScriptID()->NumberValue(context).FromJust()));
    }

    /* v8::SourceMapUrl::ResourceName */
    if (!origin.SourceMapUrl().IsEmpty() && !origin.SourceMapUrl()->IsUndefined()) {
        v8::String::Utf8Value source_map_url_utf8(origin.SourceMapUrl());
        PHP_V8_CONVERT_UTF8VALUE_TO_STRING_WITH_CHECK(source_map_url_utf8, source_map_url_chars);
        zend_update_property_string(this_ce, return_value, ZEND_STRL("source_map_url"), source_map_url_chars);
    }
}


v8::ScriptOrigin *php_v8_create_script_origin_from_zval(zval *value, v8::Isolate *isolate) {
    zval rv;

    v8::Local<v8::Value> resource_name = v8::Undefined(isolate);
    v8::Local<v8::Integer> resource_line_offset = v8::Local<v8::Integer>();
    v8::Local<v8::Integer> resource_column_offset = v8::Local<v8::Integer>();
    v8::Local<v8::Boolean> resource_is_shared_cross_origin = v8::Local<v8::Boolean>();
    v8::Local<v8::Integer> script_id = v8::Local<v8::Integer>();
    v8::Local<v8::Value> source_map_url = v8::Local<v8::Value>();
    v8::Local<v8::Boolean> resource_is_opaque = v8::Local<v8::Boolean>();
    v8::Local<v8::Boolean> is_wasm = v8::Local<v8::Boolean>();
    v8::Local<v8::Boolean> is_module = v8::Local<v8::Boolean>();

    zval *resource_name_zv = zend_read_property(this_ce, value, ZEND_STRL("resource_name"), 0, &rv); // string

    if (Z_TYPE_P(resource_name_zv) == IS_STRING) {
        v8::MaybeLocal<v8::String> local_resource_name =  v8::String::NewFromUtf8(isolate, Z_STRVAL_P(resource_name_zv), v8::NewStringType::kNormal, (int)Z_STRLEN_P(resource_name_zv));

        if (local_resource_name.IsEmpty()) {
            PHP_V8_THROW_EXCEPTION("Invalid resource name");
            return nullptr;
        }

        resource_name = local_resource_name.ToLocalChecked().As<v8::Value>();
    }

    zval *resource_line_offset_zv = zend_read_property(this_ce, value, ZEND_STRL("resource_line_offset"), 0, &rv); // zend_long

    if (Z_TYPE_P(resource_line_offset_zv) == IS_LONG) {
        resource_line_offset = v8::Integer::New(isolate, static_cast<int32_t>(Z_LVAL_P(resource_line_offset_zv)));
    }

    zval *resource_column_offset_zv = zend_read_property(this_ce, value, ZEND_STRL("resource_column_offset"), 0, &rv); // zend_long

    if (Z_TYPE_P(resource_column_offset_zv) == IS_LONG) {
        resource_column_offset = v8::Integer::New(isolate, static_cast<int32_t>(Z_LVAL_P(resource_column_offset_zv)));
    }

    zval *script_id_zv = zend_read_property(this_ce, value, ZEND_STRL("script_id"), 0, &rv);
    if (Z_TYPE_P(script_id_zv) == IS_LONG) {
        script_id = v8::Integer::New(isolate, static_cast<int32_t>(Z_LVAL_P(script_id_zv)));
    }

    zval *source_map_url_zv = zend_read_property(this_ce, value, ZEND_STRL("source_map_url"), 0, &rv);

    if (Z_TYPE_P(source_map_url_zv) == IS_STRING) {
        v8::MaybeLocal<v8::String> local_source_map_url =  v8::String::NewFromUtf8(isolate, Z_STRVAL_P(source_map_url_zv), v8::NewStringType::kNormal, (int)Z_STRLEN_P(source_map_url_zv));

        if (local_source_map_url.IsEmpty()) {
            PHP_V8_THROW_EXCEPTION("Invalid source map url");
            return nullptr;
        }

        source_map_url = local_source_map_url.ToLocalChecked().As<v8::Value>();
    }

    zval *options_zv = zend_read_property(this_ce, value, ZEND_STRL("options"), 0, &rv); // ScriptOriginOptions

    if (Z_TYPE_P(options_zv) == IS_OBJECT && instanceof_function(Z_OBJCE_P(options_zv), php_v8_script_origin_options_class_entry)) {
        zval *is_shared_cross_origin_zv = zend_read_property(php_v8_script_origin_options_class_entry, options_zv, ZEND_STRL("is_shared_cross_origin"), 0, &rv);
        zval *is_opaque_zv = zend_read_property(php_v8_script_origin_options_class_entry, options_zv, ZEND_STRL("is_opaque"), 0, &rv);
        zval *is_wasm_zv = zend_read_property(php_v8_script_origin_options_class_entry, options_zv, ZEND_STRL("is_wasm"), 0, &rv);
        zval *is_module_zv = zend_read_property(php_v8_script_origin_options_class_entry, options_zv, ZEND_STRL("is_module"), 0, &rv);

        resource_is_shared_cross_origin = v8::Boolean::New(isolate, Z_TYPE_P(is_shared_cross_origin_zv) == IS_TRUE);
        resource_is_opaque = v8::Boolean::New(isolate, Z_TYPE_P(is_opaque_zv) == IS_TRUE);
        is_wasm = v8::Boolean::New(isolate, Z_TYPE_P(is_wasm_zv) == IS_TRUE);
        is_module = v8::Boolean::New(isolate, Z_TYPE_P(is_module_zv) == IS_TRUE);
    }

    return new v8::ScriptOrigin(resource_name,
                                resource_line_offset,
                                resource_column_offset,
                                resource_is_shared_cross_origin,
                                script_id,
                                source_map_url,
                                resource_is_opaque,
                                is_wasm,
                                is_module);
}

static PHP_METHOD(V8ScriptOrigin, __construct) {
    zend_string *resource_name = NULL;
    zend_long resource_line_offset = static_cast<zend_long>(v8::Message::kNoLineNumberInfo);
    zend_long resource_column_offset = static_cast<zend_long>(v8::Message::kNoColumnInfo);
    zend_bool resource_is_shared_cross_origin = '\0';
    zend_long script_id = static_cast<zend_long>(v8::Message::kNoScriptIdInfo);
    zend_string *source_map_url = NULL;
    zend_bool resource_is_opaque = '\0';
    zend_bool is_wasm = '\0';
    zend_bool is_module = '\0';

    zval options_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "|SllblSbbb",
                              &resource_name,
                              &resource_line_offset,
                              &resource_column_offset,
                              &resource_is_shared_cross_origin,
                              &script_id,
                              &source_map_url,
                              &resource_is_opaque,
                              &is_wasm,
                              &is_module) == FAILURE) {
        return;
    }

    v8::ScriptOriginOptions options(static_cast<bool>(resource_is_shared_cross_origin),
                                    static_cast<bool>(resource_is_opaque),
                                    static_cast<bool>(is_wasm),
                                    static_cast<bool>(is_module));

    php_v8_create_script_origin_options(&options_zv, options);

    zend_update_property_str(this_ce, getThis(), ZEND_STRL("resource_name"), resource_name);

    zend_update_property_long(this_ce, getThis(), ZEND_STRL("resource_line_offset"), resource_line_offset);
    zend_update_property_long(this_ce, getThis(), ZEND_STRL("resource_column_offset"), resource_column_offset);

    zend_update_property(this_ce, getThis(), ZEND_STRL("options"), &options_zv);

    zend_update_property_long(this_ce, getThis(), ZEND_STRL("script_id"), script_id);

    if (source_map_url != NULL) {
        zend_update_property_str(this_ce, getThis(), ZEND_STRL("source_map_url"), source_map_url);
    }

    zval_ptr_dtor(&options_zv);
}

static PHP_METHOD(V8ScriptOrigin, ResourceName) {
    zval rv;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    RETVAL_ZVAL(zend_read_property(this_ce, getThis(), ZEND_STRL("resource_name"), 0, &rv), 1, 0);
}

static PHP_METHOD(V8ScriptOrigin, ResourceLineOffset) {
    zval rv;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    RETVAL_ZVAL(zend_read_property(this_ce, getThis(), ZEND_STRL("resource_line_offset"), 0, &rv), 1, 0);
}

static PHP_METHOD(V8ScriptOrigin, ResourceColumnOffset) {
    zval rv;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    RETVAL_ZVAL(zend_read_property(this_ce, getThis(), ZEND_STRL("resource_column_offset"), 0, &rv), 1, 0);
}

static PHP_METHOD(V8ScriptOrigin, ScriptID) {
    zval rv;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    RETVAL_ZVAL(zend_read_property(this_ce, getThis(), ZEND_STRL("script_id"), 0, &rv), 1, 0);
}

static PHP_METHOD(V8ScriptOrigin, SourceMapUrl) {
    zval rv;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    RETVAL_ZVAL(zend_read_property(this_ce, getThis(), ZEND_STRL("source_map_url"), 0, &rv), 1, 0);
}

static PHP_METHOD(V8ScriptOrigin, Options) {
    zval rv;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    RETVAL_ZVAL(zend_read_property(this_ce, getThis(), ZEND_STRL("options"), 0, &rv), 1, 0);
}


ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_script_origin___construct, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 1)
                ZEND_ARG_TYPE_INFO(0, resource_name, IS_STRING, 0)
                ZEND_ARG_TYPE_INFO(0, resource_line_offset, IS_LONG, 0)
                ZEND_ARG_TYPE_INFO(0, resource_column_offset, IS_LONG, 0)
                ZEND_ARG_TYPE_INFO(0, resource_is_shared_cross_origin, _IS_BOOL, 0)
                ZEND_ARG_TYPE_INFO(0, script_id, IS_LONG, 0)
                ZEND_ARG_TYPE_INFO(0, source_map_url, IS_STRING, 0)
                ZEND_ARG_TYPE_INFO(0, resource_is_opaque, _IS_BOOL, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_script_origin_ResourceName, ZEND_RETURN_VALUE, 0, IS_STRING, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_script_origin_ResourceLineOffset, ZEND_RETURN_VALUE, 0, IS_LONG, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_script_origin_ResourceColumnOffset, ZEND_RETURN_VALUE, 0, IS_LONG, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_script_origin_ScriptID, ZEND_RETURN_VALUE, 0, IS_LONG, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_script_origin_SourceMapUrl, ZEND_RETURN_VALUE, 0, IS_STRING, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_v8_script_origin_Options, ZEND_RETURN_VALUE, 0, V8\\ScriptOriginOptions, 0)
ZEND_END_ARG_INFO()


static const zend_function_entry php_v8_script_origin_methods[] = {
        PHP_ME(V8ScriptOrigin, __construct, arginfo_v8_script_origin___construct, ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)

        PHP_ME(V8ScriptOrigin, ResourceName, arginfo_v8_script_origin_ResourceName, ZEND_ACC_PUBLIC)
        PHP_ME(V8ScriptOrigin, ResourceLineOffset, arginfo_v8_script_origin_ResourceLineOffset, ZEND_ACC_PUBLIC)
        PHP_ME(V8ScriptOrigin, ResourceColumnOffset, arginfo_v8_script_origin_ResourceColumnOffset, ZEND_ACC_PUBLIC)
        PHP_ME(V8ScriptOrigin, ScriptID, arginfo_v8_script_origin_ScriptID, ZEND_ACC_PUBLIC)
        PHP_ME(V8ScriptOrigin, SourceMapUrl, arginfo_v8_script_origin_SourceMapUrl, ZEND_ACC_PUBLIC)
        PHP_ME(V8ScriptOrigin, Options, arginfo_v8_script_origin_Options, ZEND_ACC_PUBLIC)

        PHP_FE_END
};


PHP_MINIT_FUNCTION (php_v8_script_origin) {
    zend_class_entry ce;
    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS, "ScriptOrigin", php_v8_script_origin_methods);
    this_ce = zend_register_internal_class(&ce);

    zend_declare_property_string(this_ce, ZEND_STRL("resource_name"), "", ZEND_ACC_PRIVATE);
    zend_declare_property_long(this_ce, ZEND_STRL("resource_line_offset"), static_cast<zend_long>(v8::Message::kNoLineNumberInfo), ZEND_ACC_PRIVATE);
    zend_declare_property_long(this_ce, ZEND_STRL("resource_column_offset"), static_cast<zend_long>(v8::Message::kNoColumnInfo), ZEND_ACC_PRIVATE);
    zend_declare_property_null(this_ce, ZEND_STRL("options"), ZEND_ACC_PRIVATE);
    zend_declare_property_long(this_ce, ZEND_STRL("script_id"), static_cast<zend_long>(v8::Message::kNoScriptIdInfo), ZEND_ACC_PRIVATE);
    zend_declare_property_string(this_ce, ZEND_STRL("source_map_url"), "", ZEND_ACC_PRIVATE);

    return SUCCESS;
}
