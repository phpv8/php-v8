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


class ReturnValue
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
     * Check whether return value is in current calling context and thus is usable
     *
     * @return bool
     */
    public function inContext(): bool
    {
    }

    /**
     * @param Value $value
     */
    public function set(Value $value)
    {
    }

    /**
     * Getter
     *
     * If the ReturnValue was not yet set, this will return the undefined value.
     *
     * @return Value|PrimitiveValue|ObjectValue
     */
    public function get(): Value
    {
    }

    /**
     * Quick setter to set return value to null
     */
    public function setNull()
    {
    }

    /**
     * Quick setter to set return value to undefined
     */
    public function setUndefined()
    {
    }

    /**
     * Quick setter to set return value to an empty string
     */
    public function setEmptyString()
    {
    }

    /**
     * Quick setter to set return value to the provided boolean value
     *
     * @param bool $value
     */
    public function setBool(bool $value)
    {
    }

    /**
     * Quick setter to set return value to the provided integer value
     *
     * @param int $i
     */
    public function setInteger(int $i)
    {
    }

    /**
     * Quick setter to set return value to the provided float value
     *
     * @param float $i
     */
    public function setFloat(float $i)
    {
    }
}
