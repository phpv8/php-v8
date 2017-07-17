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
 * Create new error objects by calling the corresponding error object
 * constructor with the message.
 */
class Exception
{
    /**
     * @param Context         $context
     * @param \V8\StringValue $message
     *
     * @return \V8\ObjectValue
     */
    public static function RangeError(Context $context, StringValue $message): ObjectValue
    {
    }

    /**
     * @param Context         $context
     * @param \V8\StringValue $message
     *
     * @return \V8\ObjectValue
     */
    public static function ReferenceError(Context $context, StringValue $message): ObjectValue
    {
    }

    /**
     * @param Context         $context
     * @param \V8\StringValue $message
     *
     * @return \V8\ObjectValue
     */
    public static function SyntaxError(Context $context, StringValue $message): ObjectValue
    {
    }

    /**
     * @param Context         $context
     * @param \V8\StringValue $message
     *
     * @return \V8\ObjectValue
     */
    public static function TypeError(Context $context, StringValue $message): ObjectValue
    {
    }

    /**
     * @param Context         $context
     * @param \V8\StringValue $message
     *
     * @return ObjectValue
     */
    public static function Error(Context $context, StringValue $message): ObjectValue
    {
    }

    /**
     * Creates an error message for the given exception.
     * Will try to reconstruct the original stack trace from the exception value,
     * or capture the current stack trace if not available.
     *
     * @param Context $context
     * @param Value   $exception
     *
     * @return Message
     */
    static function CreateMessage(Context $context, Value $exception): Message
    {
    }

    /**
     * Returns the original stack trace that was captured at the creation time
     * of a given exception, or an empty handle if not available.
     *
     * @param Context $context
     * @param Value   $exception
     *
     * @return null|StackTrace
     */
    public static function GetStackTrace(Context $context, Value $exception)
    {
    }
}
