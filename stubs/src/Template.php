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


class Template extends Data
{
    /**
     * @var Isolate
     */
    private $isolate;

    /**
     * @param Isolate $isolate
     */
    public function __construct(Isolate $isolate)
    {
        $this->isolate = $isolate;
    }

    /**
     * @return Isolate
     */
    public function getIsolate(): Isolate
    {
        return $this->isolate;
    }

    /**
     * Adds a property to each instance created by this template.
     *
     * @param NameValue                    $name
     * @param Data|PrimitiveValue|Template $value
     * @param int                          $attributes One of PropertyAttribute constants
     *
     * @return void
     */
    public function set(NameValue $name, Data $value, int $attributes = PropertyAttribute::NONE): void
    {
    }

    /**
     * @param NameValue        $name
     * @param FunctionTemplate $getter
     * @param FunctionTemplate $setter
     * @param int              $attribute
     * @param int              $settings
     *
     * @return void
     */
    public function setAccessorProperty(
        NameValue $name,
        FunctionTemplate $getter = null,
        FunctionTemplate $setter = null,
        $attribute = PropertyAttribute::NONE,
        $settings = AccessControl::DEFAULT_ACCESS
    ): void {
    }

    /**
     * Whenever the property with the given name is accessed on objects
     * created from this Template the getter and setter callbacks
     * are called instead of getting and setting the property directly
     * on the JavaScript object.
     *
     * @param NameValue        $name      The name of the property for which an accessor is added.
     *
     * @param callable         $getter    The callback to invoke when getting the property.
     *                                    Callback signature should be (NameValue $property, PropertyCallbackInfo $info)
     *
     * @param callable         $setter    The callback to invoke when setting the property.
     *                                    Callback signature should be (NameValue $property, PropertyCallbackInfo $info)
     *
     * @param int              $attribute The attributes of the property for which an accessor is added.
     *
     * @param FunctionTemplate $receiver  The signature describes valid receivers for the accessor
     *                                    and is used to perform implicit instance checks against them. If the
     *                                    receiver is incompatible (i.e. is not an instance of the constructor as
     *                                    defined by FunctionTemplate::HasInstance()), an implicit TypeError is
     *                                    thrown and no callback is invoked.
     *
     * @param int              $settings  Access control settings for the accessor.
     *
     * @return void
     */
    public function setNativeDataProperty(
        NameValue $name,
        callable $getter,
        callable $setter = null,
        $attribute = PropertyAttribute::NONE,
        FunctionTemplate $receiver = null,
        $settings = AccessControl::DEFAULT_ACCESS
    ): void {
    }
}
