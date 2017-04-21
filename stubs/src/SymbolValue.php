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
 * A JavaScript symbol (ECMA-262 edition 6)
 *
 * This is an experimental feature. Use at your own risk.
 */
class SymbolValue extends NameValue
{
    /**
     * Create a symbol. If name is not empty, it will be used as the description.
     *
     * @param Isolate     $isolate
     * @param StringValue $name
     */
    public function __construct(Isolate $isolate, StringValue $name = null)
    {
        parent::__construct($isolate);
    }

    /**
     * Returns the print name string of the symbol, or undefined if none.
     *
     * @return StringValue | Value
     */
    public function Name(): Value
    {
    }

    /**
     * Access global symbol registry.
     * Note that symbols created this way are never collected, so
     * they should only be used for statically fixed properties.
     * Also, there is only one global name space for the names used as keys.
     * To minimize the potential for clashes, use qualified names as keys.
     *
     * // NOTE: original v8::Symbol::For() accepts isolate instead of context
     *
     * @param Context     $context
     * @param StringValue $name
     *
     * @return SymbolValue
     */
    public static function For (Context $context, StringValue $name): SymbolValue
    {
    }

    /**
     * Retrieve a global symbol. Similar to |For|, but using a separate
     * registry that is not accessible by (and cannot clash with) JavaScript code.
     *
     * // NOTE: original v8::Symbol::ForApi() accepts isolate instead of context
     *
     * @param Context     $context
     * @param StringValue $name
     *
     * @return SymbolValue
     */
    public static function ForApi(Context $context, StringValue $name): SymbolValue
    {
    }

    /**
     * @param Isolate $isolate
     *
     * @return SymbolValue
     */
    public static function GetIterator(Isolate $isolate): SymbolValue
    {
    }

    /**
     * @param Isolate $isolate
     *
     * @return SymbolValue
     */
    public static function GetUnscopables(Isolate $isolate): SymbolValue
    {
    }

    /**
     * @param Isolate $isolate
     *
     * @return SymbolValue
     */
    public static function GetToPrimitive(Isolate $isolate): SymbolValue
    {
    }

    /**
     * @param Isolate $isolate
     *
     * @return SymbolValue
     */
    public static function GetToStringTag(Isolate $isolate): SymbolValue
    {
    }

    /**
     * @param Isolate $isolate
     *
     * @return SymbolValue
     */
    public static function GetIsConcatSpreadable(Isolate $isolate): SymbolValue
    {
    }
}
