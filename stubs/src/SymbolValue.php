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
    }

    /**
     * @return string
     */
    public function value(): string
    {
    }

    /**
     * Returns the print name string of the symbol, or undefined if none.
     *
     * @return Value|StringValue|UndefinedValue
     */
    public function name(): Value
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
    public static function createFor(Context $context, StringValue $name): SymbolValue
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
    public static function createForApi(Context $context, StringValue $name): SymbolValue
    {
    }

    // Well-known symbols

    /**
     * @param Isolate $isolate
     *
     * @return SymbolValue
     */
    public static function getHasInstanceSymbol(Isolate $isolate): SymbolValue
    {
    }

    /**
     * @param Isolate $isolate
     *
     * @return SymbolValue
     */
    public static function getIsConcatSpreadableSymbol(Isolate $isolate): SymbolValue
    {
    }

    /**
     * @param Isolate $isolate
     *
     * @return SymbolValue
     */
    public static function getIteratorSymbol(Isolate $isolate): SymbolValue
    {
    }

    /**
     * @param Isolate $isolate
     *
     * @return SymbolValue
     */
    public static function getMatchSymbol(Isolate $isolate): SymbolValue
    {
    }

    /**
     * @param Isolate $isolate
     *
     * @return SymbolValue
     */
    public static function getReplaceSymbol(Isolate $isolate): SymbolValue
    {
    }

    /**
     * @param Isolate $isolate
     *
     * @return SymbolValue
     */
    public static function getSearchSymbol(Isolate $isolate): SymbolValue
    {
    }

    /**
     * @param Isolate $isolate
     *
     * @return SymbolValue
     */
    public static function getSplitSymbol(Isolate $isolate): SymbolValue
    {
    }

    /**
     * @param Isolate $isolate
     *
     * @return SymbolValue
     */
    public static function getToPrimitiveSymbol(Isolate $isolate): SymbolValue
    {
    }

    /**
     * @param Isolate $isolate
     *
     * @return SymbolValue
     */
    public static function getToStringTagSymbol(Isolate $isolate): SymbolValue
    {
    }

    /**
     * @param Isolate $isolate
     *
     * @return SymbolValue
     */
    public static function getUnscopablesSymbol(Isolate $isolate): SymbolValue
    {
    }
}
