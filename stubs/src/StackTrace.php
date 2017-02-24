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


use V8\Exceptions\GenericException;
use V8\StackTrace\StackTraceOptions;


/**
 * Representation of a JavaScript stack trace. The information collected is a
 * snapshot of the execution stack and the information remains valid after
 * execution continues.
 */
class StackTrace
{
    const MIN_FRAME_LIMIT = 0;
    const MAX_FRAME_LIMIT = 1000;

    /**
     * @var array|StackFrame[]
     */
    private $frames;
    /**
     * @var ArrayObject
     */
    private $as_array;

    /**
     * @param StackFrame[] $frames
     * @param ArrayObject  $as_array
     */
    public function __construct(array $frames, ArrayObject $as_array)
    {
        $this->frames = $frames;
        $this->as_array = $as_array;
    }


    /**
     * Returns a StackFrame at a particular index.
     *
     * @return StackFrame[]
     */
    public function GetFrames() : array
    {
        return $this->frames;
    }

    /**
     * Returns a StackFrame at a particular index.
     *
     * @param int $index
     *
     * @return StackFrame
     *
     * @throws GenericException When index is out of range
     */
    public function GetFrame(int $index) : StackFrame
    {
        if ($index < 0 || !isset($this->frames[$index])) {
            throw new GenericException('Frame index is out of range');
        }

        return $this->frames[$index];
    }

    /**
     * Returns the number of StackFrames.
     *
     * @return int
     */
    public function GetFrameCount() : int
    {
        return count($this->frames);
    }

    /**
     * Returns StackTrace as a v8::Array that contains StackFrame objects.
     *
     * @return ArrayObject | null
     */
    public function AsArray() : ArrayObject
    {
        return $this->as_array;
    }

    /**
     * Grab a snapshot of the current JavaScript execution stack.
     *
     * \param frame_limit The maximum number of stack frames we want to capture.
     * \param options Enumerates the set of things we will capture for each
     *   StackFrame.
     *
     * @param Isolate $isolate
     * @param int     $frame_limit
     * @param int     $options One or more \v8\StackTrace\StackTraceOptions const flags
     *
     * TODO: try to minimize effect of invalid args
     * Note, that having large (or negative) $frame_limit number may cause OutOfMemory error.
     * To minimize any potentially erroneous usage, allowed range for $frame_limit is [0, 1000] (boundaries included).
     *
     * @return StackTrace
     */
    public static function CurrentStackTrace(
        Isolate $isolate,
        int $frame_limit,
        int $options = StackTraceOptions::kOverview
    ) : StackTrace
    {
    }
}
