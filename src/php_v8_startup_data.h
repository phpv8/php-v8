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

#ifndef PHP_V8_STARTUP_DATA_H
#define PHP_V8_STARTUP_DATA_H

typedef struct _php_v8_startup_data_t php_v8_startup_data_t;

namespace phpv8 {
    class StartupData;
}

#include "php_v8_exceptions.h"
#include <v8.h>

extern "C" {
#include "php.h"

#ifdef ZTS
#include "TSRM.h"
#endif
}

extern zend_class_entry* php_v8_startup_data_class_entry;

inline php_v8_startup_data_t * php_v8_startup_data_fetch_object(zend_object *obj);

#define PHP_V8_STARTUP_DATA_FETCH(zv) php_v8_startup_data_fetch_object(Z_OBJ_P(zv))
#define PHP_V8_STARTUP_DATA_FETCH_INTO(pzval, into) php_v8_startup_data_t *(into) = PHP_V8_STARTUP_DATA_FETCH((pzval))


namespace phpv8 {
    class StartupData {
    public:
        StartupData(v8::StartupData *data = nullptr) : _data(data), in_use(1) {}

        inline v8::StartupData *acquire() {
            assert(in_use < UINT32_MAX);
            in_use++;
            return _data;
        }

        inline bool hasData() {
            return _data && _data->raw_size > 0;
        }

        inline v8::StartupData *data() {
            return _data;
        }

        bool release() {
            assert(in_use > 0);
            return --in_use == 0;
        }

        ~StartupData() {
            if (_data) {
                delete _data;
            }
        }

        v8::StartupData* operator*() const { return _data; }
    private:
        v8::StartupData *_data;
        uint32_t in_use;
    };
}


struct _php_v8_startup_data_t {
    phpv8::StartupData *blob;
    zend_object std;
};

inline php_v8_startup_data_t * php_v8_startup_data_fetch_object(zend_object *obj) {
    return (php_v8_startup_data_t *) ((char *) obj - XtOffsetOf(php_v8_startup_data_t, std));
}

PHP_MINIT_FUNCTION(php_v8_startup_data);

#endif //PHP_V8_STARTUP_DATA_H
