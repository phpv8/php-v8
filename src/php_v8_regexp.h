/*
  +----------------------------------------------------------------------+
  | This file is part of the pinepain/php-v8 PHP extension.              |
  |                                                                      |
  | Copyright (c) 2015-2016 Bogdan Padalko <pinepain@gmail.com>          |
  |                                                                      |
  | Licensed under the MIT license: http://opensource.org/licenses/MIT   |
  |                                                                      |
  | For the full copyright and license information, please view the      |
  | LICENSE file that was distributed with this source or visit          |
  | http://opensource.org/licenses/MIT                                   |
  +----------------------------------------------------------------------+
*/

#ifndef PHP_V8_REGEXP_H
#define PHP_V8_REGEXP_H

#include "php_v8_value.h"
#include <v8.h>

extern "C" {
#include "php.h"

#ifdef ZTS
#include "TSRM.h"
#endif
}

extern zend_class_entry* php_v8_regexp_class_entry;
extern zend_class_entry* php_v8_regexp_flags_class_entry;

extern v8::Local<v8::RegExp> php_v8_value_get_regexp_local(v8::Isolate *isolate, php_v8_value_t *php_v8_value);

#define PHP_V8_REGEXP_FLAGS (v8::RegExp::Flags::kNone | v8::RegExp::Flags::kGlobal | v8::RegExp::Flags::kIgnoreCase | v8::RegExp::Flags::kMultiline | v8::RegExp::Flags::kSticky | v8::RegExp::Flags::kUnicode)


PHP_MINIT_FUNCTION(php_v8_regexp);

#endif //PHP_V8_REGEXP_H

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: noet sw=4 ts=4 fdm=marker
 * vim<600: noet sw=4 ts=4
 */
