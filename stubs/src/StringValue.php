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
 * A JavaScript string value (ECMA-262, 4.3.17).
 */
class StringValue extends NameValue
{
    const kMaxLength = (1 << 28) - 16;

    /**
     * @param Isolate $isolate
     * @param string  $data
     */
    public function __construct(Isolate $isolate, $data = '')
    {
    }

    /**
     * @return string
     */
    public function Value(): string
    {
    }

    /**
     * @return int
     */
    public function Length(): int
    {
    }

    /**
     * @return int
     */
    public function Utf8Length(): int
    {
    }

    /**
     * @return bool
     */
    public function IsOneByte(): bool
    {
    }

    /**
     * @return bool
     */
    public function ContainsOnlyOneByte():bool
    {
    }
}
