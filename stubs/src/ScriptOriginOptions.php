<?php

/*
  +----------------------------------------------------------------------+
  | This file is part of the pinepain/php-v8 PHP extension.              |
  |                                                                      |
  | Copyright (c) 2015-2016 Bogdan Padalko <pinepain@gmail.com>          |
  |                                                                      |
  | Licensed under the MIT license: http://opensource.org/licenses/MIT   |
  |                                                                      |
  | For the full copyright and license information, please view the      |
  | LICENSE file that was distributed with this source or visit          |
  | http://opensource.org/licenses/MIT                                   |
  +----------------------------------------------------------------------+
*/


namespace V8;


/**
 * The optional attributes of ScriptOrigin.
 */
class ScriptOriginOptions
{
    /**
     * @var bool|bool
     */
    private $is_embedder_debug_script;
    /**
     * @var bool|bool
     */
    private $is_shared_cross_origin;
    /**
     * @var bool|bool
     */
    private $is_opaque;

    /**
     * @param bool $is_embedder_debug_script
     * @param bool $is_shared_cross_origin
     * @param bool $is_opaque
     */
    public function __construct(bool $is_embedder_debug_script = false,
                                bool $is_shared_cross_origin = false,
                                bool $is_opaque = false)
    {
        $this->is_embedder_debug_script = $is_embedder_debug_script;
        $this->is_shared_cross_origin = $is_shared_cross_origin;
        $this->is_opaque = $is_opaque;
    }

    public function IsEmbedderDebugScript() : bool
    {
        return $this->is_embedder_debug_script;
    }

    public function IsSharedCrossOrigin() : bool
    {
        return $this->is_shared_cross_origin;
    }

    public function IsOpaque() : bool
    {
        return $this->is_opaque;
    }
}
