<?php declare(strict_types=1);

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
 * An instance of the built-in array constructor (ECMA-262, 15.4.2).
 */
class ArrayObject extends ObjectValue
{

    /**
     * Creates a JavaScript array with the given length. If the length
     * is negative the returned array will have length 0.
     *
     * @param Context $context
     * @param int     $length Should be valid int(int32)
     */
    public function __construct(Context $context, int $length = 0)
    {
    }

    /**
     * @return int
     */
    public function length(): int
    {
    }
}
