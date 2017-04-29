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

#ifndef PHP_V8_ISOLATE_LIMITS_H
#define PHP_V8_ISOLATE_LIMITS_H

typedef struct _php_v8_isolate_limits_t php_v8_isolate_limits_t;

#include "php_v8_exceptions.h"

#include <v8.h>
#include <atomic>
#include <mutex>
#include <thread>
#include <chrono>

extern void php_v8_isolate_limits_thread(php_v8_isolate_t *php_v8_isolate);
extern void php_v8_isolate_limits_maybe_start_timer(php_v8_isolate_t *php_v8_isolate);
extern void php_v8_isolate_limits_maybe_stop_timer(php_v8_isolate_t *php_v8_isolate);
extern void php_v8_isolate_limits_free(php_v8_isolate_t *php_v8_isolate);
extern void php_v8_isolate_limits_ctor(php_v8_isolate_t *php_v8_isolate);

extern void php_v8_isolate_limits_set_time_limit(php_v8_isolate_t *php_v8_isolate, double time_limit_in_seconds);
extern void php_v8_isolate_limits_set_memory_limit(php_v8_isolate_t *php_v8_isolate, size_t memory_limit_in_bytes);
extern void php_v8_isolate_limits_set_limits(php_v8_isolate_t *php_v8_isolate, double time_limit_in_seconds, size_t memory_limit_in_bytes);

#define PHP_V8_DECLARE_ISOLATE_LOCAL_ALIAS(i) v8::Isolate *isolate = (i);

#define PHP_V8_DECLARE_LIMITS(php_v8_isolate) \
    php_v8_isolate_limits_t *limits = &(php_v8_isolate)->limits;

#define PHP_V8_INIT_ISOLATE_LIMITS_ON_SCRIPT(php_v8_script) \
    PHP_V8_THROW_EXCEPTION_WHEN_LIMITS_HIT((php_v8_script)->php_v8_context); \
    php_v8_isolate_limits_maybe_start_timer((php_v8_script)->php_v8_isolate);

#define PHP_V8_INIT_ISOLATE_LIMITS_ON_OBJECT_VALUE(php_v8_value) \
    PHP_V8_THROW_EXCEPTION_WHEN_LIMITS_HIT((php_v8_value)->php_v8_context); \
    php_v8_isolate_limits_maybe_start_timer((php_v8_value)->php_v8_isolate);


#define PHP_V8_INIT_ISOLATE_LIMITS_ON_CONTEXT(php_v8_context) \
    PHP_V8_THROW_EXCEPTION_WHEN_LIMITS_HIT(php_v8_context); \
    php_v8_isolate_limits_maybe_start_timer((php_v8_context)->php_v8_isolate);


struct _php_v8_isolate_limits_t {
    std::atomic_bool active;
    uint32_t depth;

    std::thread *thread;
    std::mutex *mutex;

    std::chrono::time_point<std::chrono::high_resolution_clock> time_point;
    double time_limit;
    bool time_limit_hit;

    size_t memory_limit;
    bool memory_limit_hit;
    bool memory_limit_in_progress;
};


#endif //PHP_V8_ISOLATE_LIMITS_H
