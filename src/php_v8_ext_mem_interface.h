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

#ifndef PHP_V8_EXT_MEM_INTERFACE_H
#define PHP_V8_EXT_MEM_INTERFACE_H


extern "C" {
#include "php.h"

#ifdef ZTS
#include "TSRM.h"
#endif
}


extern zend_class_entry* php_v8_ext_mem_interface_ce;


extern void php_v8_ext_mem_interface_value_AdjustExternalAllocatedMemory(INTERNAL_FUNCTION_PARAMETERS);
extern void php_v8_ext_mem_interface_value_GetExternalAllocatedMemory(INTERNAL_FUNCTION_PARAMETERS);

extern void php_v8_ext_mem_interface_function_template_AdjustExternalAllocatedMemory(INTERNAL_FUNCTION_PARAMETERS);
extern void php_v8_ext_mem_interface_function_template_GetExternalAllocatedMemory(INTERNAL_FUNCTION_PARAMETERS);

extern void php_v8_ext_mem_interface_object_template_AdjustExternalAllocatedMemory(INTERNAL_FUNCTION_PARAMETERS);
extern void php_v8_ext_mem_interface_object_template_GetExternalAllocatedMemory(INTERNAL_FUNCTION_PARAMETERS);

PHP_MINIT_FUNCTION(php_v8_ext_mem_interface);

#endif //PHP_V8_EXT_MEM_INTERFACE_H

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: noet sw=4 ts=4 fdm=marker
 * vim<600: noet sw=4 ts=4
 */
