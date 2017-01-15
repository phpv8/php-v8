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

#include "php_v8_string.h"
#include "php_v8_context.h"
#include "php_v8_object_template.h"
#include "php_v8_value.h"
#include "php_v8.h"

zend_class_entry* php_v8_context_class_entry;
#define this_ce php_v8_context_class_entry

static zend_object_handlers php_v8_context_object_handlers;

v8::Local<v8::Context> php_v8_context_get_local(v8::Isolate *isolate, php_v8_context_t *php_v8_context) {
    return v8::Local<v8::Context>::New(isolate, *php_v8_context->context);
};

php_v8_context_t * php_v8_context_fetch_object(zend_object *obj) {
    return (php_v8_context_t *)((char *)obj - XtOffsetOf(php_v8_context_t, std));
}

static void php_v8_context_free(zend_object *object)
{
    php_v8_context_t *php_v8_context = php_v8_context_fetch_object(object);

    // TODO: if we become weak, don't forget to remove stored `zval* this_ptr` to Context object

    if (php_v8_context->context) {
        if (PHP_V8_ISOLATE_HAS_VALID_HANDLE(php_v8_context)) {
            php_v8_context->context->Reset();
        }

        delete php_v8_context->context;
    }

    if (!Z_ISUNDEF(php_v8_context->this_ptr)) {
        zval_ptr_dtor(&php_v8_context->this_ptr);
    }

    zend_object_std_dtor(&php_v8_context->std);
}

static zend_object * php_v8_context_ctor(zend_class_entry *ce)
{
    php_v8_context_t *php_v8_context;

    php_v8_context = (php_v8_context_t *) ecalloc(1, sizeof(php_v8_context_t) + zend_object_properties_size(ce));

    zend_object_std_init(&php_v8_context->std, ce);
    object_properties_init(&php_v8_context->std, ce);

    php_v8_context->context = new v8::Persistent<v8::Context>();

    php_v8_context->std.handlers = &php_v8_context_object_handlers;

    return &php_v8_context->std;
}

void php_v8_context_store_reference(v8::Isolate *isolate, v8::Local<v8::Context> context, php_v8_context_t *php_v8_context) {
    v8::Local<v8::External> this_embedded = v8::External::New(isolate, php_v8_context);

    context->SetEmbedderData(1, this_embedded);
}

php_v8_context_t * php_v8_context_get_reference(v8::Local<v8::Context> context) {
    v8::Local<v8::Value> this_embedded = context->GetEmbedderData(1);

    assert(!this_embedded.IsEmpty());
    assert(this_embedded->IsExternal());

    return static_cast<php_v8_context_t *>(v8::Local<v8::External>::Cast(this_embedded)->Value());
}


static PHP_METHOD(V8Context, __construct)
{
    zval *php_v8_isolate_zv;
    zval *extensions_zv = NULL;
    zval *php_v8_global_template_zv = NULL;
    zval *php_v8_global_object_zv = NULL;

    v8::ExtensionConfiguration *extensions = NULL;
    v8::Local<v8::ObjectTemplate> global_template;
    v8::Local<v8::Value> global_object;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o|a!o!o!", &php_v8_isolate_zv, &extensions_zv, &php_v8_global_template_zv, &php_v8_global_object_zv) == FAILURE) {
        return;
    }

    PHP_V8_ISOLATE_FETCH_WITH_CHECK(php_v8_isolate_zv, php_v8_isolate);
    PHP_V8_CONTEXT_FETCH_INTO(getThis(), php_v8_context);

    PHP_V8_CONTEXT_STORE_ISOLATE(getThis(), php_v8_isolate_zv);
    PHP_V8_STORE_POINTER_TO_ISOLATE(php_v8_context, php_v8_isolate);
    PHP_V8_ENTER_ISOLATE(php_v8_isolate);

    if (extensions_zv) {
        zend_update_property(this_ce, getThis(), ZEND_STRL("extensions"), extensions_zv);
    }

    if (php_v8_global_template_zv) {
        zend_update_property(this_ce, getThis(), ZEND_STRL("global_template"), php_v8_global_template_zv);
    }

    // TODO: implement extensions, note this feature is controversial, it also requires v8::RegisterExtension()
    // TODO: store registered extensions somewhere and validate them by name before setting?
    if (extensions_zv && zend_array_count(Z_ARRVAL_P(extensions_zv)) > 0) {
        zend_error(E_WARNING, "Extensions are not supported yet");
    }

    if (php_v8_global_template_zv && Z_TYPE_P(php_v8_global_template_zv) != IS_NULL) {
        PHP_V8_FETCH_OBJECT_TEMPLATE_WITH_CHECK(php_v8_global_template_zv, php_v8_global_template);
        PHP_V8_DATA_ISOLATES_CHECK(php_v8_context, php_v8_global_template);

        global_template = php_v8_object_template_get_local(isolate, php_v8_global_template);
    }

    if (php_v8_global_object_zv && Z_TYPE_P(php_v8_global_object_zv) != IS_NULL) {
        PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_global_object_zv, php_v8_global_object);
        PHP_V8_DATA_ISOLATES_CHECK(php_v8_context, php_v8_global_object);

        global_object = php_v8_value_get_value_local(isolate, php_v8_global_object);
    }

    v8::Local<v8::Context> context = v8::Context::New(isolate, extensions, global_template, global_object);

    PHP_V8_THROW_VALUE_EXCEPTION_WHEN_EMPTY(context, "Failed to create Context");

    ZVAL_COPY_VALUE(&php_v8_context->this_ptr, getThis());
    php_v8_context_store_reference(isolate, context, php_v8_context);

    php_v8_context->context->Reset(isolate, context);
}

static PHP_METHOD(V8Context, GetIsolate)
{
    zval rv;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_CONTEXT_FETCH_WITH_CHECK(getThis(), php_v8_context);

    RETVAL_ZVAL(PHP_V8_CONTEXT_READ_ISOLATE(getThis()), 1, 0);
}

static PHP_METHOD(V8Context, GlobalObject)
{
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_CONTEXT_FETCH_WITH_CHECK(getThis(), php_v8_context);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    v8::Local<v8::Object> local_object = context->Global();

    php_v8_get_or_create_value(return_value, local_object, isolate);
}

static PHP_METHOD(V8Context, DetachGlobal)
{
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_CONTEXT_FETCH_WITH_CHECK(getThis(), php_v8_context);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);

    v8::Local<v8::Context> local_context = php_v8_context_get_local(isolate, php_v8_context);

    local_context->DetachGlobal();
}

static PHP_METHOD(V8Context, SetSecurityToken)
{
    zval *php_v8_value_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o", &php_v8_value_zv) == FAILURE) {
        return;
    }

    PHP_V8_CONTEXT_FETCH_WITH_CHECK(getThis(), php_v8_context);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);

    PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_value_zv, php_v8_value);

    v8::Local<v8::Context> local_context = php_v8_context_get_local(isolate, php_v8_context);
    v8::Local<v8::Value> local_token = php_v8_value_get_value_local(isolate, php_v8_value);

    local_context->SetSecurityToken(local_token);
}

static PHP_METHOD(V8Context, UseDefaultSecurityToken)
{
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_CONTEXT_FETCH_WITH_CHECK(getThis(), php_v8_context);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);

    v8::Local<v8::Context> local_context = php_v8_context_get_local(isolate, php_v8_context);

    local_context->UseDefaultSecurityToken();
}

static PHP_METHOD(V8Context, GetSecurityToken)
{
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_CONTEXT_FETCH_WITH_CHECK(getThis(), php_v8_context);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    v8::Local<v8::Context> local_context = php_v8_context_get_local(isolate, php_v8_context);
    v8::Local<v8::Value> local_value = local_context->GetSecurityToken();

    php_v8_get_or_create_value(return_value, local_value, isolate);
}

static PHP_METHOD(V8Context, AllowCodeGenerationFromStrings)
{
    zend_bool allow = '\1';

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "b", &allow) == FAILURE) {
        return;
    }

    PHP_V8_CONTEXT_FETCH_WITH_CHECK(getThis(), php_v8_context);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);

    v8::Local<v8::Context> local_context = php_v8_context_get_local(isolate, php_v8_context);
    local_context->AllowCodeGenerationFromStrings((bool) allow);
}

static PHP_METHOD(V8Context, IsCodeGenerationFromStringsAllowed)
{
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_CONTEXT_FETCH_WITH_CHECK(getThis(), php_v8_context);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);

    v8::Local<v8::Context> local_context = php_v8_context_get_local(isolate, php_v8_context);

    RETURN_BOOL(local_context->IsCodeGenerationFromStringsAllowed());
}

static PHP_METHOD(V8Context, SetErrorMessageForCodeGenerationFromStrings)
{
    zval *php_v8_string_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o", &php_v8_string_zv) == FAILURE) {
        return;
    }

    PHP_V8_CONTEXT_FETCH_WITH_CHECK(getThis(), php_v8_context);
    PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_string_zv, php_v8_string);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_context, php_v8_string);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    v8::Local<v8::Context> local_context = php_v8_context_get_local(isolate, php_v8_context);

    v8::Local<v8::String> local_string = php_v8_value_get_string_local(isolate, php_v8_string);

    local_context->SetErrorMessageForCodeGenerationFromStrings(local_string);
}

static PHP_METHOD(V8Context, EstimatedSize)
{
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_CONTEXT_FETCH_WITH_CHECK(getThis(), php_v8_context);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    v8::Local<v8::Context> local_context = php_v8_context_get_local(isolate, php_v8_context);

    RETURN_LONG(local_context->EstimatedSize());
}

ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_context___construct, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 1)
    ZEND_ARG_OBJ_INFO(0, isolate, V8\\Isolate, 0)
    ZEND_ARG_ARRAY_INFO(0, extensions, 1)
    ZEND_ARG_OBJ_INFO(0, global_template, V8\\ObjectTemplate, 1)
    ZEND_ARG_OBJ_INFO(0, global_object, V8\\ObjectValue, 1)
ZEND_END_ARG_INFO()


ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_context_GetIsolate, ZEND_RETURN_VALUE, 0, IS_OBJECT, PHP_V8_NS "\\Isolate", 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_context_GlobalObject, ZEND_RETURN_VALUE, 0, IS_OBJECT, PHP_V8_NS "\\ObjectValue", 0)
ZEND_END_ARG_INFO()

// void method
ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_context_DetachGlobal, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 0)
ZEND_END_ARG_INFO()

// void method
ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_context_SetSecurityToken, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 1)
    ZEND_ARG_OBJ_INFO(0, token, V8\\Value, 0)
ZEND_END_ARG_INFO()

// void method
ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_context_UseDefaultSecurityToken, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_context_GetSecurityToken, ZEND_RETURN_VALUE, 0, IS_OBJECT, PHP_V8_NS "\\Value", 0)
ZEND_END_ARG_INFO()

// void method
ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_context_AllowCodeGenerationFromStrings, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 1)
                ZEND_ARG_TYPE_INFO(0, allow, _IS_BOOL, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_context_IsCodeGenerationFromStringsAllowed, ZEND_RETURN_VALUE, 0, _IS_BOOL, NULL, 0)
ZEND_END_ARG_INFO()

// void method
ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_context_SetErrorMessageForCodeGenerationFromStrings, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 1)
                ZEND_ARG_OBJ_INFO(0, message, V8\\StringValue, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_context_EstimatedSize, ZEND_RETURN_VALUE, 0, IS_LONG, NULL, 0)
ZEND_END_ARG_INFO()


static const zend_function_entry php_v8_context_methods[] = {
    PHP_ME(V8Context, __construct, arginfo_v8_context___construct, ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)
    PHP_ME(V8Context, GetIsolate, arginfo_v8_context_GetIsolate, ZEND_ACC_PUBLIC)

    PHP_ME(V8Context, GlobalObject, arginfo_v8_context_GlobalObject, ZEND_ACC_PUBLIC)
    PHP_ME(V8Context, DetachGlobal, arginfo_v8_context_DetachGlobal, ZEND_ACC_PUBLIC)

    PHP_ME(V8Context, SetSecurityToken, arginfo_v8_context_SetSecurityToken, ZEND_ACC_PUBLIC)
    PHP_ME(V8Context, UseDefaultSecurityToken, arginfo_v8_context_UseDefaultSecurityToken, ZEND_ACC_PUBLIC)
    PHP_ME(V8Context, GetSecurityToken, arginfo_v8_context_GetSecurityToken, ZEND_ACC_PUBLIC)

    PHP_ME(V8Context, AllowCodeGenerationFromStrings, arginfo_v8_context_AllowCodeGenerationFromStrings, ZEND_ACC_PUBLIC)
    PHP_ME(V8Context, IsCodeGenerationFromStringsAllowed, arginfo_v8_context_IsCodeGenerationFromStringsAllowed, ZEND_ACC_PUBLIC)
    PHP_ME(V8Context, SetErrorMessageForCodeGenerationFromStrings, arginfo_v8_context_SetErrorMessageForCodeGenerationFromStrings, ZEND_ACC_PUBLIC)

    PHP_ME(V8Context, EstimatedSize, arginfo_v8_context_EstimatedSize, ZEND_ACC_PUBLIC)

    PHP_FE_END
};


PHP_MINIT_FUNCTION(php_v8_context)
{
    zend_class_entry ce;

    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS, "Context", php_v8_context_methods);
    this_ce = zend_register_internal_class(&ce);
    this_ce->create_object = php_v8_context_ctor;

    zend_declare_property_null(this_ce, ZEND_STRL("isolate"), ZEND_ACC_PRIVATE);
    zend_declare_property_null(this_ce, ZEND_STRL("extensions"), ZEND_ACC_PRIVATE);
    zend_declare_property_null(this_ce, ZEND_STRL("global_template"), ZEND_ACC_PRIVATE);
    zend_declare_property_null(this_ce, ZEND_STRL("global_object"), ZEND_ACC_PRIVATE);

    memcpy(&php_v8_context_object_handlers, zend_get_std_object_handlers(), sizeof(zend_object_handlers));

    php_v8_context_object_handlers.offset = XtOffsetOf(php_v8_context_t, std);
    php_v8_context_object_handlers.free_obj = php_v8_context_free;

    return SUCCESS;
}
