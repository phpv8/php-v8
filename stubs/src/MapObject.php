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
 * An instance of the built-in Map constructor (ECMA-262, 6th Edition, 23.1.1).
 */
class MapObject extends ObjectValue
{
    public function size(): float
    {
    }

    public function clear()
    {
    }

    /**
     * @param Context $context
     * @param Value   $key
     *
     * @return Value|PrimitiveValue|ObjectValue
     */
    public function get(Context $context, Value $key): Value
    {
    }

    /**
     * @param Context $context
     * @param Value   $key
     * @param Value   $value
     *
     * @return MapObject
     */
    public function set(Context $context, Value $key, Value $value): MapObject
    {
    }

    /**
     * @param Context $context
     * @param Value   $key
     *
     * @return bool
     */
    public function has(Context $context, Value $key): bool
    {
    }

    /**
     * @param Context $context
     * @param Value   $key
     *
     * @return bool
     */
    public function delete(Context $context, Value $key): bool
    {
    }

    /**
     * Returns an array of length Size() * 2, where index N is the Nth key and
     * index N + 1 is the Nth value.
     *
     * @return ArrayObject
     */
    public function asArray(): ArrayObject
    {
    }

}
