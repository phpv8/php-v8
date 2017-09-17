<?php declare(strict_types=1);

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


/**
 * An external exception handler.
 */
class TryCatch
{
    /**
     * @var Isolate
     */
    private $isolate;
    /**
     * @var Context
     */
    private $context;
    /**
     * @var Value
     */
    private $exception;
    /**
     * @var Value
     */
    private $stack_trace;
    /**
     * @var Message
     */
    private $message;
    /**
     * @var bool
     */
    private $can_continue;
    /**
     * @var bool
     */
    private $has_terminated;
    /**
     * @var null|Throwable
     */
    private $external_exception;

    /**
     * Creates a new try/catch block and registers it with v8.  Note that
     * all TryCatch blocks should be stack allocated because the memory
     * location itself is compared against JavaScript try/catch blocks.
     *
     * @param Isolate        $isolate
     * @param Context        $context
     * @param Value          $exception
     * @param Value          $stack_trace
     * @param Message        $message
     * @param bool           $can_continue
     * @param bool           $has_terminated
     * @param Throwable|null $external_exception
     */
    public function __construct(
        Isolate $isolate,
        Context $context,
        Value $exception = null,
        Value $stack_trace = null,
        Message $message = null,
        bool $can_continue = false,
        bool $has_terminated = false,
        Throwable $external_exception = null
    ) {
    }

    /**
     * @return Isolate
     */
    public function getIsolate(): Isolate
    {
    }

    /**
     * @return Context
     */
    public function getContext(): Context
    {
    }

    /**
     * Returns the exception caught by this try/catch block.  If no exception has
     * been caught an empty handle is returned.
     *
     * The returned handle is valid until this TryCatch block has been destroyed.
     *
     * @return Value|null
     *
     */
    public function getException(): ?Value
    {
    }

    /**
     * Returns the .stack property of the thrown object.  If no .stack
     * property is present an empty handle is returned.
     *
     * @return Value|null
     */
    public function getStackTrace(): ?Value
    {
    }

    /**
     * Returns the message associated with this exception.  If there is
     * no message associated an empty handle is returned.
     *
     * The returned handle is valid until this TryCatch block has been
     * destroyed.
     *
     * @return Message|null
     */
    public function getMessage(): ?Message
    {
    }

    /**
     * For certain types of exceptions, it makes no sense to continue execution.
     *
     * If CanContinue returns false, the correct action is to perform any C++
     * cleanup needed and then return.  If CanContinue returns false and
     * HasTerminated returns true, it is possible to call
     * CancelTerminateExecution in order to continue calling into the engine.
     *
     * @return bool
     */
    public function canContinue(): bool
    {
    }

    /**
     * Returns true if an exception has been caught due to script execution
     * being terminated.
     *
     * There is no JavaScript representation of an execution termination
     * exception.  Such exceptions are thrown when the TerminateExecution
     * methods are called to terminate a long-running script.
     *
     * If such an exception has been thrown, HasTerminated will return true,
     * indicating that it is possible to call CancelTerminateExecution in order
     * to continue calling into the engine.
     *
     * @return bool
     */
    public function hasTerminated(): bool
    {
    }

    /**
     * @return null|Throwable
     */
    public function getExternalException(): ?Throwable
    {
    }
}
