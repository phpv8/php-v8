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


class Template extends Data
{
    /**
     * @var \V8\Isolate
     */
    private $isolate;

    /**
     * @param Isolate $isolate
     */
    public function __construct(\V8\Isolate $isolate)
    {
        $this->isolate = $isolate;
    }

    /**
     * @return \V8\Isolate
     */
    public function GetIsolate() : Isolate
    {
        return $this->isolate;
    }

    /**
     * Adds a property to each instance created by this template.
     *
     * @param NameValue $name
     * @param \V8\Data  $value
     * @param int       $attributes One of \v8\PropertyAttribute constants
     */
    public function Set(NameValue $name, Data $value, int $attributes = PropertyAttribute::None)
    {
    }

    //public function void SetAccessorProperty(
    //      Local<Name> name,
    //   Local<FunctionTemplate> getter = Local<FunctionTemplate>(),
    //   Local<FunctionTemplate> setter = Local<FunctionTemplate>(),
    //   PropertyAttribute attribute = None,
    //   AccessControl settings = DEFAULT);
    /**
     * @param NameValue            $name
     * @param \V8\FunctionTemplate $getter
     * @param \V8\FunctionTemplate $setter
     * @param int                  $attribute
     * @param int                  $settings
     */
    public function SetAccessorProperty(
        NameValue $name,
        FunctionTemplate $getter = null,
        FunctionTemplate $setter = null,
        $attribute = PropertyAttribute::None,
        $settings = AccessControl::DEFAULT_ACCESS
    ) {
    }

    /**
     * Whenever the property with the given name is accessed on objects
     * created from this Template the getter and setter callbacks
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
     */
    //public function void SetNativeDataProperty(Local<String> name,
    //                           AccessorGetterCallback getter,
    //                           AccessorSetterCallback setter = 0,
    //                           // TODO(dcarney): gcc can't handle Local below
    //                           Handle<Value> data = Handle<Value>(),
    //                           PropertyAttribute attribute = None,
    //                           Local<AccessorSignature> signature =
    //      Local<AccessorSignature>(),
    //                           AccessControl settings = DEFAULT);

    /**
     * @param NameValue $name
     * @param callable  $getter Callable that will accept (string $property, PropertyCallbackInfo $info)
     * @param callable  $setter Callable that will accept (string $property, PropertyCallbackInfo $info)
     * @param int       $attribute
     * @param int       $settings
     */
    public function SetNativeDataProperty(
        NameValue $name,
        callable $getter,
        callable $setter = null,
        $attribute = PropertyAttribute::None,
        $settings = AccessControl::DEFAULT_ACCESS
    ) {
    }

    //public function void SetNativeDataProperty(Local<Name> name,
    //                           AccessorNameGetterCallback getter,
    //                           AccessorNameSetterCallback setter = 0,
    //                           // TODO(dcarney): gcc can't handle Local below
    //                           Handle<Value> data = Handle<Value>(),
    //                           PropertyAttribute attribute = None,
    //                           Local<AccessorSignature> signature =
    //      Local<AccessorSignature>(),
    //                           AccessControl settings = DEFAULT);


}
