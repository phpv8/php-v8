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


class PromiseObject extends ObjectValue
{
    const STATE_PENDING   = 0;
    const STATE_FULFILLED = 1;
    const STATE_REJECTED  = 2;

    /**
     * @param Context $context
     */
    public function __construct(Context $context)
    {
    }

    /**
     * Register a resolution/rejection handler with the promise.
     *
     * The handler is given the respective rejection value as
     * an argument. If the promise is already rejected, the handler is
     * invoked at the end of turn.
     *
     * @param Context        $context
     * @param FunctionObject $handler
     *
     * @return PromiseObject
     */
    public function catch(Context $context, FunctionObject $handler): PromiseObject
    {
    }

    /**
     * Register a resolution/rejection handler with the promise.
     *
     * The handler is given the respective resolution value as
     * an argument. If the promise is already resolved, the handler is
     * invoked at the end of turn.
     *
     * @param Context        $context
     * @param FunctionObject $handler
     *
     * @return PromiseObject
     */
    public function then(Context $context, FunctionObject $handler): PromiseObject
    {
    }

    /**
     * Returns true if the promise has at least one derived promise, and
     * therefore resolve/reject handlers (including default handler).
     *
     * @return bool
     */
    public function hasHandler(): bool
    {
    }

    /**
     * Returns the content of the promise result (resolve or reject value). The Promise must not be pending.
     *
     * @return Value|PrimitiveValue|ObjectValue
     */
    public function result(): Value
    {
    }

    /**
     * Returns the promise state value.
     *
     * @return int
     */
    public function state(): int
    {
    }
}
