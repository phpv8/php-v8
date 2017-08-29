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
 * An instance of the built-in Date constructor (ECMA-262, 15.9).
 */
class DateObject extends ObjectValue
{
    /**
     * @param \V8\Context $context
     * @param double      $value
     */
    public function __construct(Context $context, double $value)
    {
        parent::__construct($context);
    }

    /**
     * @return double
     */
    public function valueOf(): double
    {
    }

    /**
     * Notification that the embedder has changed the time zone,
     * daylight savings time, or other date / time configuration
     * parameters.  V8 keeps a cache of various values used for
     * date / time computation.  This notification will reset
     * those cached values for the current context so that date /
     * time configuration changes would be reflected in the Date
     * object.
     *
     * This API should not be called more than needed as it will
     * negatively impact the performance of date operations.
     *
     * @param \V8\Isolate $isolate
     */
    public static function dateTimeConfigurationChangeNotification(Isolate $isolate)
    {
    }
}
