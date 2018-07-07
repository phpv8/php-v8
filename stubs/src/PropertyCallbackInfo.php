<?php declare(strict_types=1);

/**
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


namespace V8;


/**
 * The information passed to a property callback about the context
 * of the property access.
 */
class PropertyCallbackInfo implements CallbackInfoInterface
{
    /**
     * {@inheritdoc}
     */
    public function getIsolate(): Isolate
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getContext(): Context
    {
    }

    /**
     * {@inheritdoc}
     */
    public function this(): ObjectValue
    {
    }

    /**
     * {@inheritdoc}
     */
    public function holder(): ObjectValue
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getReturnValue(): ReturnValue
    {
    }

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
