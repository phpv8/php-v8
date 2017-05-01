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

class Script
{
    /**
     * @var Context
     */
    private $context;

    /**
     * @param Context      $context
     * @param StringValue  $source
     * @param ScriptOrigin $origin
     */
    public function __construct(Context $context, StringValue $source, ScriptOrigin $origin = null)
    {
        $this->context = $context;
    }

    /**
     * @return Isolate
     */
    public function GetIsolate(): Isolate
    {
    }

    /**
     * @return \V8\Context
     */
    public function GetContext(): Context
    {
    }

    /**
     * Runs the script returning the resulting value.
     *
     * @param \V8\Context $context
     *
     * @return BooleanValue | FunctionObject | NumberValue | ObjectValue | StringValue | Value
     */
    public function Run(Context $context): Value
    {
    }

    /**
     * Returns the corresponding context-unbound script.
     *
     * @return \V8\UnboundScript
     */
    public function GetUnboundScript(): UnboundScript
    {
    }
}
