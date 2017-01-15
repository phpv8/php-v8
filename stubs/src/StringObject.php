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
 * A String object (ECMA-262, 4.3.18).
 */
class StringObject extends ObjectValue
{
    /**
     * @param \V8\Context     $context
     * @param \V8\StringValue $value
     */
    public function __construct(Context $context, StringValue $value)
    {
        parent::__construct($context);
    }

    /**
     * @return \V8\StringValue
     */
    public function ValueOf()
    {
    }
}
