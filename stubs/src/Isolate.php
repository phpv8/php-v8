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


use Throwable;
use V8\Exceptions\ValueException;


class Isolate
{
    public function __construct(StartupData $snapshot = null)
    {
    }

    public function SetMemoryLimit(int $memory_limit_in_bytes)
    {
    }

    public function GetMemoryLimit(): int
    {
    }

    public function IsMemoryLimitHit(): bool
    {
    }

    public function SetTimeLimit(float $time_limit_in_seconds)
    {
    }

    public function GetTimeLimit(): float
    {
    }

    public function IsTimeLimitHit(): bool
    {
    }

    /**
     * Get statistics about the heap memory usage.
     *
     * @return HeapStatistics
     */
    public function GetHeapStatistics(): HeapStatistics
    {
    }

    /**
     * Returns true if this isolate has a current context.
     *
     * @return bool
     */
    public function InContext(): bool
    {
    }

    /**
     * Returns the last entered context.
     *
     * @return \V8\Context
     */
    public function GetEnteredContext(): Context
    {
    }

    /**
     * Schedules an exception to be thrown when returning to JavaScript.  When an
     * exception has been scheduled it is illegal to invoke any JavaScript
     * operation; the caller must return immediately and only after the exception
     * has been handled does it become legal to invoke JavaScript operations.
     *
     * @param Context $context
     * @param Value $value
     * @param Throwable|null $e Exception to associate with a given value.
     *                          Because how underlying object wiring done, wiring PHP to V8 exceptions
     *                          is possible only for V8 exception that are instances of ObjectValue.
     *
     * @return void
     *
     * @throws ValueException When trying to associate external exception with non-object value
     * @throws ValueException When another external exception is already associated with a given value
     */
    public function ThrowException(Context $context, Value $value, Throwable $e = null)
    {
    }

    /**
     * Forcefully terminate the current thread of JavaScript execution
     * in the given isolate.
     *
     * This method can be used by any thread even if that thread has not
     * acquired the V8 lock with a Locker object.
     */
    public function TerminateExecution()
    {
    }

    /**
     * Is V8 terminating JavaScript execution.
     *
     * Returns true if JavaScript execution is currently terminating
     * because of a call to TerminateExecution.  In that case there are
     * still JavaScript frames on the stack and the termination
     * exception is still active.
     *
     * @return bool
     */
    public function IsExecutionTerminating(): bool
    {
    }

    /**
     * Resume execution capability in the given isolate, whose execution
     * was previously forcefully terminated using TerminateExecution().
     *
     * When execution is forcefully terminated using TerminateExecution(),
     * the isolate can not resume execution until all JavaScript frames
     * have propagated the uncatchable exception which is generated.  This
     * method allows the program embedding the engine to handle the
     * termination event and resume execution capability, even if
     * JavaScript frames remain on the stack.
     *
     * This method can be used by any thread even if that thread has not
     * acquired the V8 lock with a Locker object.
     */
    public function CancelTerminateExecution()
    {
    }

    /**
     * Optional notification that the embedder is idle.
     * V8 uses the notification to perform garbage collection.
     * This call can be used repeatedly if the embedder remains idle.
     * Returns true if the embedder should stop calling IdleNotificationDeadline
     * until real work has been done.  This indicates that V8 has done
     * as much cleanup as it will be able to do.
     *
     * The deadline_in_seconds argument specifies the deadline V8 has to finish
     * garbage collection work. deadline_in_seconds is compared with
     * MonotonicallyIncreasingTime() and should be based on the same timebase as
     * that function. There is no guarantee that the actual work will be done
     * within the time limit.
     *
     * @param double $deadline_in_seconds
     *
     * @return bool
     */
    public function IdleNotificationDeadline($deadline_in_seconds): bool
    {
    }

    /**
     * Optional notification that the system is running low on memory.
     * V8 uses these notifications to attempt to free memory.
     */
    public function LowMemoryNotification()
    {
    }

    /**
     * Check if V8 is dead and therefore unusable.  This is the case after
     * fatal errors such as out-of-memory situations.
     *
     * @return bool
     */
    public function IsDead(): bool
    {
    }

    /**
     * Check if this isolate is in use.
     * True if at least one thread Enter'ed this isolate.
     *
     * @return bool
     */
    public function IsInUse(): bool
    {
    }

    /**
     * Tells V8 to capture current stack trace when uncaught exception occurs
     * and report it to the message listeners. The option is off by default.
     *
     * @param bool $capture
     * @param int $frame_limit
     */
    public function SetCaptureStackTraceForUncaughtExceptions(bool $capture, int $frame_limit = 10)
    {
    }
}
