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
 * A superclass for symbols and strings.
 */
class NameValue extends PrimitiveValue
{
    /**
     * Returns the identity hash for this object. The current implementation
     * uses an inline property on the object to store the identity hash.
     *
     * The return value will never be 0. Also, it is not guaranteed to be
     * unique.
     */
    public function GetIdentityHash() : int
    {
    }
}
