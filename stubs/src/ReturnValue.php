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


class ReturnValue
{

    /**
     * @return \V8\Isolate
     */
    public function getIsolate(): Isolate
    {
    }

    /**
     * @return \V8\Context
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
     * @param \V8\Value $value
     */
    public function set(Value $value)
    {
    }

    /**
     * Getter
     *
     * If the ReturnValue was not yet set, this will return the undefined value.
     *
     * @return Value|StringValue|NumberValue|ObjectValue|ArrayObject|FunctionObject|DateObject|RegExpObject
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
