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


namespace v8;

/**
 * A JavaScript value representing a signed integer.
 */
class IntegerValue extends NumberValue
{
    /**
     * @param Isolate $isolate
     * @param int     $value Should be valid int32 or uint32 value
     */
    public function __construct(Isolate $isolate, int $value)
    {
        parent::__construct($isolate);
    }

    /**
     * @return int int64 value
     */
    public function Value() : int
    {
    }
}
