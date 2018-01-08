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

#ifndef PHP_V8_SCRIPT_COMPILER_H
#define PHP_V8_SCRIPT_COMPILER_H


extern "C" {
#include "php.h"

#ifdef ZTS
#include "TSRM.h"
#endif
}

#include "v8.h"

extern zend_class_entry *php_v8_script_compiler_class_entry;

#define PHP_V8_CHECK_COMPILER_OPTIONS_RANGE(options, message) \
    if ((options) < static_cast<zend_long>(v8::ScriptCompiler::CompileOptions::kNoCompileOptions) \
        || (options) > static_cast<zend_long>(v8::ScriptCompiler::CompileOptions::kEagerCompile)) { \
        PHP_V8_THROW_VALUE_EXCEPTION(message); \
        return; \
    }


PHP_MINIT_FUNCTION(php_v8_script_compiler);

#endif //PHP_V8_SCRIPT_COMPILER_H
