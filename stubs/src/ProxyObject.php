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


class ProxyObject extends ObjectValue
{
    /**
     * @param Context     $context
     * @param ObjectValue $target
     * @param ObjectValue $handler
     */
    public function __construct(Context $context, ObjectValue $target, ObjectValue $handler)
    {
    }

    /***
     * @return Value|ObjectValue|NullValue|null
     */
    public function getTarget(): ?ObjectValue
    {
    }

    /**
     * @return Value|ObjectValue|NullValue|null
     */
    public function getHandler(): ?Value
    {
    }

    /**
     * @return bool
     */
    public function isRevoked(): bool
    {
    }

    /**
     * @return void
     */
    public function revoke()
    {
    }
}
