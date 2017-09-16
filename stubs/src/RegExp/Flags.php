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


namespace V8\RegExpObject;


class Flags
{
    const NONE        = 0;
    const GLOBAL      = 1;
    const IGNORE_CASE = 2;
    const MULTILINE   = 4;
    const STICKY      = 8;
    const UNICODE     = 16;
    const DOTALL      = 32;
}
