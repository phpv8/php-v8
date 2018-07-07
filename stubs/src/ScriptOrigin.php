<?php declare(strict_types=1);

/**
 * This file is part of the phpv8/php-v8 PHP extension.
 *
 * Copyright (c) 2015-2018 Bogdan Padalko <thepinepain@gmail.com>
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
     * @var int|null
     */
    private $resource_line_offset;
    /**
     * @var int|null
     */
    private $resource_column_offset;
    /**
     * @var int|null
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
     * @param string                   $resource_name
     * @param int|null                 $resource_line_offset
     * @param int|null                 $resource_column_offset
     * @param int|null                 $script_id
     * @param string                   $source_map_url
     * @param ScriptOriginOptions|null $options
     */
    public function __construct(
        string $resource_name = "",
        ?int $resource_line_offset = null,
        ?int $resource_column_offset = null,
        ?int $script_id = null,
        string $source_map_url = '',
        ScriptOriginOptions $options = null
    ) {
        $this->resource_name          = $resource_name;
        $this->resource_line_offset   = $resource_line_offset;
        $this->resource_column_offset = $resource_column_offset;
        $this->script_id              = $script_id;


        $this->options = $options ?: new ScriptOriginOptions();

        $this->source_map_url = $source_map_url;
    }

    public function resourceName(): string
    {
        return $this->resource_name;
    }

    /**
     * @return int|null
     */
    public function resourceLineOffset(): ?int
    {
        return $this->resource_line_offset;
    }

    /**
     * @return int|null
     */
    public function resourceColumnOffset(): ?int
    {
        return $this->resource_column_offset;
    }

    /**
     * @return int|null
     */
    public function scriptId(): ?int
    {
        return $this->script_id;
    }

    public function sourceMapUrl(): string
    {
        return $this->source_map_url;
    }

    public function options(): ScriptOriginOptions
    {
        return $this->options;
    }
}
