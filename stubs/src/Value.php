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
class Value extends Data
{
    /**
     * @var \V8\Isolate
     */
    private $isolate;

    /**
     * @param Isolate $isolate
     */
    public function __construct(\V8\Isolate $isolate)
    {
        $this->isolate = $isolate;
    }

    /**
     * @return \V8\Isolate
     */
    public function GetIsolate()
    {
        return $this->isolate;
    }

    /**
     * Returns true if this value is the undefined value.  See ECMA-262 4.3.10.
     *
     * @return bool
     */
    public function IsUndefined(): bool
    {
    }

    /**
     * Returns true if this value is the null value.  See ECMA-262 4.3.11.
     *
     * @return bool
     */
    public function IsNull(): bool
    {
    }

    /**
     * Returns true if this value is true.
     *
     * @return bool
     */
    public function IsTrue(): bool
    {
    }

    /**
     * Returns true if this value is false.
     *
     * @return bool
     */
    public function IsFalse(): bool
    {
    }

    /**
     * Returns true if this value is a symbol or a string.
     * This is an experimental feature.
     *
     * @return bool
     */
    public function IsName(): bool
    {
    }

    /**
     * Returns true if this value is an instance of the String type. See ECMA-262 8.4.
     *
     * @return bool
     */
    public function IsString(): bool
    {
    }

    /**
     * Returns true if this value is a symbol.
     *
     * @return bool
     */
    public function IsSymbol(): bool
    {
    }

    /**
     * Returns true if this value is a function.
     *
     * @return bool
     */
    public function IsFunction(): bool
    {
    }

    /**
     * Returns true if this value is an array.
     *
     * @return bool
     */
    public function IsArray(): bool
    {
    }

    /**
     * Returns true if this value is an object.
     *
     * @return bool
     */
    public function IsObject(): bool
    {
    }

    /**
     * Returns true if this value is boolean.
     *
     * @return bool
     */
    public function IsBoolean(): bool
    {
    }

    /**
     * Returns true if this value is a number.
     *
     * @return bool
     */
    public function IsNumber(): bool
    {
    }

    /**
     * Returns true if this value is a 32-bit signed integer.
     *
     * @return bool
     */
    public function IsInt32(): bool
    {
    }

    /**
     * Returns true if this value is a 32-bit unsigned integer.
     *
     * @return bool
     */
    public function IsUint32(): bool
    {
    }

    /**
     * Returns true if this value is a Date.
     *
     * @return bool
     */
    public function IsDate(): bool
    {
    }

    /**
     * Returns true if this value is an Arguments object.
     *
     * @return bool
     */
    public function IsArgumentsObject(): bool
    {
    }

    /**
     * Returns true if this value is a Boolean object.
     *
     * @return bool
     */
    public function IsBooleanObject(): bool
    {
    }

    /**
     * Returns true if this value is a Number object.
     *
     * @return bool
     */
    public function IsNumberObject(): bool
    {
    }

    /**
     * Returns true if this value is a String object.
     *
     * @return bool
     */
    public function IsStringObject(): bool
    {
    }

    /**
     * Returns true if this value is a Symbol object.
     */
    public function IsSymbolObject(): bool
    {
    }

    /**
     * Returns true if this value is a NativeError.
     *
     * @return bool
     */
    public function IsNativeError(): bool
    {
    }

    /**
     * Returns true if this value is a RegExp.
     *
     * @return bool
     */
    public function IsRegExp(): bool
    {
    }


    /**
     * @param Context $context
     *
     * @return \V8\BooleanValue
     */
    public function ToBoolean(Context $context)
    {
    }

    /**
     * @param Context $context
     *
     * @return \V8\NumberValue
     */
    public function ToNumber(Context $context)
    {
    }

    /**
     * @param Context $context
     *
     * @return \V8\StringValue
     */
    public function ToString(Context $context)
    {
    }

    /**
     * @param Context $context
     *
     * @return \V8\StringValue
     */
    public function ToDetailString(Context $context)
    {
    }

    /**
     *
     * @param Context $context
     *
     * @return \V8\ObjectValue
     */
    public function ToObject(Context $context)
    {
    }

    /**
     *
     * @param Context $context
     *
     * @return \V8\IntegerValue
     */
    public function ToInteger(Context $context)
    {
    }

    /**
     *
     * @param Context $context
     *
     * @return \V8\Uint32Value
     */
    public function ToUint32(Context $context)
    {
    }

    /**
     * @param Context $context
     *
     * @return \V8\Int32Value
     */
    public function ToInt32(Context $context)
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
    public function ToArrayIndex(Context $context)
    {
    }

    /**
     * @param Context $context
     *
     * @return bool
     */
    public function BooleanValue(Context $context)
    {
    }

    /**
     * @param Context $context
     *
     * @return double
     */
    public function NumberValue(Context $context)
    {
    }

    /**
     * @param Context $context
     *
     * @return int | double
     */
    public function IntegerValue(Context $context)
    {
    }

    /**
     * @param Context $context
     *
     * @return int
     */
    public function Uint32Value(Context $context)
    {
    }

    /**
     * @param Context $context
     *
     * @return int
     */
    public function Int32Value(Context $context)
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
    public function Equals(Context $context, Value $that): bool
    {
    }

    /**
     * @param Value $that
     *
     * @return bool
     */
    public function StrictEquals(Value $that): bool
    {
    }

    /**
     * @param Value $that
     *
     * @return bool
     */
    public function SameValue(Value $that): bool
    {
    }

    /**
     * @return StringValue
     */
    public function TypeOf(): StringValue
    {
    }

    /**
     * @param Context     $context
     * @param ObjectValue $object
     *
     * @return bool
     */
    public function InstanceOf (Context $context, ObjectValue $object): bool
    {
    }
}
