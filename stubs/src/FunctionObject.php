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
 * A JavaScript function object (ECMA-262, 15.3).
 */
class  FunctionObject extends ObjectValue
{
    /**
     * Create a function in the current execution context
     * for a given FunctionCallback.
     *
     * @param \V8\Context $context
     * @param callable    $callback
     * @param int         $length
     */
    public function __construct(Context $context, callable $callback, int $length = 0)
    {
        parent::__construct($context);
    }

    /**
     * @param \V8\Context $context
     * @param \V8\Value[] $arguments
     *
     * @return \V8\ObjectValue
     */
    public function NewInstance(Context $context, array $arguments = []) : ObjectValue
    {
    }

    /**
     * @param \V8\Context $context
     * @param \V8\Value   $recv
     * @param \V8\Value[] $arguments
     *
     * @return \V8\Value
     */
    public function Call(Context $context, Value $recv, array $arguments = []) : Value
    {
    }

    /**
     * @param StringValue $name
     */
    public function SetName(StringValue $name)
    {
    }

    /**
     * @return \V8\Value | StringValue
     */
    public function GetName() : Value
    {
    }

    /**
     * Name inferred from variable or property assignment of this function.
     * Used to facilitate debugging and profiling of JavaScript code written
     * in an OO style, where many functions are anonymous but are assigned
     * to object properties.
     *
     * @return \V8\Value | StringValue
     */
    public function GetInferredName() : Value
    {
    }

    /**
     * User-defined name assigned to the "displayName" property of this function.
     * Used to facilitate debugging and profiling of JavaScript code.
     *
     * @return \V8\Value | StringValue
     */
    public function GetDisplayName() : Value
    {
    }

    /**
     * Returns zero based line number of function body and
     * kLineOffsetNotFound if no information available.
     *
     * NOTE: null used instead of kLineOffsetNotFound
     *
     * @return int | null
     */
    public function GetScriptLineNumber()
    {
    }

    /**
     * Returns zero based column number of function body and
     * kLineOffsetNotFound if no information available.
     * NOTE: null used instead of kLineOffsetNotFound
     *
     * @return int | null
     */
    public function GetScriptColumnNumber()
    {
    }


    ///**
    // * Returns scriptId.
    // */
    //int ScriptId() const;

    /**
     * Returns the original function if this function is bound, else returns
     * v8::Undefined.
     *
     * @return Value
     */
    public function GetBoundFunction() : Value
    {
    }

    /**
     * @return ScriptOrigin
     */
    public function GetScriptOrigin() : ScriptOrigin
    {
    }
}
