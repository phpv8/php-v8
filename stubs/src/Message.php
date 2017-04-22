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
 * An error message.
 */
class Message
{
    const kNoLineNumberInfo = 0;
    const kNoColumnInfo = 0;
    const kNoScriptIdInfo = 0;

    /**
     * @var ScriptOrigin
     */
    private $script_origin;
    /**
     * @var string
     */
    private $message;
    /**
     * @var string
     */
    private $source_line;
    /**
     * @var string
     */
    private $resource_name;
    /**
     * @var StackTrace
     */
    private $stack_trace;
    /**
     * @var int
     */
    private $line_number;
    /**
     * @var int
     */
    private $start_position;
    /**
     * @var int
     */
    private $end_position;
    /**
     * @var int
     */
    private $start_column;
    /**
     * @var int
     */
    private $end_column;
    /**
     * @var bool
     */
    private $is_shared_cross_origin;
    /**
     * @var bool
     */
    private $is_opaque;

    /**
     * @param string       $message
     * @param string       $source_line
     * @param ScriptOrigin $script_origin
     * @param string       $resource_name
     * @param StackTrace   $stack_trace
     * @param int          $line_number
     * @param int          $start_position
     * @param int          $end_position
     * @param int          $start_column
     * @param int          $end_column
     * @param bool         $is_shared_cross_origin
     * @param bool         $is_opaque
     */
    public function __construct(
        string $message,
        string $source_line,
        ScriptOrigin $script_origin,
        string $resource_name,
        StackTrace $stack_trace,
        int $line_number = self::kNoLineNumberInfo,
        int $start_position = -1,
        int $end_position = -1,
        int $start_column = self::kNoColumnInfo,
        int $end_column = self::kNoColumnInfo,
        bool $is_shared_cross_origin = false,
        bool $is_opaque = false
    ) {
        $this->message                = $message;
        $this->source_line            = $source_line;
        $this->script_origin          = $script_origin;
        $this->resource_name          = $resource_name;
        $this->stack_trace            = $stack_trace;
        $this->line_number            = $line_number;
        $this->start_position         = $start_position;
        $this->end_position           = $end_position;
        $this->start_column           = $start_column;
        $this->end_column             = $end_column;
        $this->is_shared_cross_origin = $is_shared_cross_origin;
        $this->is_opaque              = $is_opaque;
    }


    /**
     * @return string
     */
    public function Get(): string
    {
    }

    /**
     * TODO: should be return simple string?
     *
     * @return string
     */
    public function GetSourceLine(): string
    {
    }

    /**
     * Returns the origin for the script from where the function causing the
     * error originates.
     *
     * @return ScriptOrigin
     */
    public function GetScriptOrigin(): ScriptOrigin
    {
    }

    /**
     * Returns the resource name for the script from where the function causing
     * the error originates.
     *
     * @return string
     */
    public function GetScriptResourceName(): string
    {
    }

    /**
     * Exception stack trace. By default stack traces are not captured for
     * uncaught exceptions. SetCaptureStackTraceForUncaughtExceptions allows
     * to change this option.
     *
     * @return StackTrace
     */
    public function GetStackTrace(): StackTrace
    {
    }

    /**
     * Returns the number, 1-based, of the line where the error occurred.
     *
     * @return int
     */
    public function GetLineNumber(): int
    {
    }

    /**
     * Returns the index within the script of the first character where
     * the error occurred.
     *
     * @return int
     */
    public function GetStartPosition(): int
    {
    }

    /**
     * Returns the index within the script of the last character where
     * the error occurred.
     *
     * @return int
     */
    public function GetEndPosition(): int
    {
    }

    /**
     * Returns the index within the line of the first character where
     * the error occurred.
     *
     * @return int
     */
    public function GetStartColumn(): int
    {
    }

    /**
     * Returns the index within the line of the last character where
     * the error occurred.
     *
     * @return int
     */
    public function GetEndColumn(): int
    {
    }

    /**
     * Passes on the value set by the embedder when it fed the script from which
     * this Message was generated to V8.
     *
     * @return bool
     */
    public function IsSharedCrossOrigin(): bool
    {
    }

    /**
     * @return bool
     */
    public function IsOpaque(): bool
    {
    }
}
