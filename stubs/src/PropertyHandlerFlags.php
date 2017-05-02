<?php

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
 * Configuration flags for V8\NamedPropertyHandlerConfiguration or
 * V8\IndexedPropertyHandlerConfiguration.
 */
class PropertyHandlerFlags
{
    /**
     * None.
     */
    const kNone = 0;
    /**
     * See \V8\AccessControl::ALL_CAN_READ (all cross-context reads are allowed).
     */
    const kAllCanRead = 1;
    /** Will not call into interceptor for properties on the receiver or prototype
     * chain, i.e., only call into interceptor for properties that do not exist.
     * Currently only valid for named interceptors.
     */
    const kNonMasking = 2;
    /**
     * Will not call into interceptor for symbol lookup.  Only meaningful for
     * named interceptors.
     */
    const kOnlyInterceptStrings = 4;
}
