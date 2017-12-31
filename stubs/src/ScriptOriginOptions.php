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
 * The optional attributes of ScriptOrigin.
 */
class ScriptOriginOptions
{
    const IS_SHARED_CROSS_ORIGIN = 1;
    const IS_OPAQUE              = 2;
    const IS_WASM                = 4;
    const IS_MODULE              = 8;

    /**
     * @var int
     */
    private $options;

    /**
     * @param int $options
     */
    public function __construct(int $options = 0)
    {
    }

    public function getOptions(): int
    {
    }

    /**
     * @return bool
     */
    public function isSharedCrossOrigin(): bool
    {
    }

    /**
     * @return bool
     */
    public function isOpaque(): bool
    {
    }

    /**
     * @return bool
     */
    public function isWasm(): bool
    {
    }

    /**
     * @return bool
     */
    public function isModule(): bool
    {
    }
}
