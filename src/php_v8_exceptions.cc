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

#include "php_v8_exceptions.h"
#include "php_v8_try_catch.h"
#include "php_v8_value.h"
#include "php_v8.h"

zend_class_entry* php_v8_generic_exception_class_entry;
zend_class_entry* php_v8_try_catch_exception_class_entry;
zend_class_entry* php_v8_termination_exception_class_entry;
zend_class_entry* php_v8_abstract_resource_limit_exception_class_entry;
zend_class_entry* php_v8_time_limit_exception_class_entry;
zend_class_entry* php_v8_memory_limit_exception_class_entry;

zend_class_entry* php_v8_value_exception_class_entry;
zend_class_entry* php_v8_script_exception_class_entry;

void php_v8_create_try_catch_exception(zval *return_value, php_v8_isolate_t *php_v8_isolate, php_v8_context_t *php_v8_context, v8::TryCatch *try_catch);

void php_v8_try_catch_throw_exception(v8::TryCatch *try_catch, const char* message, zend_class_entry *ce) {
    if (try_catch->Exception()->IsNull() && try_catch->Message().IsEmpty() && !try_catch->CanContinue() && try_catch->HasTerminated()) {
        // TODO: output termination exception somehow
        return;
    }

    v8::String::Utf8Value exception(try_catch->Exception());

    PHP_V8_CONVERT_UTF8VALUE_TO_STRING_WITH_CHECK(exception, exception_message);

    PHP_V8_THROW_EXCEPTION_CE(exception_message, ce);
}

void php_v8_throw_try_catch_exception(php_v8_isolate_t *php_v8_isolate, php_v8_context_t *php_v8_context, v8::TryCatch *try_catch) {
    zval exception_zv;

    php_v8_create_try_catch_exception(&exception_zv, php_v8_isolate, php_v8_context, try_catch);

    zend_throw_exception_object(&exception_zv);
}

void php_v8_throw_try_catch_exception(php_v8_context_t *php_v8_context, v8::TryCatch *try_catch) {
    php_v8_throw_try_catch_exception(php_v8_context->php_v8_isolate, php_v8_context, try_catch);
}

void php_v8_create_try_catch_exception(zval *return_value, php_v8_isolate_t *php_v8_isolate, php_v8_context_t *php_v8_context, v8::TryCatch *try_catch)
{
    zval try_catch_zv;
    zend_class_entry* ce = NULL;
    const char *message = NULL;

    PHP_V8_DECLARE_LIMITS(php_v8_isolate);

    if ((try_catch == NULL) || (try_catch->Exception()->IsNull() && try_catch->Message().IsEmpty() && !try_catch->CanContinue() && try_catch->HasTerminated())) {
        if (limits->time_limit_hit) {
            ce = php_v8_time_limit_exception_class_entry;
            message = "Time limit exceeded";
        } else if (limits->memory_limit_hit) {
            ce = php_v8_memory_limit_exception_class_entry;
            message = "Memory limit exceeded";
        } else {
            ce = php_v8_termination_exception_class_entry;
            message = "Execution terminated";
        }

        object_init_ex(return_value, ce);
        zend_update_property_string(php_v8_try_catch_exception_class_entry, return_value, ZEND_STRL("message"), message);
    } else {
        v8::String::Utf8Value exception(try_catch->Exception());

        ce = php_v8_try_catch_exception_class_entry;
        PHP_V8_CONVERT_UTF8VALUE_TO_STRING_WITH_CHECK_NODECL(exception, message);

        object_init_ex(return_value, ce);
        zend_update_property_string(php_v8_try_catch_exception_class_entry, return_value, ZEND_STRL("message"), message);
    }

    PHP_V8_TRY_CATCH_EXCEPTION_STORE_ISOLATE(return_value, &php_v8_isolate->this_ptr);
    PHP_V8_TRY_CATCH_EXCEPTION_STORE_CONTEXT(return_value, &php_v8_context->this_ptr);

    php_v8_try_catch_create_from_try_catch(&try_catch_zv, php_v8_isolate, php_v8_context, try_catch);
    PHP_V8_TRY_CATCH_EXCEPTION_STORE_TRY_CATCH(return_value, &try_catch_zv);

    zval_ptr_dtor(&try_catch_zv);
}


static PHP_METHOD(V8ExceptionsTryCatch, __construct)
{
    zval rv;

    zval *isolate_zv = NULL;
    zval *context_zv = NULL;
    zval *try_catch_zv = NULL;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "ooo", &isolate_zv, &context_zv, &try_catch_zv) == FAILURE) {
        return;
    }

    PHP_V8_ISOLATE_FETCH_WITH_CHECK(isolate_zv, php_v8_isolate);
    PHP_V8_CONTEXT_FETCH_WITH_CHECK(context_zv, php_v8_context);

    PHP_V8_DATA_ISOLATES_CHECK_USING(php_v8_context, php_v8_isolate);

    PHP_V8_ISOLATE_FETCH_WITH_CHECK(PHP_V8_TRY_CATCH_READ_ISOLATE(try_catch_zv), php_v8_try_catch_isolate);
    // this is redundant, we do check in TryCatch constructor
    //PHP_V8_CONTEXT_FETCH_WITH_CHECK(PHP_V8_TRY_CATCH_READ_CONTEXT(try_catch_zv), php_v8_try_catch_context);

    PHP_V8_ISOLATES_CHECK(php_v8_try_catch_isolate, php_v8_isolate);
    // this is redundant, we do check in TryCatch constructor
    //PHP_V8_DATA_ISOLATES_CHECK_USING(php_v8_try_catch_context, php_v8_isolate); // thi

    PHP_V8_TRY_CATCH_EXCEPTION_STORE_ISOLATE(getThis(), isolate_zv);
    PHP_V8_TRY_CATCH_EXCEPTION_STORE_CONTEXT(getThis(), context_zv);
    PHP_V8_TRY_CATCH_EXCEPTION_STORE_TRY_CATCH(getThis(), try_catch_zv);
}

static PHP_METHOD(V8ExceptionsTryCatch, GetIsolate)
{
    zval rv;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    RETVAL_ZVAL(PHP_V8_TRY_CATCH_EXCEPTION_READ_ISOLATE(getThis()), 1, 0);
}

static PHP_METHOD(V8ExceptionsTryCatch, GetContext)
{
    zval rv;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    RETVAL_ZVAL(PHP_V8_TRY_CATCH_EXCEPTION_READ_CONTEXT(getThis()), 1, 0);
}

static PHP_METHOD(V8ExceptionsTryCatch, GetTryCatch)
{
    zval rv;

    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    RETVAL_ZVAL(PHP_V8_TRY_CATCH_EXCEPTION_READ_TRY_CATCH(getThis()), 1, 0);
}


ZEND_BEGIN_ARG_INFO_EX(arginfo_v8_exceptions_try_catch___construct, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 3)
    ZEND_ARG_OBJ_INFO(0, isolate, v8\\Isolate, 0)
    ZEND_ARG_OBJ_INFO(0, context, v8\\Context, 0)
    ZEND_ARG_OBJ_INFO(0, try_catch, v8\\TryCatch, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_exceptions_try_catch_GetIsolate, ZEND_RETURN_VALUE, 0, IS_OBJECT, "v8\\Isolate", 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_exceptions_try_catch_GetContext, ZEND_RETURN_VALUE, 0, IS_OBJECT, "v8\\Context", 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_exceptions_try_catch_GetTryCatch, ZEND_RETURN_VALUE, 0, IS_OBJECT, "v8\\TryCatch", 0)
ZEND_END_ARG_INFO()



static const zend_function_entry php_v8_exception_methods[] = {
        PHP_FE_END
};

static const zend_function_entry php_v8_try_catch_exception_methods[] = {
        PHP_ME(V8ExceptionsTryCatch, __construct, arginfo_v8_exceptions_try_catch___construct, ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)

        PHP_ME(V8ExceptionsTryCatch, GetIsolate, arginfo_v8_exceptions_try_catch_GetIsolate, ZEND_ACC_PUBLIC)
        PHP_ME(V8ExceptionsTryCatch, GetContext, arginfo_v8_exceptions_try_catch_GetContext, ZEND_ACC_PUBLIC)
        PHP_ME(V8ExceptionsTryCatch, GetTryCatch, arginfo_v8_exceptions_try_catch_GetTryCatch, ZEND_ACC_PUBLIC)

        PHP_FE_END
};


static const zend_function_entry php_v8_termination_exception_methods[] = {
        PHP_FE_END
};

static const zend_function_entry php_v8_abstract_resource_limit_exception_methods[] = {
        PHP_FE_END
};

static const zend_function_entry php_v8_time_limit_exception_methods[] = {
        PHP_FE_END
};

static const zend_function_entry php_v8_memory_limit_exception_methods[] = {
        PHP_FE_END
};

static const zend_function_entry php_v8_script_exception_methods[] = {
        PHP_FE_END
};


static const zend_function_entry php_v8_value_exception_methods[] = {
        PHP_FE_END
};


PHP_MINIT_FUNCTION(php_v8_exceptions) {
    zend_class_entry ce;

    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS "\\Exceptions", "GenericException", php_v8_exception_methods);
    php_v8_generic_exception_class_entry = zend_register_internal_class_ex(&ce, zend_exception_get_default());

    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS "\\Exceptions", "TryCatchException", php_v8_try_catch_exception_methods);
    php_v8_try_catch_exception_class_entry = zend_register_internal_class_ex(&ce, php_v8_generic_exception_class_entry);

    zend_declare_property_null(php_v8_try_catch_exception_class_entry, ZEND_STRL("isolate"),	ZEND_ACC_PRIVATE);
    zend_declare_property_null(php_v8_try_catch_exception_class_entry, ZEND_STRL("context"),	ZEND_ACC_PRIVATE);
    zend_declare_property_null(php_v8_try_catch_exception_class_entry, ZEND_STRL("try_catch"),	ZEND_ACC_PRIVATE);


    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS "\\Exceptions", "TerminationException", php_v8_termination_exception_methods);
    php_v8_termination_exception_class_entry = zend_register_internal_class_ex(&ce, php_v8_try_catch_exception_class_entry);

    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS "\\Exceptions", "AbstractResourceLimitException", php_v8_abstract_resource_limit_exception_methods);
    php_v8_abstract_resource_limit_exception_class_entry = zend_register_internal_class_ex(&ce, php_v8_termination_exception_class_entry);
    php_v8_abstract_resource_limit_exception_class_entry->ce_flags |= ZEND_ACC_EXPLICIT_ABSTRACT_CLASS;

    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS "\\Exceptions", "TimeLimitException", php_v8_time_limit_exception_methods);
    php_v8_time_limit_exception_class_entry = zend_register_internal_class_ex(&ce, php_v8_abstract_resource_limit_exception_class_entry);

    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS "\\Exceptions", "MemoryLimitException", php_v8_memory_limit_exception_methods);
    php_v8_memory_limit_exception_class_entry = zend_register_internal_class_ex(&ce, php_v8_abstract_resource_limit_exception_class_entry);

    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS "\\Exceptions", "ValueException", php_v8_value_exception_methods);
    php_v8_value_exception_class_entry = zend_register_internal_class_ex(&ce, php_v8_generic_exception_class_entry);

    // TODO: completely replace ScriptException with TryCatchException
    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS "\\Exceptions", "ScriptException", php_v8_script_exception_methods);
    php_v8_script_exception_class_entry = zend_register_internal_class_ex(&ce, php_v8_generic_exception_class_entry);

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
