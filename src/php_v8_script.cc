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

#include "php_v8_script.h"
#include "php_v8_script_origin.h"
#include "php_v8_string.h"
#include "php_v8_value.h"
#include "php_v8.h"

zend_class_entry* php_v8_script_class_entry;
#define this_ce php_v8_script_class_entry

static zend_object_handlers php_v8_script_object_handlers;


v8::Local<v8::Script> php_v8_script_get_local(v8::Isolate *isolate, php_v8_script_t *php_v8_script) {
    return v8::Local<v8::Script>::New(isolate, *php_v8_script->persistent);
}

php_v8_script_t * php_v8_script_fetch_object(zend_object *obj) {
    return (php_v8_script_t *)((char *)obj - XtOffsetOf(php_v8_script_t, std));
}

php_v8_script_t *php_v8_create_script(zval *return_value, v8::Local<v8::Script> local_script, php_v8_context_t *php_v8_context) {
    assert(!local_script.IsEmpty());

    PHP_V8_DECLARE_ISOLATE(php_v8_context->php_v8_isolate);

    object_init_ex(return_value, this_ce);

    PHP_V8_FETCH_SCRIPT_INTO(return_value, php_v8_script);

    PHP_V8_STORE_POINTER_TO_ISOLATE(php_v8_script, php_v8_context->php_v8_isolate);
    PHP_V8_STORE_POINTER_TO_CONTEXT(php_v8_script, php_v8_context);

    php_v8_script->persistent->Reset(isolate, local_script);

    return php_v8_script;
}

static void php_v8_script_free(zend_object *object)
{
    php_v8_script_t *php_v8_script = php_v8_script_fetch_object(object);

    // TODO: think about making script weak, it probably may still in use by returned functions from it, isn't it?
    if (php_v8_script->persistent) {
        if (PHP_V8_ISOLATE_HAS_VALID_HANDLE(php_v8_script)) {
            php_v8_script->persistent->Reset();
        }
        delete php_v8_script->persistent;
    }

    zend_object_std_dtor(&php_v8_script->std);
}


static zend_object * php_v8_script_ctor(zend_class_entry *ce)
{
    php_v8_script_t *php_v8_script;

    php_v8_script = (php_v8_script_t *) ecalloc(1, sizeof(php_v8_script_t) + zend_object_properties_size(ce));

    zend_object_std_init(&php_v8_script->std, ce);
    object_properties_init(&php_v8_script->std, ce);

    php_v8_script->persistent = new v8::Persistent<v8::Script>();

    php_v8_script->std.handlers = &php_v8_script_object_handlers;

    return &php_v8_script->std;
}

static PHP_METHOD(V8Script, __construct)
{
    zval rv;
    zval *php_v8_context_zv;
    zval *php_v8_string_zv;
    zval *php_v8_origin_zv = NULL;


    v8::ScriptOrigin *origin = NULL;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "oo|o", &php_v8_context_zv, &php_v8_string_zv, &php_v8_origin_zv) == FAILURE) {
        return;
    }

    PHP_V8_FETCH_SCRIPT_INTO(getThis(), php_v8_script);
    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);
    PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_string_zv, php_v8_string);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_context, php_v8_string);

    PHP_V8_STORE_POINTER_TO_ISOLATE(php_v8_script, php_v8_context->php_v8_isolate);
    PHP_V8_STORE_POINTER_TO_CONTEXT(php_v8_script, php_v8_context);

    PHP_V8_SCRIPT_STORE_CONTEXT(getThis(), php_v8_context_zv);
    PHP_V8_SCRIPT_STORE_ISOLATE(getThis(), PHP_V8_CONTEXT_READ_ISOLATE(php_v8_context_zv));

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_script);
    PHP_V8_ENTER_STORED_CONTEXT(php_v8_script);

    if (php_v8_origin_zv != NULL) {
        origin = php_v8_create_script_origin_from_zval(php_v8_origin_zv, isolate);

        if (!origin) {
            /* exception was already thrown, here we just silently exit */
            return;
        }
    }

    v8::Local<v8::String> local_source =  php_v8_value_get_string_local(isolate, php_v8_string);

    PHP_V8_TRY_CATCH(isolate);
    PHP_V8_INIT_ISOLATE_LIMITS_ON_SCRIPT(php_v8_script);

    PHP_V8_DECLARE_LIMITS(php_v8_script->php_v8_isolate);

    v8::MaybeLocal<v8::Script> maybe_script = v8::Script::Compile(context, local_source, origin);

    PHP_V8_MAYBE_CATCH(php_v8_context, try_catch);
    PHP_V8_THROW_VALUE_EXCEPTION_WHEN_EMPTY(maybe_script, "Failed to create Script");

    php_v8_script->persistent->Reset(isolate, maybe_script.ToLocalChecked());
}

static PHP_METHOD(V8Script, GetIsolate)
{
    zval rv;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_FETCH_SCRIPT_WITH_CHECK(getThis(), php_v8_script);

    RETVAL_ZVAL(PHP_V8_SCRIPT_READ_ISOLATE(getThis()), 1, 0);
}

static PHP_METHOD(V8Script, GetContext)
{
    zval rv;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_FETCH_SCRIPT_WITH_CHECK(getThis(), php_v8_script);

    RETVAL_ZVAL(PHP_V8_SCRIPT_READ_CONTEXT(getThis()), 1, 0);
}

static PHP_METHOD(V8Script, Run)
{
    zval *php_v8_context_zv;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "o", &php_v8_context_zv) == FAILURE) {
        return;
    }

    PHP_V8_FETCH_SCRIPT_WITH_CHECK(getThis(), php_v8_script);
    PHP_V8_CONTEXT_FETCH_WITH_CHECK(php_v8_context_zv, php_v8_context);

    PHP_V8_DATA_ISOLATES_CHECK(php_v8_script, php_v8_context);

    PHP_V8_ENTER_STORED_ISOLATE(php_v8_script);
    PHP_V8_ENTER_CONTEXT(php_v8_context);

    v8::Local<v8::Script> local_script = php_v8_script_get_local(isolate, php_v8_script);

    PHP_V8_TRY_CATCH(isolate);
    PHP_V8_INIT_ISOLATE_LIMITS_ON_SCRIPT(php_v8_script);

    v8::MaybeLocal<v8::Value> result = local_script->Run(context);

    PHP_V8_MAYBE_CATCH(php_v8_script->php_v8_context, try_catch);
    PHP_V8_THROW_VALUE_EXCEPTION_WHEN_EMPTY(result, "Failed to create run script");

    v8::Local<v8::Value> local_result = result.ToLocalChecked();

    php_v8_get_or_create_value(return_value, local_result, isolate);
}


ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_script___construct, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 2)
    ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
    ZEND_ARG_OBJ_INFO(0, source, V8\\StringValue, 0)
    ZEND_ARG_OBJ_INFO(0, origin, V8\\ScriptOrigin, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_v8_script_GetIsolate, ZEND_RETURN_VALUE, 0, V8\\Isolate, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_v8_script_GetContext, ZEND_RETURN_VALUE, 0, V8\\Context, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_v8_script_Run, ZEND_RETURN_VALUE, 1, V8\\Value, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
ZEND_END_ARG_INFO()


static const zend_function_entry php_v8_script_methods[] = {
    PHP_ME(V8Script, __construct, arginfo_v8_script___construct, ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)
    PHP_ME(V8Script, GetIsolate, arginfo_v8_script_GetIsolate, ZEND_ACC_PUBLIC)
    PHP_ME(V8Script, GetContext, arginfo_v8_script_GetContext, ZEND_ACC_PUBLIC)

    PHP_ME(V8Script, Run, arginfo_v8_script_Run, ZEND_ACC_PUBLIC)

    PHP_FE_END
};


PHP_MINIT_FUNCTION(php_v8_script)
{
    zend_class_entry ce;

    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS, "Script", php_v8_script_methods);
    this_ce = zend_register_internal_class(&ce);
    this_ce->create_object = php_v8_script_ctor;

    zend_declare_property_null(this_ce, ZEND_STRL("isolate"), ZEND_ACC_PRIVATE);
    zend_declare_property_null(this_ce, ZEND_STRL("context"), ZEND_ACC_PRIVATE);

    memcpy(&php_v8_script_object_handlers, zend_get_std_object_handlers(), sizeof(zend_object_handlers));

    php_v8_script_object_handlers.offset = XtOffsetOf(php_v8_script_t, std);
    php_v8_script_object_handlers.free_obj = php_v8_script_free;

    return SUCCESS;
}
