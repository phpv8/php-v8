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


use V8\Exceptions\Exception;


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
     * @param StackFrame[] $frames
     */
    public function __construct(array $frames)
    {
        $this->frames = $frames;
    }


    /**
     * Returns a StackFrame at a particular index.
     *
     * @return StackFrame[]
     */
    public function getFrames(): array
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
     * @throws Exception When index is out of range
     */
    public function getFrame(int $index): StackFrame
    {
        if ($index < 0 || !isset($this->frames[$index])) {
            throw new Exception('Frame index is out of range');
        }

        return $this->frames[$index];
    }

    /**
     * Returns the number of StackFrames.
     *
     * @return int
     */
    public function getFrameCount(): int
    {
        return count($this->frames);
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
     *
     * TODO: try to minimize effect of invalid args
     * Note, that having large (or negative) $frame_limit number may cause OutOfMemory error.
     * To minimize any potentially erroneous usage, allowed range for $frame_limit is [0, 1000] (boundaries included).
     *
     * @return StackTrace
     */
    public static function currentStackTrace(Isolate $isolate, int $frame_limit): StackTrace
    {
    }
}
