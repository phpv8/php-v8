<?php declare(strict_types=1);

/**
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


namespace V8;


/**
 * Option flags passed to the SetRAILMode function.
 * See documentation https://developers.google.com/web/tools/chrome-devtools/profile/evaluate-performance/rail
 */
final class RAILMode
{
    /**
     * Response performance mode: In this mode very low virtual machine latency is provided.
     * V8 will try to avoid JavaScript execution interruptions. Throughput may be throttled.
     */
    const PERFORMANCE_RESPONSE = 0;
    /**
     * Animation performance mode: In this mode low virtual machine latency is provided.
     * V8 will try to avoid as many JavaScript execution interruptions
     * as possible. Throughput may be throttled. This is the default mode.
     */
    const PERFORMANCE_ANIMATION = 1;
    /**
     * Idle performance mode: The embedder is idle. V8 can complete deferred work in this mode.
     */
    const PERFORMANCE_IDLE = 2;
    /**
     * Load performance mode: In this mode high throughput is provided. V8 may turn off latency optimizations.
     */
    const PERFORMANCE_LOAD = 3;
}
