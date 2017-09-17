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


class RegExpObject extends ObjectValue
{
    const FLAG_NONE        = 0;
    const FLAG_GLOBAL      = 1;
    const FLAG_IGNORE_CASE = 2;
    const FLAG_MULTILINE   = 4;
    const FLAG_STICKY      = 8;
    const FLAG_UNICODE     = 16;
    const FLAG_DOTALL      = 32;

    /**
     * @param \V8\Context $context
     * @param StringValue $pattern
     * @param int         $flags
     */
    public function __construct(Context $context, StringValue $pattern, int $flags = RegExpObject::FLAG_NONE)
    {
        parent::__construct($context);
    }

    /**
     * @return StringValue
     */
    public function getSource(): StringValue
    {
    }

    /**
     * @return int
     */
    public function getFlags(): int
    {
    }
}
