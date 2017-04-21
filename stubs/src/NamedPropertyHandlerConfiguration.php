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


class NamedPropertyHandlerConfiguration
{
    /**
     * @param callable $getter     The callback to invoke when getting a property.
     * @param callable $setter     The callback to invoke when setting a property.
     * @param callable $query      The callback to invoke to check if a property is present, and if present, get its attributes.
     * @param callable $deleter    The callback to invoke when deleting a property.
     * @param callable $enumerator The callback to invoke to enumerate all the named properties of an object.
     * @param int      $flags      One of \v8\PropertyHandlerFlags constants
     */
    public function __construct(
        callable $getter,
        callable $setter = null,
        callable $query = null,
        callable $deleter = null,
        callable $enumerator = null,
        $flags = PropertyHandlerFlags::kNone
    ) {
    }
}
