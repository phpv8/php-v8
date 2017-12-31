<?php declare(strict_types=1);

/**
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


namespace V8;


/**
 * Configuration flags for V8\NamedPropertyHandlerConfiguration or
 * V8\IndexedPropertyHandlerConfiguration.
 */
final class PropertyHandlerFlags
{
    /**
     * None.
     */
    const NONE = 0;
    /**
     * See \V8\AccessControl::ALL_CAN_READ (all cross-context reads are allowed).
     */
    const ALL_CAN_READ = 1;
    /** Will not call into interceptor for properties on the receiver or prototype
     * chain, i.e., only call into interceptor for properties that do not exist.
     * Currently only valid for named interceptors.
     */
    const NON_MASKING = 2;
    /**
     * Will not call into interceptor for symbol lookup.  Only meaningful for
     * named interceptors.
     */
    const ONLY_INTERCEPT_STRINGS = 4;
}
