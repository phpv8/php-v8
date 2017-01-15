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


class RegExpObject extends ObjectValue
{

    /**
     * @param \V8\Context $context
     * @param StringValue $pattern
     * @param int         $flags
     */
    public function __construct(Context $context, StringValue $pattern, int $flags = RegExpObject\Flags::kNone)
    {
        parent::__construct($context);
    }

    /**
     * @return StringValue
     */
    public function GetSource() : StringValue
    {
    }

    /**
     * @return int
     */
    public function GetFlags() : int
    {
    }
}
