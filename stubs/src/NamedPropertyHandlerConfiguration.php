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
     *                             Callback signature is (NameValue $name, PropertyCallbackInfo $args)
     *                             ReturnValue from $args->GetReturnValue() accepts any value.
     *
     * @param callable $setter     The callback to invoke when setting a property.
     *                             Callback signature is (\V8\NameValue $name, \V8\Value $value, PropertyCallbackInfo $args)
     *                             ReturnValue from $args->GetReturnValue() accepts any value
     *
     * @param callable $query      The callback to invoke to check if a property is present, and if present, get its attributes.
     *                             Callback signature is (\V8\NameValue $name, PropertyCallbackInfo $args)
     *                             ReturnValue from $args->GetReturnValue() accepts integer only
     *
     * @param callable $deleter    The callback to invoke when deleting a property.
     *                             Callback signature is (\V8\NameValue $name, PropertyCallbackInfo $args)
     *                             ReturnValue from $args->GetReturnValue() accepts boolean only
     *
     * @param callable $enumerator The callback to invoke to enumerate all the named properties of an object.
     *                             Callback signature is (PropertyCallbackInfo $args).
     *                             ReturnValue from $args->GetReturnValue() accepts ArrayObject only
     *
     * @param int      $flags      One of \V8\PropertyHandlerFlags constants
     */
    public function __construct(
        callable $getter,
        callable $setter = null,
        callable $query = null,
        callable $deleter = null,
        callable $enumerator = null,
        $flags = PropertyHandlerFlags::NONE
    ) {
    }
}
