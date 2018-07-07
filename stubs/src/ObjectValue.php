<?php declare(strict_types=1);

/**
 * This file is part of the phpv8/php-v8 PHP extension.
 *
 * Copyright (c) 2015-2018 Bogdan Padalko <thepinepain@gmail.com>
 *
 * Licensed under the MIT license: http://opensource.org/licenses/MIT
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source or visit
 * http://opensource.org/licenses/MIT
 */


namespace V8;


/**
 * A JavaScript object (ECMA-262, 4.3.3)
 */
class ObjectValue extends Value implements AdjustableExternalMemoryInterface
{
    public function __construct(Context $context)
    {
        parent::__construct($context->getIsolate());
    }

    /**
     * @return Context
     */
    public function getContext()
    {
    }

    /**
     * @param Context $context
     * @param Value   $key
     * @param Value   $value
     *
     * @return bool
     */
    public function set(Context $context, Value $key, Value $value): bool
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
    public function createDataProperty(Context $context, NameValue $key, Value $value): bool
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
    public function defineOwnProperty(
        Context $context,
        NameValue $key,
        Value $value,
        int $attributes = PropertyAttribute::NONE
    ): bool {
    }

    /**
     * @param Context $context
     * @param Value   $key
     *
     * @return Value|PrimitiveValue|ObjectValue
     */
    public function get(Context $context, Value $key): Value
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
    public function getPropertyAttributes(Context $context, NameValue $key): int
    {
    }

    /**
     * Returns Object.getOwnPropertyDescriptor as per ES5 section 15.2.3.3.
     *
     * @param Context   $context
     * @param NameValue $key
     *
     * @return Value|PrimitiveValue|ObjectValue
     */
    public function getOwnPropertyDescriptor(Context $context, NameValue $key): Value
    {
    }

    /**
     * @param Context $context
     * @param Value   $key
     *
     * @return bool
     */
    public function has(Context $context, Value $key): bool
    {
    }

    /**
     * @param Context $context
     * @param Value   $key
     *
     * @return bool
     */
    public function delete(Context $context, Value $key): bool
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
    public function setAccessor(
        Context $context,
        NameValue $name,
        callable $getter,
        callable $setter = null,
        int $settings = AccessControl::DEFAULT_ACCESS,
        int $attributes = PropertyAttribute::NONE
    ): bool {
    }

    /**
     * @param NameValue           $name
     * @param FunctionObject      $getter
     * @param FunctionObject|null $setter
     * @param int                 $attributes
     * @param int                 $settings
     */
    public function setAccessorProperty(
        NameValue $name,
        FunctionObject $getter,
        FunctionObject $setter = null,
        int $attributes = PropertyAttribute::NONE,
        int $settings = AccessControl::DEFAULT_ACCESS
    ) {
    }

    /**
     * Sets a native data property like Template::SetNativeDataProperty, but
     * this method sets on this object directly.
     *
     * @param Context   $context
     * @param NameValue $name
     * @param callable  $getter
     * @param callable  $setter
     * @param int       $attributes
     *
     * @return bool
     */
    public function setNativeDataProperty(
        Context $context,
        NameValue $name,
        callable $getter,
        callable $setter = null,
        int $attributes = PropertyAttribute::NONE
    ): bool {
    }

    /**
     * Attempts to create a property with the given name which behaves like a data
     * property, except that the provided getter is invoked (and provided with the
     * data value) to supply its value the first time it is read. After the
     * property is accessed once, it is replaced with an ordinary data property.
     *
     * Analogous to Template::SetLazyDataProperty.
     *
     * @param Context   $context
     * @param NameValue $name
     * @param callable  $getter
     * @param int       $attributes
     *
     * @return bool
     */
    public function setLazyDataProperty(
        Context $context,
        NameValue $name,
        callable $getter,
        callable $setter = null,
        int $attributes = PropertyAttribute::NONE
    ): bool {
    }


    /**
     * Returns an array containing the names of the enumerable properties
     * of this object, including properties from prototype objects.  The
     * array returned by this method contains the same values as would
     * be enumerated by a for-in statement over this object.
     *
     * @param Context $context
     * @param int     $mode One of KeyCollectionMode options
     * @param int     $property_filter One or multiple PropertyFilter options
     * @param int     $index_filter One or multiple IndexFilter options
     * @param bool    $convert_to_strings Convert integer indices to strings
     *
     * @return ArrayObject
     */
    public function getPropertyNames(
        Context $context,
        int $mode = KeyCollectionMode::kOwnOnly,
        int $property_filter = PropertyFilter::ALL_PROPERTIES,
        int $index_filter = IndexFilter::kIncludeIndices,
        bool $convert_to_strings = false
    ): ArrayObject {
    }

    /**
     * This function has the same functionality as GetPropertyNames but
     * the returned array doesn't contain the names of properties from
     * prototype objects.
     *
     * @param Context $context
     * @param int     $filter One or multiple PropertyFilter options
     * @param bool    $convert_to_strings Will convert integer indices to strings
     *
     * @return ArrayObject
     */
    public function getOwnPropertyNames(
        Context $context,
        int $filter = PropertyFilter::ALL_PROPERTIES,
        bool $convert_to_strings = false
    ): ArrayObject {
    }

    /**
     * Get the prototype object.  This does not skip objects marked to
     * be skipped by __proto__ and it does not consult the security
     * handler.
     *
     * @return Value
     */
    public function getPrototype(): Value
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
    public function setPrototype(Context $context, Value $prototype): bool
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
    public function findInstanceInPrototypeChain(FunctionTemplate $tmpl): Object
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
    public function objectProtoToString(Context $context): StringValue
    {
    }

    /**
     * Returns the name of the function invoked as a constructor for this object.
     *
     * @return StringValue
     */
    public function getConstructorName(): StringValue
    {
    }

    /**
     * Sets the integrity level of the object.
     *
     * @param Context $context
     * @param int     $level One of IntegrityLevel::{kFrozen, kSealed}
     *
     * @return bool
     */
    public function setIntegrityLevel(Context $context, int $level): bool
    {
    }

    /**
     * @param Context   $context
     * @param NameValue $key
     *
     * @return bool
     */
    public function hasOwnProperty(Context $context, NameValue $key): bool
    {
    }

    /**
     * @param Context   $context
     *
     * @param NameValue $key
     *
     * @return bool
     */
    public function hasRealNamedProperty(Context $context, NameValue $key): bool
    {
    }

    /**
     * @param Context $context
     * @param int     $index
     *
     * @return bool
     */
    public function hasRealIndexedProperty(Context $context, int $index): bool
    {
    }

    /**
     * @param Context   $context
     * @param NameValue $key
     *
     * @return bool
     */
    public function hasRealNamedCallbackProperty(Context $context, NameValue $key): bool
    {
    }

    /**
     * If result.IsEmpty() no real property was located in the prototype chain.
     * This means interceptors in the prototype chain are not called.
     *
     * @param Context   $context
     * @param NameValue $key
     *
     * @return Value
     */
    public function getRealNamedPropertyInPrototypeChain(Context $context, NameValue $key): Value
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
    public function getRealNamedPropertyAttributesInPrototypeChain(Context $context, NameValue $key): int
    {
    }

    /**
     * If result.IsEmpty() no real property was located on the object or
     * in the prototype chain.
     * This means interceptors in the prototype chain are not called.
     *
     * @param Context   $context
     * @param NameValue $key
     *
     * @return Value
     */
    public function getRealNamedProperty(Context $context, NameValue $key): Value
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
    public function getRealNamedPropertyAttributes(Context $context, NameValue $key): int
    {
    }


    /**
     * Tests for a named lookup interceptor.
     *
     * @return bool
     */
    public function hasNamedLookupInterceptor(): bool
    {
    }


    /** Tests for an index lookup interceptor.
     *
     * @return bool
     */
    public function hasIndexedLookupInterceptor(): bool
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
    public function getIdentityHash(): int
    {
    }

    /**
     * Clone this object with a fast but shallow copy.  Values will point
     * to the same values as the original object.
     *
     * @return ObjectValue
     */
    public function clone(): ObjectValue
    {
    }

    /**
     * Checks whether a callback is set by the
     * ObjectTemplate::SetCallAsFunctionHandler method.
     * When an Object is callable this method returns true.
     *
     * @return bool
     */
    public function isCallable(): bool
    {
    }

    /**
     * True if this object is a constructor.
     *
     * @return bool
     */
    public function isConstructor(): bool
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
     * @return Value|PrimitiveValue|ObjectValue
     */
    public function callAsFunction(Context $context, Value $recv, array $arguments = []): Value
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
     * @return Value|PrimitiveValue|ObjectValue
     */
    public function callAsConstructor(Context $context, array $arguments = []): Value
    {
    }

    /**
     * {@inheritdoc}
     */
    public function adjustExternalAllocatedMemory(int $change_in_bytes): int
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getExternalAllocatedMemory(): int
    {
    }
}
