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
 * A primitive undefined value (ECMA-262, 4.3.10). Sole value is the undefined value.
 */
class UndefinedValue extends PrimitiveValue
{
    public function __construct(Isolate $isolate)
    {
    }
}
