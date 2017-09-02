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

#include "php_v8_regexp.h"
#include "php_v8_object.h"
#include "php_v8_string.h"
#include "php_v8_value.h"
#include "php_v8_context.h"
#include "php_v8.h"

zend_class_entry *php_v8_regexp_class_entry;
zend_class_entry *php_v8_regexp_flags_class_entry;

#define this_ce php_v8_regexp_class_entry


static PHP_METHOD(RegExp, __construct) {
    zval rv;
    zval *php_v8_context_zv;
    zval *php_v8_string_zv;

    zend_long flags;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "oo|l", &php_v8_context_zv, &php_v8_string_zv, &flags) == FAILURE) {
        return;
    }

    PHP_V8_OBJECT_CONSTRUCT(getThis(), php_v8_context_zv, php_v8_context, php_v8_value);
    PHP_V8_VALUE_FETCH_WITH_CHECK(php_v8_string_zv, php_v8_pattern);

    v8::Local<v8::String> local_pattern = php_v8_value_get_local_as<v8::String>(php_v8_pattern);

    flags = flags? flags & PHP_V8_REGEXP_FLAGS : flags;

    v8::MaybeLocal<v8::RegExp> maybe_local_regexp = v8::RegExp::New(context, local_pattern, static_cast<v8::RegExp::Flags>(flags));

    PHP_V8_THROW_VALUE_EXCEPTION_WHEN_EMPTY(maybe_local_regexp, "Failed to create RegExp object");

    v8::Local<v8::RegExp> local_regexp = maybe_local_regexp.ToLocalChecked();

    php_v8_object_store_self_ptr(php_v8_value, local_regexp);

    php_v8_value->persistent->Reset(isolate, local_regexp);
}


static PHP_METHOD(RegExp, getSource) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);

    v8::Local<v8::String> local_string = php_v8_value_get_local_as<v8::RegExp>(php_v8_value)->GetSource();

    php_v8_get_or_create_value(return_value, local_string, php_v8_value->php_v8_isolate);
}

static PHP_METHOD(RegExp, getFlags) {
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_STORED_ISOLATE(php_v8_value);

    RETURN_LONG(static_cast<long>(php_v8_value_get_local_as<v8::RegExp>(php_v8_value)->GetFlags()));
}


PHP_V8_ZEND_BEGIN_ARG_WITH_CONSTRUCTOR_INFO_EX(arginfo___construct, 2)
                ZEND_ARG_OBJ_INFO(0, context, V8\\Context, 0)
                ZEND_ARG_OBJ_INFO(0, context, V8\\StringValue, 0)
                ZEND_ARG_TYPE_INFO(0, flags, IS_LONG, 1)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_getSource, ZEND_RETURN_VALUE, 0, V8\\StringValue, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_getFlags, ZEND_RETURN_VALUE, 0, IS_LONG, 0)
ZEND_END_ARG_INFO()


static const zend_function_entry php_v8_regexp_methods[] = {
        PHP_V8_ME(RegExp, __construct, ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)
        PHP_V8_ME(RegExp, getSource,   ZEND_ACC_PUBLIC)
        PHP_V8_ME(RegExp, getFlags,    ZEND_ACC_PUBLIC)

        PHP_FE_END
};

static const zend_function_entry php_v8_regexp_flags_methods[] = {
        PHP_FE_END
};

PHP_MINIT_FUNCTION(php_v8_regexp) {
    zend_class_entry ce;
    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS, "RegExpObject", php_v8_regexp_methods);
    this_ce = zend_register_internal_class_ex(&ce, php_v8_object_class_entry);

    #undef this_ce
    #define this_ce php_v8_regexp_flags_class_entry

    INIT_NS_CLASS_ENTRY(ce, "V8\\RegExpObject", "Flags", php_v8_regexp_flags_methods);
    this_ce = zend_register_internal_class(&ce);
    this_ce->ce_flags |= ZEND_ACC_FINAL;

    zend_declare_class_constant_long(this_ce, ZEND_STRL("NONE"),        v8::RegExp::Flags::kNone);
    zend_declare_class_constant_long(this_ce, ZEND_STRL("GLOBAL"),      v8::RegExp::Flags::kGlobal);
    zend_declare_class_constant_long(this_ce, ZEND_STRL("IGNORE_CASE"), v8::RegExp::Flags::kIgnoreCase);
    zend_declare_class_constant_long(this_ce, ZEND_STRL("MULTILINE"),   v8::RegExp::Flags::kMultiline);
    zend_declare_class_constant_long(this_ce, ZEND_STRL("STICKY"),      v8::RegExp::Flags::kSticky);
    zend_declare_class_constant_long(this_ce, ZEND_STRL("UNICODE"),     v8::RegExp::Flags::kUnicode);

    return SUCCESS;
}
