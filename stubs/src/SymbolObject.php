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


/**
 * A Symbol object (ECMA-262 edition 6).
 */
class SymbolObject extends ObjectValue
{
    /**
     * @param Context     $context
     * @param SymbolValue $value
     */
    public function __construct(Context $context, SymbolValue $value)
    {
        parent::__construct($context);
    }

    /**
     * @return SymbolValue
     */
    public function valueOf(): SymbolValue
    {
    }
}
