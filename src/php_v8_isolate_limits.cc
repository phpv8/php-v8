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

#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include "php_v8_isolate.h"
#include "php_v8_isolate_limits.h"

#define PHP_V8_TIME_SLEEP_MILLISECONDS 10

//#define PHP_V8_DEBUG_EXECUTION 1
#define one_mb (1024.0 * 1024.0)
#define kb(sz) ((sz)/1024.0)
#define mb(sz) (kb(sz)/1024.0)
#define has(v, str) (v ? "has " str : "no " str)
#define is(v) (v ? "yes" : "no")

#ifdef PHP_V8_DEBUG_EXECUTION
#define php_v8_debug_execution(format, ...) fprintf(stderr, (format), ##__VA_ARGS__);
#else
#define php_v8_debug_execution(format, ...)
#endif

static void php_v8_isolate_limits_interrupt_handler(v8::Isolate *isolate, void *data) {
    php_v8_isolate_t *php_v8_isolate = static_cast<php_v8_isolate_t *>(data);
    php_v8_isolate_limits_t *limits = &php_v8_isolate->limits;

    v8::Locker locker(isolate);
    v8::HeapStatistics hs;

    bool send_low_mem_notification = false;
    bool has_low_mem_notification_sent = false;

    std::lock_guard<std::mutex> lock(*limits->mutex);

    do {
        if (send_low_mem_notification) {
            isolate->LowMemoryNotification();
            has_low_mem_notification_sent = true;
        }

        isolate->GetHeapStatistics(&hs);
        php_v8_debug_execution("Memory usage: %.2fmb used, %.2fmb limit\n", mb(hs.used_heap_size()), mb(limits->memory_limit));

        if (limits->memory_limit > 0 && hs.used_heap_size() > limits->memory_limit) {
            php_v8_debug_execution("  Memory limit reached: %.2fmb used, %.2fmb limit\n", mb(hs.used_heap_size()), mb(limits->memory_limit));
            if (has_low_mem_notification_sent) {
                php_v8_debug_execution("    terminating: %.2fmb used, %.2fmb limit\n", mb(hs.used_heap_size()), mb(limits->memory_limit));
                isolate->TerminateExecution();
                limits->active = false;
                limits->memory_limit_hit = true;
            } else {
                php_v8_debug_execution("    requesting gc: %.2fmb used, %.2fmb limit\n", mb(hs.used_heap_size()), mb(limits->memory_limit));
                // send low memory notification, maybe GC free some space for us so we'll be staying under the limits
                send_low_mem_notification = true;
            }
        }

    } while(send_low_mem_notification != has_low_mem_notification_sent);

    limits->memory_limit_in_progress = false;
}

void php_v8_isolate_limits_thread(php_v8_isolate_t *php_v8_isolate) {
    php_v8_isolate_limits_t *limits = &php_v8_isolate->limits;
    std::chrono::time_point<std::chrono::high_resolution_clock> now;
    std::chrono::milliseconds duration(PHP_V8_TIME_SLEEP_MILLISECONDS);

    while (true) {
        limits->mutex->lock();

        if (limits->active && limits->time_limit > 0) {
            now = std::chrono::high_resolution_clock::now();

            if (now > limits->time_point) {
                limits->active = false;
                php_v8_isolate->isolate->TerminateExecution();
                limits->time_limit_hit = true;
            }
        }

        if (limits->active && limits->memory_limit > 0 && !limits->memory_limit_in_progress) {
            php_v8_debug_execution("Checking memory limit\n");
            /*
             * For memory limit check we need to call v8::Isolate::GetHeapStatistics() which is not thread-safe and
             * thus needs v8::Locker on isolate, so we request isolate to interrupt execution and perform our checks
             */
            php_v8_isolate->isolate->RequestInterrupt(php_v8_isolate_limits_interrupt_handler, php_v8_isolate);
            limits->memory_limit_in_progress = true;
        }

        limits->mutex->unlock();

        if (!limits->active) {
            return;
        }

        std::this_thread::sleep_for(duration);
    }
}

void php_v8_isolate_limits_maybe_start_timer(php_v8_isolate_t *php_v8_isolate) {
    php_v8_isolate_limits_t *limits = &php_v8_isolate->limits;

    assert (limits->depth < UINT32_MAX);

    if (!limits->mutex) {
        limits->depth++;
        return;
    }

    std::lock_guard<std::mutex> lock(*limits->mutex);
    limits->depth++;

    if (limits->active && !limits->thread) {
        limits->thread = new std::thread(php_v8_isolate_limits_thread, php_v8_isolate);
    }
}

void php_v8_isolate_limits_maybe_stop_timer(php_v8_isolate_t *php_v8_isolate) {
    php_v8_isolate_limits_t *limits = &php_v8_isolate->limits;

    assert (limits->depth > 0);

    php_v8_debug_execution("Maybe stopping timer: %s, %s\n", has(limits->mutex, "mutex"), has(limits->thread, "thread"));
    php_v8_debug_execution("    active: %s, depth: %d, time limit hit: %d, memory limit hit: %d\n", is(limits->active), limits->depth, limits->time_limit_hit, limits->memory_limit_hit);

    if (!limits->mutex) {
        limits->depth--;
        return;
    }

    limits->mutex->lock();
    limits->depth--;

    bool active = limits->active;

    limits->active = limits->active && limits->depth;

    limits->mutex->unlock();

    if (!limits->active && limits->thread) {
        limits->thread->join();
        delete limits->thread;
        limits->thread = NULL;
    }

    limits->active = active;
}

void php_v8_isolate_limits_free(php_v8_isolate_t *php_v8_isolate) {
    PHP_V8_DECLARE_LIMITS(php_v8_isolate);

    limits->active = false;

    if (limits->thread) {
        limits->thread->join();
        delete limits->thread;
    }

    if (limits->mutex) {
        delete limits->mutex;
    }
}

void php_v8_isolate_limits_ctor(php_v8_isolate_t *php_v8_isolate) {
    PHP_V8_DECLARE_LIMITS(php_v8_isolate);

    limits->thread = NULL;
    limits->mutex = NULL;
    limits->depth = 0;
}

void php_v8_isolate_maybe_update_limits_hit(php_v8_isolate_t *php_v8_isolate) {
    PHP_V8_DECLARE_LIMITS(php_v8_isolate);
    if (limits->time_limit) {
        zend_update_property_bool(php_v8_isolate_class_entry, &php_v8_isolate->this_ptr, ZEND_STRL("time_limit_hit"), limits->time_limit_hit);
    }
    if (limits->memory_limit) {
        zend_update_property_bool(php_v8_isolate_class_entry, &php_v8_isolate->this_ptr, ZEND_STRL("memory_limit_hit"), limits->memory_limit_hit);
    }
}

void php_v8_isolate_limits_set_time_limit(php_v8_isolate_t *php_v8_isolate, double time_limit_in_seconds) {
    PHP_V8_DECLARE_ISOLATE(php_v8_isolate);
    PHP_V8_DECLARE_LIMITS(php_v8_isolate);

    assert(time_limit_in_seconds >=0);

    v8::Locker locker(isolate);

    if (!limits->mutex) {
        limits->mutex = new std::mutex();
    }

    limits->mutex->lock();

    std::chrono::milliseconds duration(static_cast<int64_t>(time_limit_in_seconds * 1000));
    std::chrono::time_point<std::chrono::high_resolution_clock> from = std::chrono::high_resolution_clock::now();

    limits->time_limit = time_limit_in_seconds;
    limits->time_point = from + duration;
    limits->time_limit_hit = false;

    limits->active = (limits->time_limit > 0 || limits->memory_limit > 0)
                     && !limits->time_limit_hit
                     && !limits->memory_limit_hit;

    if (limits->active && limits->depth && !limits->thread) {
        limits->thread = new std::thread(php_v8_isolate_limits_thread, php_v8_isolate);
    }

    limits->mutex->unlock();

    if (!limits->active && limits->thread) {
        limits->thread->join();
        delete limits->thread;
        limits->thread = NULL;
    }
}

void php_v8_isolate_limits_set_memory_limit(php_v8_isolate_t *php_v8_isolate, size_t memory_limit_in_bytes) {
    PHP_V8_DECLARE_ISOLATE(php_v8_isolate);
    PHP_V8_DECLARE_LIMITS(php_v8_isolate);

    v8::Locker locker(isolate);

    if (!limits->mutex) {
        limits->mutex = new std::mutex();
    }

    limits->mutex->lock();

    limits->memory_limit = memory_limit_in_bytes;
    limits->memory_limit_hit = false;

    limits->active = (limits->time_limit > 0 || limits->memory_limit > 0)
                     && !limits->time_limit_hit
                     && !limits->memory_limit_hit;

    if (limits->active && limits->depth && !limits->thread) {
        limits->thread = new std::thread(php_v8_isolate_limits_thread, php_v8_isolate);
    }

    limits->mutex->unlock();

    if (!limits->active && limits->thread) {
        limits->thread->join();
        delete limits->thread;
        limits->thread = NULL;
    }
}
