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
 * Collection of V8 heap information.
 *
 * Instances of this class can be passed to v8::V8::HeapStatistics to
 * get heap statistics from V8.
 */
class  HeapStatistics
{
    /**
     * @var float
     */
    private $total_heap_size;
    /**
     * @var float
     */
    private $total_heap_size_executable;
    /**
     * @var float
     */
    private $total_physical_size;
    /**
     * @var float
     */
    private $total_available_size;
    /**
     * @var float
     */
    private $used_heap_size;
    /**
     * @var float
     */
    private $heap_size_limit;
    /**
     * @var float
     */
    private $malloced_memory;
    /**
     * @var float
     */
    private $peak_malloced_memory;
    /**
     * @var bool
     */
    private $does_zap_garbage;

    /**
     * @param float $total_heap_size
     * @param float $total_heap_size_executable
     * @param float $total_physical_size
     * @param float $total_available_size
     * @param float $used_heap_size
     * @param float $heap_size_limit
     * @param float $malloced_memory
     * @param bool  $does_zap_garbage
     */
    public function __construct(
        float $total_heap_size,
        float $total_heap_size_executable,
        float $total_physical_size,
        float $total_available_size,
        float $used_heap_size,
        float $heap_size_limit,
        float $malloced_memory,
        float $peak_malloced_memory,
        bool $does_zap_garbage
    ) {
        $this->total_heap_size = $total_heap_size;
        $this->total_heap_size_executable = $total_heap_size_executable;
        $this->total_physical_size = $total_physical_size;
        $this->total_available_size = $total_available_size;
        $this->used_heap_size = $used_heap_size;
        $this->heap_size_limit = $heap_size_limit;
        $this->malloced_memory = $malloced_memory;
        $this->peak_malloced_memory = $peak_malloced_memory;
        $this->does_zap_garbage = $does_zap_garbage;
    }

    public function total_heap_size() : float
    {
        return $this->total_heap_size;
    }

    public function total_heap_size_executable() : float
    {
        return $this->total_heap_size_executable;
    }

    public function total_physical_size() : float
    {
        return $this->total_physical_size;
    }

    public function total_available_size() : float
    {
        return $this->total_available_size;
    }

    public function used_heap_size() : float
    {
        return $this->used_heap_size;
    }

    public function heap_size_limit() : float
    {
        return $this->heap_size_limit;
    }

    public function malloced_memory() : float
    {
        return $this->malloced_memory;
    }

    public function peak_malloced_memory() : float
    {
        return $this->peak_malloced_memory;
    }

    public function does_zap_garbage() : bool
    {
        return $this->does_zap_garbage;
    }
}
