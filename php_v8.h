/*
 * This file is part of the pinepain/php-v8 PHP extension.
 *
 * Copyright (c) 2015-2018 Bogdan Padalko <pinepain@gmail.com>
 *
 * Licensed under the MIT license: http://opensource.org/licenses/MIT
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source or visit
 * http://opensource.org/licenses/MIT
 */

#ifndef PHP_V8_H
#define PHP_V8_H

#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include <v8-version.h>
#include <v8.h>

extern "C" {
#include "php.h"

#ifdef ZTS
#include "TSRM.h"
#endif
};

extern zend_module_entry php_v8_module_entry;
#define phpext_v8_ptr &php_v8_module_entry

#ifndef PHP_V8_ICU_DATA_DIR
#define PHP_V8_ICU_DATA_DIR NULL
#endif

#ifndef PHP_V8_VERSION
#define PHP_V8_VERSION "0.2.2"
#endif

#ifndef PHP_V8_REVISION
#define PHP_V8_REVISION "release"
#endif


#define PHP_V8_NS "V8"

#ifndef PHP_V8_LIBV8_VERSION
#define PHP_V8_LIBV8_VERSION "undefined"
#endif

#if PHP_VERSION_ID < 70100
// should never get her, but just in case
#error PHP >= 7.1 required
#endif


ZEND_BEGIN_MODULE_GLOBALS(v8)
    bool v8_initialized;
    v8::Platform *platform;
ZEND_END_MODULE_GLOBALS(v8)

// Add zend_type support (new since PHP 7.2)
#ifdef ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX
    #define PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(name, return_reference, required_num_args, classname, allow_null) \
        ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(name, return_reference, required_num_args, classname, allow_null)

    #define PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(name, return_reference, required_num_args, type, allow_null) \
        ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(name, return_reference, required_num_args, type, allow_null)
#else
    #define PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(name, return_reference, required_num_args, classname, allow_null) \
        ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(name, return_reference, required_num_args, IS_OBJECT, #classname, allow_null)

    #define PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(name, return_reference, required_num_args, type, allow_null) \
        ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(name, return_reference, required_num_args, type, NULL, allow_null)
#endif

#define PHP_V8_ZEND_BEGIN_ARG_WITH_CONSTRUCTOR_INFO_EX(name, required_num_args) ZEND_BEGIN_ARG_INFO_EX(name, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, required_num_args)
#define PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_VOID_INFO_EX(name, required_num_args) ZEND_BEGIN_ARG_INFO_EX(name, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, required_num_args)
#define PHP_V8_ZEND_BEGIN_ARG_WITH_RETURN_MIXED_INFO_EX(name, required_num_args) ZEND_BEGIN_ARG_INFO_EX(name, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, required_num_args)

#define PHP_V8_IS_UP_AND_RUNNING() (zend_is_executing() && !CG(unclean_shutdown))

#define PHP_V8_ME(classname, name, flags)   PHP_ME(classname, name, arginfo_##name, flags)
#define PHP_V8_ABSTRACT_ME(classname, name) PHP_ABSTRACT_ME(classname, name, arginfo_##name)

/* Always refer to the globals in your function as PHP_V8_G(variable).
   You are encouraged to rename these macros something shorter, see
   examples in any other php module directory.
*/

#ifdef ZTS
#define PHP_V8_G(v) ZEND_MODULE_GLOBALS_ACCESSOR(v8, v)
#ifdef COMPILE_DL_V8
ZEND_TSRMLS_CACHE_EXTERN();
#endif
#else
#define PHP_V8_G(v) (v8_globals.v)
#endif

ZEND_EXTERN_MODULE_GLOBALS(v8);

#endif //PHP_V8_H
