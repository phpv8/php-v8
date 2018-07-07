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

static inline void php_v8_isolate_limits_update_time_point(php_v8_isolate_limits_t *limits) {
    php_v8_debug_execution("Updating time limits\n");

    std::chrono::milliseconds duration(static_cast<int64_t>(limits->time_limit * 1000));
    std::chrono::time_point<std::chrono::high_resolution_clock> from = std::chrono::high_resolution_clock::now();

    php_v8_debug_execution("             now: %.3f\n", std::chrono::time_point_cast<std::chrono::milliseconds>(from).time_since_epoch().count()/1000.0);
    php_v8_debug_execution("  old time point: %.3f\n", std::chrono::time_point_cast<std::chrono::milliseconds>(limits->time_point).time_since_epoch().count()/1000.0);

    limits->time_point = from + duration;
    php_v8_debug_execution("  new time point: %.3f\n", std::chrono::time_point_cast<std::chrono::milliseconds>(limits->time_point).time_since_epoch().count()/1000.0);
}

static inline void php_v8_isolate_limits_maybe_terminate_thread(php_v8_isolate_limits_t *limits) {
    if (!limits->active && limits->thread) {
        limits->thread->join();
        delete limits->thread;
        limits->thread = NULL;
    }
}

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
                php_v8_debug_execution("Time limit reached, terminating\n");
                php_v8_debug_execution("         now: %.3f\n", std::chrono::time_point_cast<std::chrono::milliseconds>(now).time_since_epoch().count()/1000.0);
                php_v8_debug_execution("  time point: %.3f\n", std::chrono::time_point_cast<std::chrono::milliseconds>(limits->time_point).time_since_epoch().count()/1000.0);

                limits->time_limit_hit = true;
                limits->active = false;
                php_v8_isolate->isolate->TerminateExecution();
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
            php_v8_debug_execution("Exit timer loop: %s, %s\n", has(limits->mutex, "mutex"), has(limits->thread, "thread"));
            php_v8_debug_execution("  active: %s, depth: %d, time limit hit: %d, memory limit hit: %d\n", is(limits->active), limits->depth, limits->time_limit_hit, limits->memory_limit_hit);
            return;
        }

        std::this_thread::sleep_for(duration);
    }
}

void php_v8_isolate_limits_maybe_start_timer(php_v8_isolate_t *php_v8_isolate) {
    php_v8_isolate_limits_t *limits = &php_v8_isolate->limits;

    php_v8_debug_execution("Maybe start timer: %d, %s, %s\n", limits->depth, has(limits->mutex, "mutex"), has(limits->thread, "thread"));

    assert (limits->depth < UINT32_MAX);

    if (!limits->mutex) {
        limits->depth++;
        return;
    }

    std::lock_guard<std::mutex> lock(*limits->mutex);
    limits->depth++;

    if (limits->active && !limits->thread) {
        php_v8_isolate_limits_update_time_point(limits);

        php_v8_debug_execution("  start timer\n");
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

    limits->time_point.~time_point();
}

void php_v8_isolate_limits_ctor(php_v8_isolate_t *php_v8_isolate) {
    PHP_V8_DECLARE_LIMITS(php_v8_isolate);

    limits->thread = NULL;
    limits->mutex = NULL;
    limits->depth = 0;

    new(&limits->time_point) std::chrono::time_point<std::chrono::high_resolution_clock>();
}

void php_v8_isolate_limits_set_time_limit(php_v8_isolate_t *php_v8_isolate, double time_limit_in_seconds) {
    PHP_V8_DECLARE_ISOLATE(php_v8_isolate);
    PHP_V8_DECLARE_LIMITS(php_v8_isolate);

    assert(time_limit_in_seconds >= 0);

    v8::Locker locker(isolate);

    if (!limits->mutex) {
        limits->mutex = new std::mutex();
    }

    limits->mutex->lock();

    php_v8_debug_execution("Setting time limits, new limit: %f, old limit: %f, time_limit_hit: %s\n", time_limit_in_seconds, limits->time_limit, is(limits->time_limit_hit));
    limits->time_limit = time_limit_in_seconds;
    php_v8_isolate_limits_update_time_point(limits);

    if (limits->time_limit_hit) {
        php_v8_debug_execution(" trying to recover from time limit hit, active: %s\n", is(limits->active));

        isolate->CancelTerminateExecution();

        php_v8_isolate_limits_maybe_terminate_thread(limits);
        limits->time_limit_hit = false;
    }

    limits->active = (limits->time_limit > 0 || limits->memory_limit > 0)
                     && !limits->time_limit_hit
                     && !limits->memory_limit_hit;

    if (limits->active && limits->depth && !limits->thread) {
        php_v8_debug_execution("Restart timer: %d, %s, %s\n", limits->depth, has(limits->memory_limit_hit, "memory limit hit"), has(limits->time_limit_hit, "time limit hit"));
        limits->thread = new std::thread(php_v8_isolate_limits_thread, php_v8_isolate);
    }

    limits->mutex->unlock();
    php_v8_isolate_limits_maybe_terminate_thread(limits);
}

void php_v8_isolate_limits_set_memory_limit(php_v8_isolate_t *php_v8_isolate, size_t memory_limit_in_bytes) {
    PHP_V8_DECLARE_ISOLATE(php_v8_isolate);
    PHP_V8_DECLARE_LIMITS(php_v8_isolate);

    v8::Locker locker(isolate);

    if (!limits->mutex) {
        limits->mutex = new std::mutex();
    }

    limits->mutex->lock();

    php_v8_debug_execution("Updating memory limits, memory_limit_hit: %s\n", is(limits->memory_limit_hit));
    limits->memory_limit = memory_limit_in_bytes;

    if (limits->memory_limit_hit) {
        php_v8_debug_execution(" trying to recover from memory limit hit, active: %s\n", is(limits->active));

        isolate->CancelTerminateExecution();

        php_v8_isolate_limits_maybe_terminate_thread(limits);
        limits->memory_limit_hit = false;
    }

    limits->active = (limits->time_limit > 0 || limits->memory_limit > 0)
                     && !limits->time_limit_hit
                     && !limits->memory_limit_hit;

    if (limits->active && limits->depth && !limits->thread) {
        php_v8_debug_execution("Restart timer: %d, %s, %s\n", limits->depth, has(limits->memory_limit_hit, "memory limit hit"), has(limits->time_limit_hit, "time limit hit"));
        limits->thread = new std::thread(php_v8_isolate_limits_thread, php_v8_isolate);
    }

    limits->mutex->unlock();
    php_v8_isolate_limits_maybe_terminate_thread(limits);
}
