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

namespace v8;

class Script
{
    private $isolate;
    private $context;
    private $source;
    private $origin;

    /**
     * @param Context      $context
     * @param StringValue  $source
     * @param ScriptOrigin $origin
     */
    public function __construct(Context $context, StringValue $source, ScriptOrigin $origin = null)
    {
        // $this->isolate = $context->isolate;
        $this->context = $context;
        $this->source = $source;
        $this->origin = $origin;
    }

    /**
     * @return Isolate
     */
    public function GetIsolate() : Isolate
    {
    }

    /**
     * @return \v8\Context
     */
    public function GetContext() : Context
    {
    }

    /**
     * @return StringValue
     */
    public function getSource() : StringValue
    {
        return $this->source;
    }

    /**
     * @return ScriptOrigin
     */
    public function getOrigin() : ScriptOrigin
    {
        return $this->origin;
    }

    /**
     * Runs the script returning the resulting value. It will be run in the
     * context in which it was created (ScriptCompiler::CompileBound or
     * UnboundScript::BindToCurrentContext()).
     *
     * @return \v8\Value | \v8\StringValue | \v8\BooleanValue | \v8\NumberValue | \v8\ObjectValue | \v8\FunctionObject
     */
    public function Run() : Value
    {
    }

    /**
     * Returns the corresponding context-unbound script.
     */
    //Local<UnboundScript> GetUnboundScript();
}