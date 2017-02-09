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

/**
 * An instance of the built-in Set constructor (ECMA-262, 6th Edition, 23.2.1).
 */
class SetObject extends ObjectValue
{
    public function Size(): float
    {
    }

    public function Clear()
    {
    }

    public function Add(Context $context, Value $key): SetObject
    {
    }

    public function Has(Context $context, Value $key): bool
    {
    }

    public function Delete(Context $context, Value $key): bool
    {
    }

    /**
     * Returns an array of the keys in this Set.
     */
    public function AsArray(): ArrayObject
    {
    }
}

