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
 * An ObjectTemplate is used to create objects at runtime.
 *
 * Properties added to an ObjectTemplate are added to each object
 * created from the ObjectTemplate.
 */
class ObjectTemplate extends Template implements AdjustableExternalMemoryInterface
{
    public function __construct(Isolate $isolate, FunctionTemplate $constructor = null)
    {
        parent::__construct($isolate);
    }

    /** Creates a new instance of this template.
     *
     * @param Context $context
     *
     * @return ObjectValue
     */
    public function newInstance(Context $context): ObjectValue
    {
    }

    /**
     * Sets an accessor on the object template.
     *
     * Whenever the property with the given name is accessed on objects
     * created from this ObjectTemplate the getter and setter callbacks
     * are called instead of getting and setting the property directly
     * on the JavaScript object.
     *
     * @param NameValue        $name
     * @param NameValue        $name       The name of the property for which an accessor is added.
     *
     * @param callable         $getter     The callback to invoke when getting the property.
     *                                     Callback signature should be (NameValue $property, PropertyCallbackInfo $info)
     *
     * @param callable         $setter     The callback to invoke when setting the property.
     *                                     Callback signature should be (NameValue $property, PropertyCallbackInfo $info)
     *
     * @param int              $settings   Access control settings for the accessor.
     *
     * @param int              $attributes The attributes of the property for which an accessor is added.
     *
     * @param FunctionTemplate $receiver   The signature describes valid receivers for the accessor
     *                                     and is used to perform implicit instance checks against them. If the
     *                                     receiver is incompatible (i.e. is not an instance of the constructor as
     *                                     defined by FunctionTemplate::HasInstance()), an implicit TypeError is
     *                                     thrown and no callback is invoked.
     */

    public function setAccessor(
        NameValue $name,
        callable $getter,
        callable $setter,
        $settings = AccessControl::DEFAULT_ACCESS,
        $attributes = PropertyAttribute::NONE,
        FunctionTemplate $receiver
    ) {
    }

    /**
     * Sets a named property handler on the object template.
     *
     * Whenever a property whose name is a string or a symbol is accessed on
     * objects created from this object template, the provided callback is
     * invoked instead of accessing the property directly on the JavaScript
     * object.
     *
     * See NamedPropertyHandlerConfiguration constructor argument description for details
     *
     * @param NamedPropertyHandlerConfiguration The NamedPropertyHandlerConfiguration that defines the callbacks to invoke when accessing a property.
     */
    public function setHandlerForNamedProperty(NamedPropertyHandlerConfiguration $configuration)
    {
    }

    /**
     * Sets an indexed property handler on the object template.
     *
     * Whenever an indexed property is accessed on objects created from
     * this object template, the provided callback is invoked instead of
     * accessing the property directly on the JavaScript object.
     *
     * See IndexedPropertyHandlerConfiguration constructor argument description for details
     *
     * @param IndexedPropertyHandlerConfiguration $configuration The IndexedPropertyHandlerConfiguration that defines the callbacks to invoke when accessing a property.
     */
    public function setHandlerForIndexedProperty(IndexedPropertyHandlerConfiguration $configuration)
    {
    }

    /**
     * Sets the callback to be used when calling instances created from
     * this template as a function.  If no callback is set, instances
     * behave like normal JavaScript objects that cannot be called as a
     * function.
     *
     * @param callable $callback
     */
    public function setCallAsFunctionHandler(callable $callback)
    {
    }


    /**
     * Returns true if the object will be an immutable prototype exotic object.
     *
     * @return bool
     */
    public function isImmutableProto(): bool
    {
    }

    /**
     * Makes the ObjectTempate for an immutable prototype exotic object, with an
     * immutable __proto__.
     *
     * @return bool
     */
    public function setImmutableProto(): bool
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
