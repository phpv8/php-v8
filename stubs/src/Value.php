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
 * The superclass of all JavaScript values and objects.
 */
abstract class Value extends Data
{
    /**
     * @return \V8\Isolate
     */
    public function getIsolate(): Isolate
    {
    }

    /**
     * Returns true if this value is the undefined value.  See ECMA-262 4.3.10.
     *
     * @return bool
     */
    public function isUndefined(): bool
    {
    }

    /**
     * Returns true if this value is the null value.  See ECMA-262 4.3.11.
     *
     * @return bool
     */
    public function isNull(): bool
    {
    }

    /**
     * Returns true if this value is true.
     *
     * @return bool
     */
    public function isTrue(): bool
    {
    }

    /**
     * Returns true if this value is false.
     *
     * @return bool
     */
    public function isFalse(): bool
    {
    }

    /**
     * Returns true if this value is a symbol or a string.
     * This is an experimental feature.
     *
     * @return bool
     */
    public function isName(): bool
    {
    }

    /**
     * Returns true if this value is an instance of the String type. See ECMA-262 8.4.
     *
     * @return bool
     */
    public function isString(): bool
    {
    }

    /**
     * Returns true if this value is a symbol.
     *
     * @return bool
     */
    public function isSymbol(): bool
    {
    }

    /**
     * Returns true if this value is a function.
     *
     * @return bool
     */
    public function isFunction(): bool
    {
    }

    /**
     * Returns true if this value is an array.
     *
     * @return bool
     */
    public function isArray(): bool
    {
    }

    /**
     * Returns true if this value is an object.
     *
     * @return bool
     */
    public function isObject(): bool
    {
    }

    /**
     * Returns true if this value is boolean.
     *
     * @return bool
     */
    public function isBoolean(): bool
    {
    }

    /**
     * Returns true if this value is a number.
     *
     * @return bool
     */
    public function isNumber(): bool
    {
    }

    /**
     * Returns true if this value is a 32-bit signed integer.
     *
     * @return bool
     */
    public function isInt32(): bool
    {
    }

    /**
     * Returns true if this value is a 32-bit unsigned integer.
     *
     * @return bool
     */
    public function isUint32(): bool
    {
    }

    /**
     * Returns true if this value is a Date.
     *
     * @return bool
     */
    public function isDate(): bool
    {
    }

    /**
     * Returns true if this value is an Arguments object.
     *
     * @return bool
     */
    public function isArgumentsObject(): bool
    {
    }

    /**
     * Returns true if this value is a Boolean object.
     *
     * @return bool
     */
    public function isBooleanObject(): bool
    {
    }

    /**
     * Returns true if this value is a Number object.
     *
     * @return bool
     */
    public function isNumberObject(): bool
    {
    }

    /**
     * Returns true if this value is a String object.
     *
     * @return bool
     */
    public function isStringObject(): bool
    {
    }

    /**
     * Returns true if this value is a Symbol object.
     */
    public function isSymbolObject(): bool
    {
    }

    /**
     * Returns true if this value is a NativeError.
     *
     * @return bool
     */
    public function isNativeError(): bool
    {
    }

    /**
     * Returns true if this value is a RegExp.
     *
     * @return bool
     */
    public function isRegExp(): bool
    {
    }


    /**
     * @param Context $context
     *
     * @return \V8\BooleanValue
     */
    public function toBoolean(Context $context): BooleanValue
    {
    }

    /**
     * @param Context $context
     *
     * @return \V8\NumberValue
     */
    public function toNumber(Context $context): NumberValue
    {
    }

    /**
     * @param Context $context
     *
     * @return \V8\StringValue
     */
    public function toString(Context $context): StringValue
    {
    }

    /**
     * @param Context $context
     *
     * @return \V8\StringValue
     */
    public function toDetailString(Context $context): StringValue
    {
    }

    /**
     *
     * @param Context $context
     *
     * @return \V8\ObjectValue
     */
    public function toObject(Context $context): ObjectValue
    {
    }

    /**
     *
     * @param Context $context
     *
     * @return \V8\IntegerValue
     */
    public function toInteger(Context $context): IntegerValue
    {
    }

    /**
     *
     * @param Context $context
     *
     * @return \V8\Uint32Value
     */
    public function toUint32(Context $context): Uint32Value
    {
    }

    /**
     * @param Context $context
     *
     * @return \V8\Int32Value
     */
    public function toInt32(Context $context): Int32Value
    {
    }

    /**
     * Attempts to convert a string to an array index.
     * Returns an empty handle if the conversion fails.
     *
     * @param Context $context
     *
     * @return \V8\Uint32Value
     */
    public function toArrayIndex(Context $context)
    {
    }

    /**
     * @param Context $context
     *
     * @return bool
     */
    public function booleanValue(Context $context): bool
    {
    }

    /**
     * @param Context $context
     *
     * @return double
     */
    public function numberValue(Context $context): double
    {
    }

    /**
     * @param Context $context
     *
     * @return int|double
     */
    public function integerValue(Context $context): double
    {
    }

    /**
     * @param Context $context
     *
     * @return int
     */
    public function uint32Value(Context $context): int
    {
    }

    /**
     * @param Context $context
     *
     * @return int
     */
    public function int32Value(Context $context): int
    {
    }


    /** JS == */

    /**
     * @param Context $context
     *
     * @param Value   $that
     *
     * @return bool
     */
    public function equals(Context $context, Value $that): bool
    {
    }

    /**
     * @param Value $that
     *
     * @return bool
     */
    public function strictEquals(Value $that): bool
    {
    }

    /**
     * @param Value $that
     *
     * @return bool
     */
    public function sameValue(Value $that): bool
    {
    }

    /**
     * @return StringValue
     */
    public function typeOf(): StringValue
    {
    }

    /**
     * @param Context     $context
     * @param ObjectValue $object
     *
     * @return bool
     */
    public function instanceOf (Context $context, ObjectValue $object): bool
    {
    }
}
