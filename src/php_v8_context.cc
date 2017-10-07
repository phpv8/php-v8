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


static void php_v8_context_free(zend_object *object)
{
    php_v8_context_t *php_v8_context = php_v8_context_fetch_object(object);

    if (php_v8_context->context) {
        if (PHP_V8_IS_UP_AND_RUNNING() && PHP_V8_ISOLATE_HAS_VALID_HANDLE(php_v8_context)) {
            {
                PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
                PHP_V8_DECLARE_CONTEXT(php_v8_context);
                php_v8_context_store_reference(isolate, context, nullptr);
            }

            php_v8_context->context->Reset();
        }

        delete php_v8_context->context;
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


static PHP_METHOD(Context, __construct)
{
    zval *php_v8_isolate_zv;
    zval *php_v8_global_template_zv = NULL;
    zval *php_v8_global_object_zv = NULL;

    v8::ExtensionConfiguration *extensions = NULL;
    v8::Local<v8::ObjectTemplate> global_template;
    v8::Local<v8::Value> global_object;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o|o!o!", &php_v8_isolate_zv, &php_v8_global_template_zv, &php_v8_global_object_zv) == FAILURE) {
        return;
    }

    PHP_V8_ISOLATE_FETCH_WITH_CHECK(php_v8_isolate_zv, php_v8_isolate);
    PHP_V8_CONTEXT_FETCH_INTO(getThis(), php_v8_context);

    PHP_V8_CONTEXT_STORE_ISOLATE(getThis(), php_v8_isolate_zv);
    PHP_V8_STORE_POINTER_TO_ISOLATE(php_v8_context, php_v8_isolate);
    PHP_V8_ENTER_ISOLATE(php_v8_isolate);

    if (php_v8_global_template_zv && Z_TYPE_P(php_v8_global_template_zv) != IS_NULL) {
        PHP_V8_FETCH_OBJECT_TEMPLATE_WITH_CHECK(php_v8_global_template_zv, php_v8_global_template);
        PHP_V8_DATA_ISOLATES_CHECK(php_v8_context, php_v8_global_template);

        global_template = php_v8_object_template_get_local(php_v8_global_template);
    }

    if (php_v8_global_object_zv && Z_TYPE_P(php_v8_global_object_zv) != IS_NULL) {
        PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_global_object_zv, php_v8_global_object);
        PHP_V8_DATA_ISOLATES_CHECK(php_v8_context, php_v8_global_object);

        global_object = php_v8_value_get_local(php_v8_global_object);
    }

    v8::Local<v8::Context> context = v8::Context::New(isolate, extensions, global_template, global_object);

    PHP_V8_THROW_VALUE_EXCEPTION_WHEN_EMPTY(context, "Failed to create Context");

    php_v8_context_store_reference(isolate, context, php_v8_context);

    php_v8_context->context->Reset(isolate, context);
}

static PHP_METHOD(Context, within) {
    zval args;
    zval retval;
    zval rv;
    zval *tmp;

    zend_fcall_info fci = empty_fcall_info;
    zend_fcall_info_cache fci_cache = empty_fcall_info_cache;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "f",  &fci, &fci_cache) == FAILURE) {
        return;
    }

    PHP_V8_CONTEXT_FETCH_WITH_CHECK(getThis(), php_v8_context);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    /* Build the parameter array */
    array_init_size(&args, 2);

    tmp = PHP_V8_CONTEXT_READ_ISOLATE(getThis());
    add_index_zval(&args, 0, tmp);
    Z_ADDREF_P(tmp);

    add_index_zval(&args, 1, getThis());
    Z_ADDREF_P(getThis());

    /* Convert everything to be callable */
    zend_fcall_info_args(&fci, &args);

    /* Initialize the return persistent pointer */
    fci.retval = &retval;

    /* Call the function */
    if (zend_call_function(&fci, &fci_cache) == SUCCESS && Z_TYPE(retval) != IS_UNDEF) {
        ZVAL_COPY_VALUE(return_value, &retval);
    }

    // We let user handle any case of exceptions for themselves

    /* Clean up our mess */
    zend_fcall_info_args_clear(&fci, 1);

    zval_ptr_dtor(&args);
}

static PHP_METHOD(Context, getIsolate)
{
    zval rv;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_CONTEXT_FETCH_WITH_CHECK(getThis(), php_v8_context);

    RETVAL_ZVAL(PHP_V8_CONTEXT_READ_ISOLATE(getThis()), 1, 0);
}

static PHP_METHOD(Context, globalObject)
{
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_CONTEXT_FETCH_WITH_CHECK(getThis(), php_v8_context);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    v8::Local<v8::Object> local_object = context->Global();

    php_v8_get_or_create_value(return_value, local_object, php_v8_context->php_v8_isolate);
}

static PHP_METHOD(Context, detachGlobal)
{
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_CONTEXT_FETCH_WITH_CHECK(getThis(), php_v8_context);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
    PHP_V8_DECLARE_CONTEXT(php_v8_context);

    context->DetachGlobal();
}

static PHP_METHOD(Context, setSecurityToken)
{
    zval *php_v8_value_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o", &php_v8_value_zv) == FAILURE) {
        return;
    }

    PHP_V8_CONTEXT_FETCH_WITH_CHECK(getThis(), php_v8_context);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);

    PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_value_zv, php_v8_value);

    PHP_V8_DECLARE_CONTEXT(php_v8_context);

    v8::Local<v8::Value> local_token = php_v8_value_get_local(php_v8_value);

    context->SetSecurityToken(local_token);
}

static PHP_METHOD(Context, useDefaultSecurityToken)
{
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_CONTEXT_FETCH_WITH_CHECK(getThis(), php_v8_context);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
    PHP_V8_DECLARE_CONTEXT(php_v8_context);

    context->UseDefaultSecurityToken();
}

static PHP_METHOD(Context, getSecurityToken)
{
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_CONTEXT_FETCH_WITH_CHECK(getThis(), php_v8_context);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    v8::Local<v8::Value> local_value = context->GetSecurityToken();

    php_v8_get_or_create_value(return_value, local_value, php_v8_context->php_v8_isolate);
}

static PHP_METHOD(Context, allowCodeGenerationFromStrings)
{
    zend_bool allow = '\1';

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "b", &allow) == FAILURE) {
        return;
    }

    PHP_V8_CONTEXT_FETCH_WITH_CHECK(getThis(), php_v8_context);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
    PHP_V8_DECLARE_CONTEXT(php_v8_context);

    context->AllowCodeGenerationFromStrings((bool) allow);
}

static PHP_METHOD(Context, isCodeGenerationFromStringsAllowed)
{
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_CONTEXT_FETCH_WITH_CHECK(getThis(), php_v8_context);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_context);
    PHP_V8_DECLARE_CONTEXT(php_v8_context);

    RETURN_BOOL(context->IsCodeGenerationFromStringsAllowed());
}

static PHP_METHOD(Context, setErrorMessageForCodeGenerationFromStrings)
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

    v8::Local<v8::String> local_string = php_v8_value_get_local_as<v8::String>(php_v8_string);

    context->SetErrorMessageForCodeGenerationFromStrings(local_string);
}

PHP_V8_ZEND_BEGIN_ARG_WITH_CONSTRUCTOR_INFO_EX(arginfo___construct, 1)
    ZEND_ARG_OBJ_INFO(0, isolate, V8\\Isolate, 0)
    ZEND_ARG_OBJ_INFO(0, global_template, V8\\ObjectTemplate, 1)
    ZEND_ARG_OBJ_INFO(0, global_object, V8\\ObjectValue, 1)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_MIXED_INFO_EX(arginfo_within, 1)
                ZEND_ARG_CALLABLE_INFO(0, callback, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_getIsolate, ZEND_RETURN_VALUE, 0, V8\\Isolate, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_globalObject, ZEND_RETURN_VALUE, 0, V8\\ObjectValue, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_VOID_INFO_EX(arginfo_detachGlobal, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_VOID_INFO_EX(arginfo_setSecurityToken, 1)
    ZEND_ARG_OBJ_INFO(0, token, V8\\Value, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_VOID_INFO_EX(arginfo_useDefaultSecurityToken, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_getSecurityToken, ZEND_RETURN_VALUE, 0, V8\\Value, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_VOID_INFO_EX(arginfo_allowCodeGenerationFromStrings, 1)
                ZEND_ARG_TYPE_INFO(0, allow, _IS_BOOL, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_isCodeGenerationFromStringsAllowed, ZEND_RETURN_VALUE, 0, _IS_BOOL, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_VOID_INFO_EX(arginfo_setErrorMessageForCodeGenerationFromStrings, 1)
                ZEND_ARG_OBJ_INFO(0, message, V8\\StringValue, 0)
ZEND_END_ARG_INFO()


static const zend_function_entry php_v8_context_methods[] = {
    PHP_V8_ME(Context, __construct, ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)
    PHP_V8_ME(Context, within,      ZEND_ACC_PUBLIC)
    PHP_V8_ME(Context, getIsolate,  ZEND_ACC_PUBLIC)

    PHP_V8_ME(Context, globalObject, ZEND_ACC_PUBLIC)
    PHP_V8_ME(Context, detachGlobal, ZEND_ACC_PUBLIC)

    PHP_V8_ME(Context, setSecurityToken,        ZEND_ACC_PUBLIC)
    PHP_V8_ME(Context, useDefaultSecurityToken, ZEND_ACC_PUBLIC)
    PHP_V8_ME(Context, getSecurityToken,        ZEND_ACC_PUBLIC)

    PHP_V8_ME(Context, allowCodeGenerationFromStrings,              ZEND_ACC_PUBLIC)
    PHP_V8_ME(Context, isCodeGenerationFromStringsAllowed,          ZEND_ACC_PUBLIC)
    PHP_V8_ME(Context, setErrorMessageForCodeGenerationFromStrings, ZEND_ACC_PUBLIC)

    PHP_FE_END
};


PHP_MINIT_FUNCTION(php_v8_context)
{
    zend_class_entry ce;

    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS, "Context", php_v8_context_methods);
    this_ce = zend_register_internal_class(&ce);
    this_ce->create_object = php_v8_context_ctor;

    zend_declare_property_null(this_ce, ZEND_STRL("isolate"), ZEND_ACC_PRIVATE);

    memcpy(&php_v8_context_object_handlers, zend_get_std_object_handlers(), sizeof(zend_object_handlers));

    php_v8_context_object_handlers.offset    = XtOffsetOf(php_v8_context_t, std);
    php_v8_context_object_handlers.free_obj  = php_v8_context_free;
    php_v8_context_object_handlers.clone_obj = NULL;

    return SUCCESS;
}
