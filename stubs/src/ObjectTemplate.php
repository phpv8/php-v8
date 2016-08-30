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
     * @return \V8\ObjectValue
     */
    public function NewInstance(Context $context) : ObjectValue
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
     * \param name The name of the property for which an accessor is added.
     * \param getter The callback to invoke when getting the property.
     * \param setter The callback to invoke when setting the property.
     * \param data A piece of data that will be passed to the getter and setter
     *   callbacks whenever they are invoked.
     * \param settings Access control settings for the accessor. This is a bit
     *   field consisting of one of more of
     *   DEFAULT = 0, ALL_CAN_READ = 1, or ALL_CAN_WRITE = 2.
     *   The default is to not allow cross-context access.
     *   ALL_CAN_READ means that all cross-context reads are allowed.
     *   ALL_CAN_WRITE means that all cross-context writes are allowed.
     *   The combination ALL_CAN_READ | ALL_CAN_WRITE can be used to allow all
     *   cross-context access.
     * \param attribute The attributes of the property for which an accessor
     *   is added.
     * \param signature The signature describes valid receivers for the accessor
     *   and is used to perform implicit instance checks against them. If the
     *   receiver is incompatible (i.e. is not an instance of the constructor as
     *   defined by FunctionTemplate::HasInstance()), an implicit TypeError is
     *   thrown and no callback is invoked.
     *
     * @param NameValue $name
     * @param callable  $getter
     * @param callable  $setter
     * @param int       $settings  \v8\AccessControl constants (one or many)
     * @param int       $attribute \v8\PropertyAttribute constants (one or many)
     *
     * TODO: add signature support
     */
    public function SetAccessor(
        NameValue $name,
        callable $getter,
        callable $setter,
        $settings = AccessControl::DEFAULT_ACCESS,
        $attribute = PropertyAttribute::None
    ) {
    }

    ///**
    // * Sets a named or indexed property handler on the object template.
    // *
    // * See \v8\NamedPropertyHandlerConfiguration and \v8\IndexedPropertyHandlerConfiguration constructor argument
    // * description for details
    // *
    // * @param \v8\NamedPropertyHandlerConfiguration | \v8\IndexedPropertyHandlerConfiguration $configuration
    // */
    //public function SetHandler(NamedPropertyHandlerConfiguration $configuration)
    //{
    //}

    /**
     * Sets a named property handler on the object template.
     *
     * Whenever a property whose name is a string or a symbol is accessed on
     * objects created from this object template, the provided callback is
     * invoked instead of accessing the property directly on the JavaScript
     * object.
     *
     * See \v8\NamedPropertyHandlerConfiguration constructor argument description for details
     *
     * @param \v8\NamedPropertyHandlerConfiguration The NamedPropertyHandlerConfiguration that defines the callbacks to invoke when accessing a property.
     */
    public function SetHandlerForNamedProperty(NamedPropertyHandlerConfiguration $configuration)
    {
    }

    /**
     * Sets an indexed property handler on the object template.
     *
     * Whenever an indexed property is accessed on objects created from
     * this object template, the provided callback is invoked instead of
     * accessing the property directly on the JavaScript object.
     *
     * See \v8\IndexedPropertyHandlerConfiguration constructor argument description for details
     *
     * @param \V8\IndexedPropertyHandlerConfiguration $configuration The IndexedPropertyHandlerConfiguration that defines the callbacks to invoke when accessing a property.
     */
    public function SetHandlerForIndexedProperty(IndexedPropertyHandlerConfiguration $configuration)
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
    public function SetCallAsFunctionHandler(callable $callback)
    {
    }

    /**
     * Mark object instances of the template as undetectable.
     *
     * In many ways, undetectable objects behave as though they are not
     * there.  They behave like 'undefined' in conditionals and when
     * printed.  However, properties can be accessed and called as on
     * normal objects.
     */
    public function MarkAsUndetectable()
    {
    }

    // Disabled due to https://groups.google.com/forum/#!topic/v8-dev/c7LhW2bNabY and it should be not necessary to use
    // it in other then browser setup in most cases, though It would be nice to have it for API consistency reason.
    ///**
    // * Sets access check callback on the object template and enables access
    // * checks.
    // *
    // * When accessing properties on instances of this object template,
    // * the access check callback will be called to determine whether or
    // * not to allow cross-context access to the properties.
    // */
    //public function SetAccessCheckCallback(callable $callback)
    //{
    //}

    /**
     * {@inheritdoc}
     */
    public function AdjustExternalAllocatedMemory(int $change_in_bytes) : int
    {
    }

    /**
     * {@inheritdoc}
     */
    public function GetExternalAllocatedMemory() : int
    {
    }
}
