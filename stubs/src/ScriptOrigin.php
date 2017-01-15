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
 * The origin, within a file, of a script.
 */
class ScriptOrigin
{
    /**
     * @var string
     */
    private $resource_name;
    /**
     * @var int
     */
    private $resource_line_offset;
    /**
     * @var int
     */
    private $resource_column_offset;
    /**
     * @var int
     */
    private $script_id;
    /**
     * @var string
     */
    private $source_map_url;

    /**
     * @var ScriptOriginOptions
     */
    private $options;

    /**
     * @param string $resource_name
     * @param int    $resource_line_offset
     * @param int    $resource_column_offset
     * @param bool   $resource_is_shared_cross_origin
     * @param int    $script_id
     * @param string $source_map_url
     * @param bool   $resource_is_opaque
     */
    public function __construct(string $resource_name,
                                int $resource_line_offset = Message::kNoLineNumberInfo,
                                int $resource_column_offset = Message::kNoColumnInfo,
                                bool $resource_is_shared_cross_origin = false,
                                int $script_id = Message::kNoScriptIdInfo,
                                string $source_map_url = '',
                                bool $resource_is_opaque = false)
    {
        $this->resource_name = $resource_name;
        $this->resource_line_offset = $resource_line_offset;
        $this->resource_column_offset = $resource_column_offset;

        $this->options = new ScriptOriginOptions($resource_is_shared_cross_origin, $resource_is_opaque);

        $this->script_id = $script_id;
        $this->source_map_url = $source_map_url;
    }

    public function ResourceName() : int
    {
        return $this->resource_name;
    }

    public function ResourceLineOffset() : int
    {
        return $this->resource_line_offset;
    }

    public function ResourceColumnOffset() : int
    {
        return $this->resource_column_offset;
    }

    public function ScriptID() : int
    {
        return $this->script_id;
    }

    /**
     * @return string
     */
    public function SourceMapUrl() : string
    {
        return $this->source_map_url;
    }

    public function Options() : ScriptOriginOptions
    {
        return $this->options;
    }
}
