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
 * A FunctionTemplate is used to create functions at runtime. There
 * can only be one function created from a FunctionTemplate in a
 * context.  The lifetime of the created function is equal to the
 * lifetime of the context.  So in case the embedder needs to create
 * temporary functions that can be collected using Scripts is
 * preferred.
 *
 * Any modification of a FunctionTemplate after first instantiation will trigger
 * a crash.
 *
 * A FunctionTemplate can have properties, these properties are added to the
 * function object when it is created.
 *
 * A FunctionTemplate has a corresponding instance template which is
 * used to create object instances when the function is used as a
 * constructor. Properties added to the instance template are added to
 * each object instance.
 *
 * A FunctionTemplate can have a prototype template. The prototype template
 * is used to create the prototype object of the function.
 *
 * The following example shows how to use a FunctionTemplate:
 *
 * \code
 *    v8::Local<v8::FunctionTemplate> t = v8::FunctionTemplate::New(isolate);
 *    t->Set(isolate, "func_property", v8::Number::New(isolate, 1));
 *
 *    v8::Local<v8::Template> proto_t = t->PrototypeTemplate();
 *    proto_t->Set(isolate,
 *                 "proto_method",
 *                 v8::FunctionTemplate::New(isolate, InvokeCallback));
 *    proto_t->Set(isolate, "proto_const", v8::Number::New(isolate, 2));
 *
 *    v8::Local<v8::ObjectTemplate> instance_t = t->InstanceTemplate();
 *    instance_t->SetAccessor(String::NewFromUtf8(isolate, "instance_accessor"),
 *                            InstanceAccessorCallback);
 *    instance_t->SetNamedPropertyHandler(PropertyHandlerCallback);
 *    instance_t->Set(String::NewFromUtf8(isolate, "instance_property"),
 *                    Number::New(isolate, 3));
 *
 *    v8::Local<v8::Function> function = t->GetFunction();
 *    v8::Local<v8::Object> instance = function->NewInstance();
 * \endcode
 *
 * Let's use "function" as the JS variable name of the function object
 * and "instance" for the instance object created above.  The function
 * and the instance will have the following properties:
 *
 * \code
 *   func_property in function == true;
 *   function.func_property == 1;
 *
 *   function.prototype.proto_method() invokes 'InvokeCallback'
 *   function.prototype.proto_const == 2;
 *
 *   instance instanceof function == true;
 *   instance.instance_accessor calls 'InstanceAccessorCallback'
 *   instance.instance_property == 3;
 * \endcode
 *
 * A FunctionTemplate can inherit from another one by calling the
 * FunctionTemplate::Inherit method.  The following graph illustrates
 * the semantics of inheritance:
 *
 * \code
 *   FunctionTemplate Parent  -> Parent() . prototype -> { }
 *     ^                                                  ^
 *     | Inherit(Parent)                                  | .__proto__
 *     |                                                  |
 *   FunctionTemplate Child   -> Child()  . prototype -> { }
 * \endcode
 *
 * A FunctionTemplate 'Child' inherits from 'Parent', the prototype
 * object of the Child() function has __proto__ pointing to the
 * Parent() function's prototype object. An instance of the Child
 * function has all properties on Parent's instance templates.
 *
 * Let Parent be the FunctionTemplate initialized in the previous
 * section and create a Child FunctionTemplate by:
 *
 * \code
 *   Local<FunctionTemplate> parent = t;
 *   Local<FunctionTemplate> child = FunctionTemplate::New();
 *   child->Inherit(parent);
 *
 *   Local<Function> child_function = child->GetFunction();
 *   Local<Object> child_instance = child_function->NewInstance();
 * \endcode
 *
 * The Child function and Child instance will have the following
 * properties:
 *
 * \code
 *   child_func.prototype.__proto__ == function.prototype;
 *   child_instance.instance_accessor calls 'InstanceAccessorCallback'
 *   child_instance.instance_property == 3;
 * \endcode
 */
class FunctionTemplate extends Template implements AdjustableExternalMemoryInterface
{
    /**
     * @param Isolate       $isolate
     * @param callable|null $callback
     * @param int           $length
     * @param int           $behavior
     */
    public function __construct(Isolate $isolate, callable $callback = null, int $length = 0, int $behavior = ConstructorBehavior::kAllow)
    {
        parent::__construct($isolate);
    }

    /**
     * Returns the unique function instance in the current execution context.
     *
     * @param Context $context
     *
     * @return FunctionObject
     */
    public function GetFunction(Context $context): FunctionObject
    {
    }

    /**
     * Set the call-handler callback for a FunctionTemplate.  This
     * callback is called whenever the function created from this
     * FunctionTemplate is called.
     *
     * @param callable $callback
     */
    public function SetCallHandler(callable $callback)
    {
    }

    /** Set the predefined length property for the FunctionTemplate. */
    public function SetLength(int $length)
    {
    }

    /**
     * Get the InstanceTemplate.
     *
     * @return \V8\ObjectTemplate
     */
    public function InstanceTemplate(): ObjectTemplate
    {
    }

    /**
     * Causes the function template to inherit from a parent function template.
     *
     * @param FunctionTemplate $parent
     */
    public function Inherit(FunctionTemplate $parent)
    {
    }

    /**
     * A PrototypeTemplate is the template used to create the prototype object
     * of the function created by this template.
     *
     * @return \V8\ObjectTemplate
     */
    public function PrototypeTemplate(): ObjectTemplate
    {
    }

    /**
     * Set the class name of the FunctionTemplate.  This is used for
     * printing objects created with the function created from the
     * FunctionTemplate as its constructor.
     *
     * @param StringValue $name
     */
    public function SetClassName(StringValue $name)
    {
    }


    ///**
    // * When set to true, no access check will be performed on the receiver of a
    // * function call.  Currently defaults to true, but this is subject to change.
    // */
    //void SetAcceptAnyReceiver(bool value);

    /**
     * Determines whether the __proto__ accessor ignores instances of
     * the function template.  If instances of the function template are
     * ignored, __proto__ skips all instances and instead returns the
     * next object in the prototype chain.
     *
     * Call with a value of true to make the __proto__ accessor ignore
     * instances of the function template.  Call with a value of false
     * to make the __proto__ accessor not ignore instances of the
     * function template.  By default, instances of a function template
     * are not ignored.
     */
    public function SetHiddenPrototype($value)
    {
    }

    /**
     * Sets the ReadOnly flag in the attributes of the 'prototype' property
     * of functions created from this FunctionTemplate to true.
     */
    public function ReadOnlyPrototype()
    {
    }

    /**
     * Removes the prototype property from functions created from this
     * FunctionTemplate.
     */
    public function RemovePrototype()
    {
    }

    /**
     * Returns true if the given object is an instance of this function
     * template.
     *
     * @param Value $object
     *
     * @return bool
     */
    public function HasInstance(Value $object): bool
    {
    }

    /**
     * {@inheritdoc}
     */
    public function AdjustExternalAllocatedMemory(int $change_in_bytes): int
    {
    }

    /**
     * {@inheritdoc}
     */
    public function GetExternalAllocatedMemory(): int
    {
    }
}
