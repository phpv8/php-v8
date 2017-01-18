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

#include "php_v8_name.h"
#include "php_v8_primitive.h"
#include "php_v8_value.h"
#include "php_v8.h"

zend_class_entry* php_v8_name_class_entry;
#define this_ce php_v8_name_class_entry

v8::Local<v8::Name> php_v8_value_get_name_local(v8::Isolate *isolate, php_v8_value_t *php_v8_value) {
    return v8::Local<v8::Name>::Cast(php_v8_value_get_value_local(isolate, php_v8_value));
};


static PHP_METHOD(V8Name, GetIdentityHash)
{
    if (zend_parse_parameters_none() == FAILURE) {
        return;
    }

    PHP_V8_VALUE_FETCH_WITH_CHECK(getThis(), php_v8_value);
    PHP_V8_ENTER_ISOLATE(php_v8_value->php_v8_isolate);

    v8::Local<v8::Name> local_name = php_v8_value_get_name_local(isolate, php_v8_value);

    if (!local_name->IsString() && !local_name->IsSymbol()) {
        RETURN_LONG(0);
    }

    RETURN_LONG(local_name->GetIdentityHash());
}


PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_v8_name_GetIdentityHash, ZEND_RETURN_VALUE, 0, IS_LONG, 0)
ZEND_END_ARG_INFO()


static const zend_function_entry php_v8_name_methods[] = {
    PHP_ME(V8Name, GetIdentityHash, arginfo_v8_name_GetIdentityHash, ZEND_ACC_PUBLIC)

    PHP_FE_END
};


PHP_MINIT_FUNCTION(php_v8_name)
{
    zend_class_entry ce;
    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS, "NameValue", php_v8_name_methods);
    this_ce = zend_register_internal_class_ex(&ce, php_v8_primitive_class_entry);

    return SUCCESS;
}
