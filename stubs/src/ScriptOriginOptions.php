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
 * The optional attributes of ScriptOrigin.
 */
class ScriptOriginOptions
{
    /**
     * @var bool
     */
    private $is_shared_cross_origin;
    /**
     * @var bool
     */
    private $is_opaque;
    /**
     * @var bool
     */
    private $is_wasm;
    /**
     * @var bool
     */
    private $is_module;

    /**
     * @param bool $is_shared_cross_origin
     * @param bool $is_opaque
     * @param bool $is_wasm
     * @param bool $is_module
     */
    public function __construct(bool $is_shared_cross_origin = false,
                                bool $is_opaque = false,
                                bool $is_wasm = false,
                                bool $is_module = false
                                )
    {
        $this->is_shared_cross_origin = $is_shared_cross_origin;
        $this->is_opaque = $is_opaque;
        $this->is_wasm = $is_wasm;
        $this->is_module = $is_module;
    }

    /**
     * @return bool
     */
    public function IsSharedCrossOrigin() : bool
    {
        return $this->is_shared_cross_origin;
    }

    /**
     * @return bool
     */
    public function IsOpaque() : bool
    {
        return $this->is_opaque;
    }

    /**
     * @return bool
     */
    public function isIsWasm(): bool
    {
        return $this->is_wasm;
    }

    /**
     * @return bool
     */
    public function isIsModule(): bool
    {
        return $this->is_module;
    }
}
