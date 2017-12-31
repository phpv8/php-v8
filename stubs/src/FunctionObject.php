<?php declare(strict_types=1);

/**
 * This file is part of the pinepain/php-v8 PHP extension.
 *
 * Copyright (c) 2015-2018 Bogdan Padalko <pinepain@gmail.com>
 *
 * Licensed under the MIT license: http://opensource.org/licenses/MIT
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source or visit
 * http://opensource.org/licenses/MIT
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
     * @param Context  $context
     * @param callable $callback
     * @param int      $length
     * @param int      $behavior
     */
    public function __construct(Context $context, callable $callback, int $length = 0, int $behavior = ConstructorBehavior::ALLOW)
    {
        parent::__construct($context);
    }

    /**
     * @param Context $context
     * @param Value[] $arguments
     *
     * @return ObjectValue
     */
    public function newInstance(Context $context, array $arguments = []): ObjectValue
    {
    }

    /**
     * @param Context $context
     * @param Value   $recv
     * @param Value[] $arguments
     *
     * @return Value|PrimitiveValue|ObjectValue
     */
    public function call(Context $context, Value $recv, array $arguments = []): Value
    {
    }

    /**
     * @param StringValue $name
     */
    public function setName(StringValue $name)
    {
    }

    /**
     * @return Value|StringValue
     */
    public function getName(): Value
    {
    }

    /**
     * Name inferred from variable or property assignment of this function.
     * Used to facilitate debugging and profiling of JavaScript code written
     * in an OO style, where many functions are anonymous but are assigned
     * to object properties.
     *
     * @return Value|StringValue
     */
    public function getInferredName(): Value
    {
    }

    /**
     * User-defined name assigned to the "displayName" property of this function.
     * Used to facilitate debugging and profiling of JavaScript code.
     *
     * @return Value|StringValue
     */
    public function getDisplayName(): Value
    {
    }

    /**
     * Returns zero based line number of function body and
     * null if no information available.
     *
     * @return int|null
     */
    public function getScriptLineNumber(): ?int
    {
    }

    /**
     * Returns zero based column number of function body and
     * null if no information available.
     *
     * @return int|null
     */
    public function getScriptColumnNumber(): ?int
    {
    }


    /**
     * Returns script id where function was created and null if no information available.
     *
     * @return int
     */
    public function getScriptId(): ?int
    {
    }

    /**
     * Returns the original function if this function is bound, else returns UndefinedValue.
     *
     * @return Value|FunctionObject|UndefinedValue
     */
    public function getBoundFunction(): Value
    {
    }

    /**
     * @return ScriptOrigin
     */
    public function getScriptOrigin(): ScriptOrigin
    {
    }
}
