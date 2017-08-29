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
 * The information passed to a property callback about the context
 * of the property access.
 */
class PropertyCallbackInfo extends CallbackInfo
{
    /**
     * Returns true if the intercepted function should throw if an error occurs.
     * Usually, true corresponds to 'use strict'.
     *
     * Always false when intercepting Reflect.set() independent of the language mode.
     *
     * @return bool
     */
    public function shouldThrowOnError(): bool
    {
    }
}
