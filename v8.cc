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

#include "php_v8.h"

#include "php_v8_isolate.h"
#include "php_v8_startup_data.h"
#include "php_v8_heap_statistics.h"
#include "php_v8_exceptions.h"
#include "php_v8_exception.h"
#include "php_v8_try_catch.h"
#include "php_v8_message.h"
#include "php_v8_stack_frame.h"
#include "php_v8_stack_trace.h"
#include "php_v8_script_origin.h"
#include "php_v8_script_origin_options.h"
#include "php_v8_context.h"
#include "php_v8_object_template.h"
#include "php_v8_function_template.h"
#include "php_v8_script.h"
#include "php_v8_null.h"
#include "php_v8_boolean.h"
#include "php_v8_symbol.h"
#include "php_v8_string.h"
#include "php_v8_name.h"
#include "php_v8_number.h"
#include "php_v8_integer.h"
#include "php_v8_int32.h"
#include "php_v8_uint32.h"
#include "php_v8_primitive.h"
#include "php_v8_object.h"
#include "php_v8_integrity_level.h"
#include "php_v8_function.h"
#include "php_v8_array.h"
#include "php_v8_date.h"
#include "php_v8_regexp.h"
#include "php_v8_number_object.h"
#include "php_v8_boolean_object.h"
#include "php_v8_string_object.h"
#include "php_v8_symbol_object.h"
#include "php_v8_property_attribute.h"
#include "php_v8_template.h"
#include "php_v8_return_value.h"
#include "php_v8_callback_info.h"
#include "php_v8_property_callback_info.h"
#include "php_v8_function_callback_info.h"
#include "php_v8_access_control.h"
#include "php_v8_property_handler_flags.h"
#include "php_v8_named_property_handler_configuration.h"
#include "php_v8_indexed_property_handler_configuration.h"
#include "php_v8_access_type.h"

#include "php_v8_value.h"
#include "php_v8_data.h"
#include "php_v8_ext_mem_interface.h"

#include <v8.h>

extern "C" {
#include "ext/standard/info.h"
};



ZEND_DECLARE_MODULE_GLOBALS(v8)


/* True global resources - no need for thread safety here */
static int le_v8;

/* {{{ PHP_INI
 */
/* Remove comments and fill if you need to have entries in php.ini
PHP_INI_BEGIN()
    STD_PHP_INI_ENTRY("v8.global_value",      "42", PHP_INI_ALL, OnUpdateLong, global_value, zend_PHP_V8_Globals, PHP_V8_Globals)
    STD_PHP_INI_ENTRY("v8.global_string", "foobar", PHP_INI_ALL, OnUpdateString, global_string, zend_PHP_V8_Globals, PHP_V8_Globals)
PHP_INI_END()
*/
/* }}} */


/* {{{ PHP_MINIT_FUNCTION
 */
PHP_MINIT_FUNCTION(v8)
{
    PHP_MINIT(php_v8_exceptions)(INIT_FUNC_ARGS_PASSTHRU);    /* Exceptions */
    PHP_MINIT(php_v8_ext_mem_interface)(INIT_FUNC_ARGS_PASSTHRU);    /* AdjustableExternalMemoryInterface */

    PHP_MINIT(php_v8_heap_statistics)(INIT_FUNC_ARGS_PASSTHRU);
    PHP_MINIT(php_v8_startup_data)(INIT_FUNC_ARGS_PASSTHRU);
    PHP_MINIT(php_v8_isolate)(INIT_FUNC_ARGS_PASSTHRU);
    PHP_MINIT(php_v8_context)(INIT_FUNC_ARGS_PASSTHRU);
    PHP_MINIT(php_v8_script)(INIT_FUNC_ARGS_PASSTHRU);

    PHP_MINIT(php_v8_exception)(INIT_FUNC_ARGS_PASSTHRU);
    PHP_MINIT(php_v8_try_catch)(INIT_FUNC_ARGS_PASSTHRU);
    PHP_MINIT(php_v8_message)(INIT_FUNC_ARGS_PASSTHRU);
    PHP_MINIT(php_v8_stack_frame)(INIT_FUNC_ARGS_PASSTHRU);
    PHP_MINIT(php_v8_stack_trace)(INIT_FUNC_ARGS_PASSTHRU);
    PHP_MINIT(php_v8_script_origin_options)(INIT_FUNC_ARGS_PASSTHRU);
    PHP_MINIT(php_v8_script_origin)(INIT_FUNC_ARGS_PASSTHRU);

    PHP_MINIT(php_v8_data)(INIT_FUNC_ARGS_PASSTHRU);
    PHP_MINIT(php_v8_value)(INIT_FUNC_ARGS_PASSTHRU);
    PHP_MINIT(php_v8_primitive)(INIT_FUNC_ARGS_PASSTHRU);
    PHP_MINIT(php_v8_null)(INIT_FUNC_ARGS_PASSTHRU);
    PHP_MINIT(php_v8_boolean)(INIT_FUNC_ARGS_PASSTHRU);
    PHP_MINIT(php_v8_name)(INIT_FUNC_ARGS_PASSTHRU);
    PHP_MINIT(php_v8_string)(INIT_FUNC_ARGS_PASSTHRU);
    PHP_MINIT(php_v8_symbol)(INIT_FUNC_ARGS_PASSTHRU);
    PHP_MINIT(php_v8_number)(INIT_FUNC_ARGS_PASSTHRU);
    PHP_MINIT(php_v8_integer)(INIT_FUNC_ARGS_PASSTHRU);
    PHP_MINIT(php_v8_int32)(INIT_FUNC_ARGS_PASSTHRU);
    PHP_MINIT(php_v8_uint32)(INIT_FUNC_ARGS_PASSTHRU);
    PHP_MINIT(php_v8_integrity_level)(INIT_FUNC_ARGS_PASSTHRU);
    PHP_MINIT(php_v8_object)(INIT_FUNC_ARGS_PASSTHRU);
    PHP_MINIT(php_v8_function)(INIT_FUNC_ARGS_PASSTHRU);
    PHP_MINIT(php_v8_array)(INIT_FUNC_ARGS_PASSTHRU);
    PHP_MINIT(php_v8_date)(INIT_FUNC_ARGS_PASSTHRU);
    PHP_MINIT(php_v8_regexp)(INIT_FUNC_ARGS_PASSTHRU);

    PHP_MINIT(php_v8_number_object)(INIT_FUNC_ARGS_PASSTHRU);
    PHP_MINIT(php_v8_boolean_object)(INIT_FUNC_ARGS_PASSTHRU);
    PHP_MINIT(php_v8_string_object)(INIT_FUNC_ARGS_PASSTHRU);
    PHP_MINIT(php_v8_symbol_object)(INIT_FUNC_ARGS_PASSTHRU);

    PHP_MINIT(php_v8_template)(INIT_FUNC_ARGS_PASSTHRU);
    PHP_MINIT(php_v8_object_template)(INIT_FUNC_ARGS_PASSTHRU);
    PHP_MINIT(php_v8_function_template)(INIT_FUNC_ARGS_PASSTHRU);


    PHP_MINIT(php_v8_property_attribute)(INIT_FUNC_ARGS_PASSTHRU); /* Helper class, holds constants for v8 internals similarity/compatibility */
    PHP_MINIT(php_v8_access_control)(INIT_FUNC_ARGS_PASSTHRU); /* Helper class, holds constants */
    PHP_MINIT(php_v8_return_value)(INIT_FUNC_ARGS_PASSTHRU);

    PHP_MINIT(php_v8_callback_info)(INIT_FUNC_ARGS_PASSTHRU);
    PHP_MINIT(php_v8_property_callback_info)(INIT_FUNC_ARGS_PASSTHRU); /* PropertyCallbackInfo inherits CallbackInfo */
    PHP_MINIT(php_v8_function_callback_info)(INIT_FUNC_ARGS_PASSTHRU); /* FunctionCallbackInfo inherits CallbackInfo */

    PHP_MINIT(php_v8_property_handler_flags)(INIT_FUNC_ARGS_PASSTHRU); /* Helper class, holds constants */
    PHP_MINIT(php_v8_named_property_handler_configuration)(INIT_FUNC_ARGS_PASSTHRU);
    PHP_MINIT(php_v8_indexed_property_handler_configuration)(INIT_FUNC_ARGS_PASSTHRU);

    PHP_MINIT(php_v8_access_type)(INIT_FUNC_ARGS_PASSTHRU); /* Helper class, holds constants */

    /* If you have INI entries, uncomment these lines
    REGISTER_INI_ENTRIES();
    */

    return SUCCESS;
}
/* }}} */

/* {{{ PHP_MSHUTDOWN_FUNCTION
 */
PHP_MSHUTDOWN_FUNCTION(v8)
{
    /* uncomment this line if you have INI entries
    UNREGISTER_INI_ENTRIES();
    */
    return SUCCESS;
}
/* }}} */

/* Remove if there's nothing to do at request start */
/* {{{ PHP_RINIT_FUNCTION
 */
PHP_RINIT_FUNCTION(v8)
{
    return SUCCESS;
}
/* }}} */

/* Remove if there's nothing to do at request end */
/* {{{ PHP_RSHUTDOWN_FUNCTION
 */
PHP_RSHUTDOWN_FUNCTION(v8)
{
    return SUCCESS;
}
/* }}} */

/* {{{ PHP_MINFO_FUNCTION
 */
PHP_MINFO_FUNCTION(v8)
{
    php_info_print_table_start();
    php_info_print_table_header(2, "V8 support", "enabled");
    php_info_print_table_row(2, "Version", PHP_V8_VERSION);
    php_info_print_table_row(2, "Revision", PHP_V8_REVISION);
    php_info_print_table_row(2, "Compiled", __DATE__ " @ "  __TIME__);
    php_info_print_table_end();

    php_info_print_table_start();
    php_info_print_table_row(2, "V8 Engine Compiled Version", PHP_V8_LIBV8_VERSION);
    php_info_print_table_row(2, "V8 Engine Linked Version", v8::V8::GetVersion());
    php_info_print_table_end();

    /* Remove comments if you have entries in php.ini
    DISPLAY_INI_ENTRIES();
    */
}
/* }}} */


/* {{{ PHP_GINIT_FUNCTION
 */
static PHP_GINIT_FUNCTION(v8)
{
#if defined(COMPILE_DL_V8) && defined(ZTS)
    ZEND_TSRMLS_CACHE_UPDATE();
#endif
    v8_globals->v8_initialized = false;
}
/* }}} */

/* {{{ PHP_GSHUTDOWN_FUNCTION
 */
static PHP_GSHUTDOWN_FUNCTION(v8)
{
}
/* }}} */



/* {{{ php_v8_functions[]
 *
 * Every user visible function must have an entry in php_v8_functions[].
 */
const zend_function_entry php_v8_functions[] = {
    PHP_FE_END    /* Must be the last line in php_v8_functions[] */
};
/* }}} */

/* {{{ php_v8_module_entry
 */
zend_module_entry php_v8_module_entry = {
    STANDARD_MODULE_HEADER,
    "v8",
    php_v8_functions,
    PHP_MINIT(v8),
    PHP_MSHUTDOWN(v8),
    PHP_RINIT(v8),        /* Replace with NULL if there's nothing to do at request start */
    PHP_RSHUTDOWN(v8),    /* Replace with NULL if there's nothing to do at request end */
    PHP_MINFO(v8),
    PHP_V8_VERSION,
    PHP_MODULE_GLOBALS(v8),
    PHP_GINIT(v8),
    PHP_GSHUTDOWN(v8),
    NULL,
    STANDARD_MODULE_PROPERTIES_EX

};
/* }}} */


#ifdef COMPILE_DL_V8
#ifdef ZTS
ZEND_TSRMLS_CACHE_DEFINE();
#endif
ZEND_GET_MODULE(php_v8)
#endif
