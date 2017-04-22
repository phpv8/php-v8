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

#include "php_v8_enums.h"
#include "php_v8.h"

zend_class_entry *php_v8_access_control_class_entry;
zend_class_entry* php_v8_constructor_behavior_class_entry;
zend_class_entry* php_v8_integrity_level_class_entry;
zend_class_entry* php_v8_property_attribute_class_entry;
zend_class_entry* php_v8_property_handler_flags_class_entry;
zend_class_entry *php_v8_property_filter_class_entry;
zend_class_entry *php_v8_key_collection_mode_class_entry;
zend_class_entry *php_v8_index_filter_class_entry;


static const zend_function_entry php_v8_enum_methods[] = {
        PHP_FE_END
};


PHP_MINIT_FUNCTION (php_v8_enums) {
    zend_class_entry ce;

    // v8::AccessControl
    #define this_ce php_v8_access_control_class_entry
    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS, "AccessControl", php_v8_enum_methods);
    this_ce = zend_register_internal_class(&ce);

    zend_declare_class_constant_long(this_ce, ZEND_STRL("DEFAULT_ACCESS"), v8::AccessControl::DEFAULT);
    zend_declare_class_constant_long(this_ce, ZEND_STRL("ALL_CAN_READ"), v8::AccessControl::ALL_CAN_READ);
    zend_declare_class_constant_long(this_ce, ZEND_STRL("ALL_CAN_WRITE"), v8::AccessControl::ALL_CAN_WRITE);
    #undef this_ce

    //v8::ConstructorBehavior
    #define this_ce php_v8_constructor_behavior_class_entry
    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS, "ConstructorBehavior", php_v8_enum_methods);
    this_ce = zend_register_internal_class(&ce);

    zend_declare_class_constant_long(this_ce, ZEND_STRL("kThrow"), static_cast<long>(v8::ConstructorBehavior::kThrow));
    zend_declare_class_constant_long(this_ce, ZEND_STRL("kAllow"), static_cast<long>(v8::ConstructorBehavior::kAllow));

    #undef this_ce

    // v8::IntegrityLevel
    #define this_ce php_v8_integrity_level_class_entry
    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS, "IntegrityLevel", php_v8_enum_methods);
    this_ce = zend_register_internal_class(&ce);

    zend_declare_class_constant_long(this_ce, ZEND_STRL("kFrozen"), static_cast<zend_long>(v8::IntegrityLevel::kFrozen));
    zend_declare_class_constant_long(this_ce, ZEND_STRL("kSealed"), static_cast<zend_long>(v8::IntegrityLevel::kSealed));
    #undef this_ce

    // v8::PropertyAttribute
    #define this_ce php_v8_property_attribute_class_entry
    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS, "PropertyAttribute", php_v8_enum_methods);
    this_ce = zend_register_internal_class(&ce);

    zend_declare_class_constant_long(this_ce, ZEND_STRL("None"), v8::PropertyAttribute::None);
    zend_declare_class_constant_long(this_ce, ZEND_STRL("ReadOnly"), v8::PropertyAttribute::ReadOnly);
    zend_declare_class_constant_long(this_ce, ZEND_STRL("DontEnum"), v8::PropertyAttribute::DontEnum);
    zend_declare_class_constant_long(this_ce, ZEND_STRL("DontDelete"), v8::PropertyAttribute::DontDelete);

    #undef this_ce

    // v8::PropertyHandlerFlags
    #define this_ce php_v8_property_handler_flags_class_entry
    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS, "PropertyHandlerFlags", php_v8_enum_methods);
    this_ce = zend_register_internal_class(&ce);

    zend_declare_class_constant_long(this_ce, ZEND_STRL("kNone"), static_cast<zend_long>(v8::PropertyHandlerFlags::kNone));
    zend_declare_class_constant_long(this_ce, ZEND_STRL("kAllCanRead"), static_cast<zend_long>(v8::PropertyHandlerFlags::kAllCanRead));
    zend_declare_class_constant_long(this_ce, ZEND_STRL("kNonMasking"), static_cast<zend_long>(v8::PropertyHandlerFlags::kNonMasking));
    zend_declare_class_constant_long(this_ce, ZEND_STRL("kOnlyInterceptStrings"), static_cast<zend_long>(v8::PropertyHandlerFlags::kOnlyInterceptStrings));
    #undef this_ce

    // v8::PropertyFilter
    #define this_ce php_v8_property_filter_class_entry
    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS, "PropertyFilter", php_v8_enum_methods);
    this_ce = zend_register_internal_class(&ce);

    zend_declare_class_constant_long(this_ce, ZEND_STRL("ALL_PROPERTIES"), v8::PropertyFilter::ALL_PROPERTIES);
    zend_declare_class_constant_long(this_ce, ZEND_STRL("ONLY_WRITABLE"), v8::PropertyFilter::ONLY_WRITABLE);
    zend_declare_class_constant_long(this_ce, ZEND_STRL("ONLY_ENUMERABLE"), v8::PropertyFilter::ONLY_ENUMERABLE);
    zend_declare_class_constant_long(this_ce, ZEND_STRL("ONLY_CONFIGURABLE"), v8::PropertyFilter::ONLY_CONFIGURABLE);
    zend_declare_class_constant_long(this_ce, ZEND_STRL("SKIP_STRINGS"), v8::PropertyFilter::SKIP_STRINGS);
    zend_declare_class_constant_long(this_ce, ZEND_STRL("SKIP_SYMBOLS"), v8::PropertyFilter::SKIP_SYMBOLS);
    #undef this_ce

    // v8::KeyCollectionMode
    #define this_ce php_v8_key_collection_mode_class_entry
    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS, "KeyCollectionMode", php_v8_enum_methods);
    this_ce = zend_register_internal_class(&ce);

    zend_declare_class_constant_long(this_ce, ZEND_STRL("kOwnOnly"), static_cast<zend_long>(v8::KeyCollectionMode::kOwnOnly));
    zend_declare_class_constant_long(this_ce, ZEND_STRL("kIncludePrototypes"), static_cast<zend_long>(v8::KeyCollectionMode::kIncludePrototypes));
    #undef this_ce

    // v8::IndexFilter
    #define this_ce php_v8_index_filter_class_entry
    INIT_NS_CLASS_ENTRY(ce, PHP_V8_NS, "IndexFilter", php_v8_enum_methods);
    this_ce = zend_register_internal_class(&ce);

    zend_declare_class_constant_long(this_ce, ZEND_STRL("kIncludeIndices"), static_cast<zend_long>(v8::IndexFilter::kIncludeIndices));
    zend_declare_class_constant_long(this_ce, ZEND_STRL("kSkipIndices"), static_cast<zend_long>(v8::IndexFilter::kSkipIndices));
    #undef this_ce

    return SUCCESS;
}
