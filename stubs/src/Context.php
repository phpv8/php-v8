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

/**
 * A sandboxed execution context with its own set of built-in objects
 * and functions.
 */
class Context
{
    /**
     * @var Isolate
     */
    private $isolate;

    /**
     * Creates a new context and returns a handle to the newly allocated
     * context.
     *
     * \param isolate The isolate in which to create the context.
     *
     * \param global_template An optional object template from which the
     * global object for the newly created context will be created.
     *
     * \param global_object An optional global object to be reused for
     * the newly created context. This global object must have been
     * created by a previous call to Context::New with the same global
     * template. The state of the global object will be completely reset
     * and only object identify will remain.
     *
     * @param Isolate             $isolate
     * @param ObjectTemplate|null $global_template
     * @param ObjectValue|null    $global_object
     *
     * @internal param array|null $extensions Currently unused as there are not extensions support
     */
    public function __construct(Isolate $isolate, ObjectTemplate $global_template = null, ObjectValue $global_object = null)
    {
    }

    /**
     * Enter context and execute callback
     *
     * @param callable $callback Callback to execute. Current isolate will be passed as first argument and current context object as second.
     *
     * @return mixed Value returned by callback
     */
    public function within(callable $callback)
    {
    }

    /**
     * @return Isolate
     */
    public function getIsolate(): Isolate
    {
        return $this->isolate;
    }

    /**
     * Returns the global proxy object.
     *
     * Global proxy object is a thin wrapper whose prototype points to actual
     * context's global object with the properties like Object, etc. This is done
     * that way for security reasons (for more details see
     * https://wiki.mozilla.org/Gecko:SplitWindow).
     *
     * Please note that changes to global proxy object prototype most probably
     * would break VM---v8 expects only global object as a prototype of global
     * proxy object.
     *
     * @return ObjectValue
     */
    public function globalObject(): ObjectValue
    {
    }


    /**
     * Detaches the global object from its context before
     * the global object can be reused to create a new context.
     */
    public function detachGlobal()
    {
    }

    /**
     * Sets the security token for the context.  To access an object in
     * another context, the security tokens must match.
     *
     * @param Value $token
     */
    public function setSecurityToken(Value $token)
    {
    }

    /**
     * Restores the security token to the default value.
     */
    public function useDefaultSecurityToken()
    {
    }

    /**
     * Returns the security token of this context.
     *
     * @return Value|PrimitiveValue|ObjectValue
     */
    public function getSecurityToken(): Value
    {
    }

    /**
     * Control whether code generation from strings is allowed. Calling
     * this method with false will disable 'eval' and the 'Function'
     * constructor for code running in this context. If 'eval' or the
     * 'Function' constructor are used an exception will be thrown.
     *
     * If code generation from strings is not allowed the
     * V8::AllowCodeGenerationFromStrings callback will be invoked if
     * set before blocking the call to 'eval' or the 'Function'
     * constructor. If that callback returns true, the call will be
     * allowed, otherwise an exception will be thrown. If no callback is
     * set an exception will be thrown.
     *
     * @param bool $allow
     */
    public function allowCodeGenerationFromStrings(bool $allow)
    {
    }

    /**
     * Returns true if code generation from strings is allowed for the context.
     * For more details see AllowCodeGenerationFromStrings(bool) documentation.
     *
     * @return bool
     */
    public function isCodeGenerationFromStringsAllowed(): bool
    {
    }

    /**
     * Sets the error description for the exception that is thrown when
     * code generation from strings is not allowed and 'eval' or the 'Function'
     * constructor are called.
     *
     * @param StringValue $message
     */
    public function setErrorMessageForCodeGenerationFromStrings(StringValue $message)
    {
    }
}
