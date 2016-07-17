<?php

/*
  +----------------------------------------------------------------------+
  | This file is part of the pinepain/php-v8 PHP extension.              |
  |                                                                      |
  | Copyright (c) 2015-2016 Bogdan Padalko <pinepain@gmail.com>          |
  |                                                                      |
  | Licensed under the MIT license: http://opensource.org/licenses/MIT   |
  |                                                                      |
  | For the full copyright and license information, please view the      |
  | LICENSE file that was distributed with this source or visit          |
  | http://opensource.org/licenses/MIT                                   |
  +----------------------------------------------------------------------+
*/

namespace V8;

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
     * Creates a new try/catch block and registers it with v8.  Note that
     * all TryCatch blocks should be stack allocated because the memory
     * location itself is compared against JavaScript try/catch blocks.
     *
     * @param Isolate $isolate
     * @param Context $context
     * @param Value   $exception
     * @param Value   $stack_trace
     * @param Message $message
     * @param bool    $can_continue
     * @param bool    $has_terminated
     */
    public function __construct(Isolate $isolate,
                                Context $context,
                                Value $exception = null,
                                Value $stack_trace = null,
                                Message $message = null,
                                bool $can_continue = false,
                                bool $has_terminated = false)
    {
        $this->isolate = $isolate;
        $this->exception = $exception;
        $this->stack_trace = $stack_trace;
        $this->message = $message;
        $this->can_continue = $can_continue;
        $this->has_terminated = $has_terminated;
    }

    public function GetIsolate() : Isolate
    {
        return $this->isolate;
    }

    public function GetContext() : Context
    {
        return $this->context;
    }

    /**
     * Returns the exception caught by this try/catch block.  If no exception has
     * been caught an empty handle is returned.
     *
     * The returned handle is valid until this TryCatch block has been destroyed.
     *
     * @return \V8\Value | null
     *
     */
    public function Exception()
    {
        return $this->exception;
    }

    /**
     * Returns the .stack property of the thrown object.  If no .stack
     * property is present an empty handle is returned.
     *
     * @return Value | null
     */
    public function StackTrace()
    {
        return $this->stack_trace;
    }

    /**
     * Returns the message associated with this exception.  If there is
     * no message associated an empty handle is returned.
     *
     * The returned handle is valid until this TryCatch block has been
     * destroyed.
     *
     * @return Message | null
     */
    public function Message()
    {
        return $this->message;
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
    public function CanContinue() : bool
    {
        return $this->can_continue;
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
    public function HasTerminated() : bool
    {
        return $this->has_terminated;
    }
}
