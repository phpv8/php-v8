<?php

/*
  +----------------------------------------------------------------------+
  | This file is part of the pinepain/php-v8 PHP extension.              |
  |                                                                      |
  | Copyright (c) 2015-2016 Bogdan Padalko <pinepain@gmail.com>          |
  |                                                                      |
  | Licensed under the MIT license: http://opensource.org/licenses/MIT   |
  |                                                                      |
  | For the full copyright and license information, please view the      |
  | LICENSE file that was distributed with this source or visit          |
  | http://opensource.org/licenses/MIT                                   |
  +----------------------------------------------------------------------+
*/


namespace V8;


class ReturnValue
{
    /**
     * @param \V8\Value $value
     */
    public function Set(Value $value)
    {
    }

    /**
     * Getter
     *
     * If the ReturnValue was not yet set, this will return the undefined value.
     *
     * @return Value
     */
    public function Get() : Value
    {
    }

    public function SetNull()
    {
    }

    public function SetUndefined()
    {
    }

    public function SetEmptyString()
    {
    }

    /**
     * @param bool $value
     */
    public function SetBool(bool $value)
    {
    }

    /**
     * @param int $i
     */
    public function SetInteger(int $i)
    {
    }

    /**
     * @param float $i
     */
    public function SetFloat(float $i)
    {
    }

    /**
     * @return \V8\Isolate
     */
    public function GetIsolate() : Isolate
    {
    }

    /**
     * @return \V8\Context
     */
    public function GetContext() : Context
    {
    }

    /**
     * Check whether object is in current calling context and thus is usable
     *
     * @return bool
     */
    public function InContext() : bool
    {
    }
}
