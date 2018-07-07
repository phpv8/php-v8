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

#ifndef PHP_V8_ENUMS_H
#define PHP_V8_ENUMS_H

#include <v8.h>

extern "C" {
#include "php.h"

#ifdef ZTS
#include "TSRM.h"
#endif
}

extern zend_class_entry* php_v8_access_control_class_entry;
extern zend_class_entry* php_v8_constructor_behavior_class_entry;
extern zend_class_entry* php_v8_integrity_level_class_entry;
extern zend_class_entry* php_v8_property_attribute_class_entry;
extern zend_class_entry* php_v8_property_handler_flags_class_entry;
extern zend_class_entry* php_v8_property_filter_class_entry;
extern zend_class_entry* php_v8_key_collection_mode_class_entry;
extern zend_class_entry* php_v8_index_filter_class_entry;
extern zend_class_entry *php_v8_rail_mode_class_entry;


#define PHP_V8_ACCESS_CONTROL_FLAGS ( 0 \
    | v8::AccessControl::DEFAULT        \
    | v8::AccessControl::ALL_CAN_READ   \
    | v8::AccessControl::ALL_CAN_WRITE  \
)

#define PHP_V8_CONSTRUCTOR_BEHAVIOR_FLAGS ( 0               \
    | static_cast<long>(v8::ConstructorBehavior::kAllow)    \
    | static_cast<long>(v8::ConstructorBehavior::kThrow)    \
)

#define PHP_V8_INTEGRITY_LEVEL_FLAGS ( 0                \
    | static_cast<long>(v8::IntegrityLevel::kFrozen)    \
    | static_cast<long>(v8::IntegrityLevel::kSealed)    \
)

#define PHP_V8_PROPERTY_ATTRIBUTE_FLAGS ( 0 \
    | v8::PropertyAttribute::None           \
    | v8::PropertyAttribute::ReadOnly       \
    | v8::PropertyAttribute::DontEnum       \
    | v8::PropertyAttribute::DontDelete     \
)

#define PHP_V8_PROPERTY_HANDLER_FLAGS ( 0                                   \
    | static_cast<long>(v8::PropertyHandlerFlags::kNone)                    \
    | static_cast<long>(v8::PropertyHandlerFlags::kAllCanRead)              \
    | static_cast<long>(v8::PropertyHandlerFlags::kNonMasking)              \
    | static_cast<long>(v8::PropertyHandlerFlags::kOnlyInterceptStrings)    \
)

#define PHP_V8_PROPERTY_FILTER_FLAGS ( 0    \
  | v8::PropertyFilter::ALL_PROPERTIES      \
  | v8::PropertyFilter::ONLY_WRITABLE       \
  | v8::PropertyFilter::ONLY_ENUMERABLE     \
  | v8::PropertyFilter::ONLY_CONFIGURABLE   \
  | v8::PropertyFilter::SKIP_STRINGS        \
  | v8::PropertyFilter::SKIP_SYMBOLS        \
)

#define PHP_V8_KEY_COLLECTION_MODE_FLAGS ( 0                      \
  | static_cast<long>(v8::KeyCollectionMode::kOwnOnly)            \
  | static_cast<long>(v8::KeyCollectionMode::kIncludePrototypes)  \
)

#define PHP_V8_INDEX_FILTER_FLAGS ( 0                   \
  | static_cast<long>(v8::IndexFilter::kIncludeIndices) \
  | static_cast<long>(v8::IndexFilter::kSkipIndices)    \
)

PHP_MINIT_FUNCTION (php_v8_enums);

#endif //PHP_V8_ENUMS_H
