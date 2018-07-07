<?php declare(strict_types=1);

/**
 * This file is part of the phpv8/php-v8 PHP extension.
 *
 * Copyright (c) 2015-2018 Bogdan Padalko <thepinepain@gmail.com>
 *
 * Licensed under the MIT license: http://opensource.org/licenses/MIT
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source or visit
 * http://opensource.org/licenses/MIT
 */


namespace V8;

/**
 * Property filter bits. They can be or'ed to build a composite filter.
 */
final class PropertyFilter
{
    const ALL_PROPERTIES    = 0;
    const ONLY_WRITABLE     = 1;
    const ONLY_ENUMERABLE   = 2;
    const ONLY_CONFIGURABLE = 4;
    const SKIP_STRINGS      = 8;
    const SKIP_SYMBOLS      = 16;
}
