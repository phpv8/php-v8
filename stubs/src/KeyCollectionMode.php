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
 * Keys/Properties filter to limits the range of collected properties
 */
class KeyCollectionMode
{
    const kOwnOnly            = 0; // limits the collected properties to the given Object only. kIncludesPrototypes
    const kIncludesPrototypes = 1; // will include all keys of the objects's prototype chain as well.
}
