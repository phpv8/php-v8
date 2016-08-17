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
 * A JavaScript object (ECMA-262, 4.3.3)
 */
class ObjectValue extends Value
{
    public function __construct(Context $context)
    {
        parent::__construct($context->GetIsolate());
    }

    /**
     * @return \V8\Context
     */
    public function GetContext()
    {
    }

    /**
     * @param Context $context
     * @param Value   $key
     * @param Value   $value
     *
     * @return bool
     */
    public function Set(Context $context, Value $key, Value $value) : bool
    {
    }

    /**
     * @param Context $context
     * @param int     $index
     * @param Value   $value
     *
     * @return bool
     */
    public function SetIndex(Context $context, int $index, Value $value) : bool
    {
    }

    /**
     * Implements CreateDataProperty (ECMA-262, 7.3.4).
     *
     * Defines a configurable, writable, enumerable property with the given value
     * on the object unless the property already exists and is not configurable
     * or the object is not extensible.
     *
     * @param Context   $context
     * @param NameValue $key
     * @param Value     $value
     *
     * @return bool
     */
    public function CreateDataProperty(Context $context, NameValue $key, Value $value) : bool
    {
    }

    /**
     * Implements CreateDataProperty (ECMA-262, 7.3.4).
     *
     * Defines a configurable, writable, enumerable property with the given value
     * on the object unless the property already exists and is not configurable
     * or the object is not extensible.
     *
     * @param Context $context
     * @param int     $index
     * @param Value   $value
     *
     * @return bool
     */
    public function CreateDataPropertyIndex(Context $context, int $index, Value $value) : bool
    {
    }

    /**
     * Implements DefineOwnProperty.
     *
     * In general, CreateDataProperty will be faster, however, does not allow for specifying attributes.
     *
     * @param Context   $context
     * @param NameValue $key
     * @param Value     $value
     * @param int       $attributes
     *
     * @return bool
     */
    public function DefineOwnProperty(Context $context, NameValue $key, Value $value, int $attributes = PropertyAttribute::None) : bool
    {
    }

    /**
     * @param Context $context
     * @param Value   $key
     *
     * @return \V8\Value | \V8\ObjectValue | \V8\FunctionObject | ArrayObject | StringValue | NumberValue
     */
    public function Get(Context $context, Value $key) : Value
    {
    }

    /**
     * @param Context $context
     * @param int     $index
     *
     * @return \V8\Value | \V8\ObjectValue | \V8\FunctionObject
     */
    public function GetIndex(Context $context, int $index) : Value
    {
    }

    /**
     * Gets the property attributes of a property which can be None or
     * any combination of ReadOnly, DontEnum and DontDelete. Returns
     * None when the property doesn't exist.
     *
     * @param Context   $context
     * @param NameValue $key
     *
     * @return int
     */
    public function GetPropertyAttributes(Context $context, NameValue $key) : int
    {
    }

    /**
     * Returns Object.getOwnPropertyDescriptor as per ES5 section 15.2.3.3.
     *
     * @param Context   $context
     * @param NameValue $key
     *
     * @return \V8\Value
     */
    public function GetOwnPropertyDescriptor(Context $context, NameValue $key) : Value
    {
    }

    /**
     * @param Context $context
     * @param Value   $key
     *
     * @return bool
     */
    public function Has(Context $context, Value $key) : bool
    {
    }

    /**
     * @param Context $context
     * @param int     $index
     *
     * @return bool
     */
    public function HasIndex(Context $context, int $index) : bool
    {
    }

    /**
     * @param Context $context
     * @param Value   $key
     *
     * @return bool
     */
    public function Delete(Context $context, Value $key) : bool
    {
    }

    /**
     * @param Context $context
     *
     * @param int     $index
     *
     * @return bool
     */
    public function DeleteIndex(Context $context, int $index) : bool
    {
    }

    /**
     * @param Context   $context
     * @param NameValue $name
     * @param callable  $getter
     * @param callable  $setter
     * @param int       $settings
     * @param int       $attributes
     *
     * @return bool
     */
    public function SetAccessor(
        Context $context,
        NameValue $name,
        callable $getter,
        callable $setter = null,
        int $settings = AccessControl::DEFAULT_ACCESS,
        int $attributes = PropertyAttribute::None
    ) : bool
    {
    }

    /**
     * @param NameValue     $name
     * @param callable      $getter
     * @param callable|null $setter
     * @param int           $attributes
     * @param int           $settings
     */
    public function SetAccessorProperty(
        NameValue $name,
        callable $getter,
        callable $setter = null,
        int $attributes = PropertyAttribute::None,
        int $settings = AccessControl::DEFAULT_ACCESS
    ) {
    }

    /**
     * Returns an array containing the names of the enumerable properties
     * of this object, including properties from prototype objects.  The
     * array returned by this method contains the same values as would
     * be enumerated by a for-in statement over this object.
     *
     * @param Context $context
     *
     *
     * @return \V8\ArrayObject
     */
    public function GetPropertyNames(Context $context) : ArrayObject
    {
    }

    /**
     * This function has the same functionality as GetPropertyNames but
     * the returned array doesn't contain the names of properties from
     * prototype objects.
     *
     * @param Context $context
     *
     *
     * @return \V8\ArrayObject
     */
    public function GetOwnPropertyNames(Context $context) : ArrayObject
    {
    }

    /**
     * Get the prototype object.  This does not skip objects marked to
     * be skipped by __proto__ and it does not consult the security
     * handler.
     *
     * @return \V8\Value
     */
    public function GetPrototype() : Value
    {
    }

    /**
     * Set the prototype object.  This does not skip objects marked to
     * be skipped by __proto__ and it does not consult the security
     * handler.
     *
     * @param Context $context
     * @param Value   $prototype
     *
     * @return bool
     */
    public function SetPrototype(Context $context, Value $prototype) : bool
    {
    }

    /**
     * Finds an instance of the given function template in the prototype
     * chain.
     *
     * @param FunctionTemplate $tmpl
     *
     * @return Object
     */
    public function FindInstanceInPrototypeChain(FunctionTemplate $tmpl) : Object
    {
    }

    /**
     * Call builtin Object.prototype.toString on this object.
     * This is different from Value::ToString() that may call
     * user-defined toString function. This one does not.
     *
     * @param Context $context
     *
     * @return StringValue
     */
    public function ObjectProtoToString(Context $context) : StringValue
    {
    }

    /**
     * Returns the name of the function invoked as a constructor for this object.
     *
     * @return StringValue
     */
    public function GetConstructorName() : StringValue
    {
    }

    /**
     * @param Context $context
     * @param string  $key
     *
     * @return bool
     */
    public function HasOwnProperty(Context $context, $key) : bool
    {
    }

    /**
     * @param Context $context
     * @param string  $key
     *
     * @return bool
     */
    public function HasRealNamedProperty(Context $context, $key) : bool
    {
    }

    /**
     * @param Context $context
     * @param string  $index
     *
     * @return bool
     */
    public function HasRealIndexedProperty(Context $context, $index) : bool
    {
    }

    /**
     * @param Context $context
     * @param string  $key
     *
     * @return bool
     */
    public function HasRealNamedCallbackProperty(Context $context, $key) : bool
    {
    }

    /**
     * If result.IsEmpty() no real property was located in the prototype chain.
     * This means interceptors in the prototype chain are not called.
     *
     * @param Context $context
     * @param string  $key
     *
     * @return \V8\Value
     */
    public function GetRealNamedPropertyInPrototypeChain(Context $context, $key) : Value
    {
    }

    /**
     * Gets the property attributes of a real property in the prototype chain,
     * which can be None or any combination of ReadOnly, DontEnum and DontDelete.
     * Interceptors in the prototype chain are not called.
     *
     * @param Context   $context
     * @param NameValue $key
     *
     * @return int
     */
    public function GetRealNamedPropertyAttributesInPrototypeChain(Context $context, NameValue $key) : int
    {
    }

    /**
     * If result.IsEmpty() no real property was located on the object or
     * in the prototype chain.
     * This means interceptors in the prototype chain are not called.
     *
     * @param Context $context
     * @param string  $key
     *
     * @return \V8\Value
     */
    public function GetRealNamedProperty(Context $context, $key) : Value
    {
    }

    /**
     * Gets the property attributes of a real property which can be
     * None or any combination of ReadOnly, DontEnum and DontDelete.
     * Interceptors in the prototype chain are not called.
     *
     * @param Context   $context
     * @param NameValue $key
     *
     * @return int
     */
    public function GetRealNamedPropertyAttributes(Context $context, NameValue $key) : int
    {
    }


    /**
     * Tests for a named lookup interceptor.
     *
     * @return bool
     */
    public function HasNamedLookupInterceptor() : bool
    {
    }


    /** Tests for an index lookup interceptor.
     *
     * @return bool
     */
    public function HasIndexedLookupInterceptor() : bool
    {
    }

    /**
     * Returns the identity hash for this object. The current implementation
     * uses a hidden property on the object to store the identity hash.
     *
     * The return value will never be 0. Also, it is not guaranteed to be
     * unique.
     *
     * @return int
     */
    public function GetIdentityHash() : int
    {
    }

    /**
     * Clone this object with a fast but shallow copy.  Values will point
     * to the same values as the original object.
     *
     * @return ObjectValue
     */
    public function Clone() : ObjectValue
    {
    }

    /**
     * Returns the context in which the object was created.
     * Note: Object may be created outside context!
     *
     * @return \V8\Context
     */
    public function CreationContext() : Context
    {
    }

    /**
     * Checks whether a callback is set by the
     * ObjectTemplate::SetCallAsFunctionHandler method.
     * When an Object is callable this method returns true.
     *
     * @return bool
     */
    public function IsCallable() : bool
    {
    }

    /**
     * True if this object is a constructor.
     *
     * @return bool
     */
    public function IsConstructor() : bool
    {
    }

    /**
     * Call an Object as a function if a callback is set by the
     * ObjectTemplate::SetCallAsFunctionHandler method.
     *
     * @param Context $context
     * @param Value   $recv
     * @param array   $arguments
     *
     * @return \V8\Value
     */
    public function CallAsFunction(Context $context, Value $recv, array $arguments = []) : Value
    {
    }

    /**
     * Call an Object as a constructor if a callback is set by the
     * ObjectTemplate::SetCallAsFunctionHandler method.
     * Note: This method behaves like the Function::NewInstance method.
     *
     * @param Context $context
     * @param array   $arguments
     *
     * @return \V8\Value
     */
    public function CallAsConstructor(Context $context, array $arguments = []) : Value
    {
    }
}
