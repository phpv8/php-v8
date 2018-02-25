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


namespace V8\PromiseObject;

use V8\Context;
use V8\PromiseObject;
use V8\Value;


class ResolverObject extends PromiseObject
{
    /**
     * @param Context $context
     */
    public function __construct(Context $context)
    {
    }

    /**
     * Resolve the promise with a given value. Ignored if the promise is no longer pending.
     *
     * @param Context $context
     * @param Value   $value
     */
    public function resolve(Context $context, Value $value)
    {
    }

    /**
     * Reject the promise with a given value. Ignored if the promise is no longer pending.
     *
     * @param Context $context
     * @param Value   $value
     */
    public function reject(Context $context, Value $value)
    {
    }
}
