<?php declare(strict_types=1);

/**
 * This file is part of the pinepain/php-v8 PHP extension.
 *
 * Copyright (c) 2015-2018 Bogdan Padalko <pinepain@gmail.com>
 *
 * Licensed under the MIT license: http://opensource.org/licenses/MIT
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source or visit
 * http://opensource.org/licenses/MIT
 */


namespace V8;

/**
 * A JSON Parser and Stringifier.
 */
class JSON extends IntegerValue
{
    /**
     * Tries to parse the string |json_string| and returns it as value if
     * successful.
     *
     * @param Context $context
     * @param String  $json_string The string to parse.
     *
     * @return Value|PrimitiveValue|ObjectValue
     */
    public static function parse(Context $context, String $json_string): Value
    {
    }

    /**
     * Tries to stringify the JSON-serializable object |json_object| and returns
     * it as string if successful.
     *
     * @param Context     $context
     * @param Value       $json_value The JSON-serializable value to stringify.
     * @param String|null $gap
     *
     * @return string
     */
    public static function stringify(Context $context, Value $json_value, String $gap = null): string
    {
    }
}
