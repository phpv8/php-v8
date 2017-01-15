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

#include "php_v8_property_handler_flags.h"
#include "php_v8.h"

zend_class_entry* php_v8_property_handler_flags_class_entry;
#define this_ce php_v8_property_handler_flags_class_entry


static const zend_function_entry php_v8_property_handler_flags_methods[] = {
        PHP_FE_END
};

PHP_MINIT_FUNCTION(php_v8_property_handler_flags) {
    zend_class_entry ce;
    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS, "PropertyHandlerFlags", php_v8_property_handler_flags_methods);
    this_ce = zend_register_internal_class(&ce);

    zend_declare_class_constant_long(this_ce, ZEND_STRL("kNone"), static_cast<zend_long>(v8::PropertyHandlerFlags::kNone));
    zend_declare_class_constant_long(this_ce, ZEND_STRL("kAllCanRead"), static_cast<zend_long>(v8::PropertyHandlerFlags::kAllCanRead));
    zend_declare_class_constant_long(this_ce, ZEND_STRL("kNonMasking"), static_cast<zend_long>(v8::PropertyHandlerFlags::kNonMasking));
    zend_declare_class_constant_long(this_ce, ZEND_STRL("kOnlyInterceptStrings"), static_cast<zend_long>(v8::PropertyHandlerFlags::kOnlyInterceptStrings));

    return SUCCESS;
}
