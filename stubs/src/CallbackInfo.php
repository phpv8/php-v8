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

class CallbackInfo
{
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
     * @return \V8\ObjectValue
     */
    public function This() : ObjectValue
    {
    }

    /**
     * @return \V8\ObjectValue
     */
    public function Holder() : ObjectValue
    {
    }

    /**
     * @return \V8\ReturnValue
     */
    public function GetReturnValue() : ReturnValue
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
