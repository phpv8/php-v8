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
 * The argument information given to function call callbacks.  This
 * class provides access to information about the context of the call,
 * including the receiver, the number and values of arguments, and
 * the holder of the function.
 */
class FunctionCallbackInfo extends CallbackInfo
{
    /**
     * @return int
     */
    public function length(): int
    {
    }

    /**
     * Get available arguments
     *
     * @return Value[]
     */
    public function arguments(): array
    {
    }

    /**
     * For construct calls, this returns the "new.target" value.
     *
     * @return Value|PrimitiveValue|ObjectValue
     */
    public function newTarget(): Value
    {
    }

    /**
     * Indicates whether this is a regular call or a construct call.
     *
     * @return bool
     */
    public function isConstructCall(): bool
    {
    }
}
