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

#include "php_v8_property_attribute.h"
#include "php_v8.h"

zend_class_entry* php_v8_property_attribute_class_entry;
#define this_ce php_v8_property_attribute_class_entry


static const zend_function_entry php_v8_property_attribute_methods[] = {
        PHP_FE_END
};

PHP_MINIT_FUNCTION(php_v8_property_attribute) {
    zend_class_entry ce;
    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS, "PropertyAttribute", php_v8_property_attribute_methods);
    this_ce = zend_register_internal_class(&ce);

    zend_declare_class_constant_long(this_ce, ZEND_STRL("None"), v8::PropertyAttribute::None);
    zend_declare_class_constant_long(this_ce, ZEND_STRL("ReadOnly"), v8::PropertyAttribute::ReadOnly);
    zend_declare_class_constant_long(this_ce, ZEND_STRL("DontEnum"), v8::PropertyAttribute::DontEnum);
    zend_declare_class_constant_long(this_ce, ZEND_STRL("DontDelete"), v8::PropertyAttribute::DontDelete);

    return SUCCESS;
}
