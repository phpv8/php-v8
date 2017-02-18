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

#ifndef PHP_V8_COMPILE_OPTIONS_H
#define PHP_V8_COMPILE_OPTIONS_H

#include "php_v8_exceptions.h"
#include <v8.h>

extern "C" {
#include "php.h"

#ifdef ZTS
#include "TSRM.h"
#endif
}

extern zend_class_entry* php_v8_compile_options_class_entry;

#define PHP_V8_CHECK_COMPILER_OPTIONS_RANGE(options, message) \
    if (options < static_cast<zend_long>(v8::ScriptCompiler::CompileOptions::kNoCompileOptions) \
         || options > static_cast<zend_long>(v8::ScriptCompiler::CompileOptions::kConsumeCodeCache)) { \
        PHP_V8_THROW_VALUE_EXCEPTION(message); \
        return; \
    }

PHP_MINIT_FUNCTION (php_v8_compile_options);


#endif //PHP_V8_COMPILE_OPTIONS_H
