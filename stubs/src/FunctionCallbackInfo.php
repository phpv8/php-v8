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


/**
 * The argument information given to function call callbacks.  This
 * class provides access to information about the context of the call,
 * including the receiver, the number and values of arguments, and
 * the holder of the function.
 */
class FunctionCallbackInfo extends CallbackInfo
{
    /**
     * @return Value[] | StringValue[] | SymbolValue[] | NumberValue[] | ObjectValue[] | ArrayObject[] | FunctionObject[] | StringObject[] | SymbolObject[]
     */
    public function Arguments() : array
    {
    }

    /**
     * @return int
     */
    public function Length() : int
    {
    }

    /**
     * @return bool
     */
    public function IsConstructCall() : bool
    {
    }
}
