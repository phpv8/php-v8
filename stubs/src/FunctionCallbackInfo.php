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
class FunctionCallbackInfo
{
    /**
     * @return Isolate
     */
    public function getIsolate(): Isolate
    {
    }

    /**
     * @return Context
     */
    public function getContext(): Context
    {
    }

    /**
     * Returns the receiver. This corresponds to the "this" value.
     *
     * @return ObjectValue
     */
    public function this(): ObjectValue
    {
    }

    /**
     * If the callback was created without a Signature, this is the same
     * value as This(). If there is a signature, and the signature didn't match
     * This() but one of its hidden prototypes, this will be the respective
     * hidden prototype.
     *
     * Note that this is not the prototype of This() on which the accessor
     * referencing this callback was found (which in V8 internally is often
     * referred to as holder [sic]).
     *
     * @return ObjectValue
     */
    public function holder(): ObjectValue
    {
    }

    /**
     * The ReturnValue for the call
     *
     * @return ReturnValue
     */
    public function getReturnValue(): ReturnValue
    {
    }

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
