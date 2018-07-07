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
 * A String object (ECMA-262, 4.3.18).
 */
class StringObject extends ObjectValue
{
    /**
     * @param Context     $context
     * @param StringValue $value
     */
    public function __construct(Context $context, StringValue $value)
    {
        parent::__construct($context);
    }

    /**
     * @return StringValue
     */
    public function valueOf(): StringValue
    {
    }
}
