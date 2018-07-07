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

#include "php_v8_callback_info_interface.h"
#include "php_v8.h"


zend_class_entry *php_v8_callback_info_interface_class_entry;
#define this_ce php_v8_callback_info_interface_class_entry


PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_getIsolate, ZEND_RETURN_VALUE, 0, V8\\Isolate, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_getContext, ZEND_RETURN_VALUE, 0, V8\\Context, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_this, ZEND_RETURN_VALUE, 0, V8\\ObjectValue, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_holder, ZEND_RETURN_VALUE, 0, V8\\ObjectValue, 0)
ZEND_END_ARG_INFO()

PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_getReturnValue, ZEND_RETURN_VALUE, 0, V8\\ReturnValue, 0)
ZEND_END_ARG_INFO()


static const zend_function_entry php_v8_callback_info_interface_methods[] = {
        PHP_V8_ABSTRACT_ME(CallbackInfoInterface, getIsolate)
        PHP_V8_ABSTRACT_ME(CallbackInfoInterface, getContext)
        PHP_V8_ABSTRACT_ME(CallbackInfoInterface, this)
        PHP_V8_ABSTRACT_ME(CallbackInfoInterface, holder)
        PHP_V8_ABSTRACT_ME(CallbackInfoInterface, getReturnValue)
        PHP_FE_END
};

PHP_MINIT_FUNCTION (php_v8_callback_info_interface) {
    zend_class_entry ce;

    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS, "CallbackInfoInterface", php_v8_callback_info_interface_methods);
    this_ce = zend_register_internal_interface(&ce);

    return SUCCESS;
}
