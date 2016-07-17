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
 * Collection of V8 heap information.
 *
 * Instances of this class can be passed to v8::V8::HeapStatistics to
 * get heap statistics from V8.
 */
class  HeapStatistics
{
    /**
     * @var int
     */
    private $total_heap_size;
    /**
     * @var int
     */
    private $total_heap_size_executable;
    /**
     * @var int
     */
    private $total_physical_size;
    /**
     * @var int
     */
    private $total_available_size;
    /**
     * @var int
     */
    private $used_heap_size;
    /**
     * @var int
     */
    private $heap_size_limit;
    /**
     * @var bool
     */
    private $does_zap_garbage;

    /**
     * @param int  $total_heap_size
     * @param int  $total_heap_size_executable
     * @param int  $total_physical_size
     * @param int  $total_available_size
     * @param int  $used_heap_size
     * @param int  $heap_size_limit
     * @param bool $does_zap_garbage
     */
    public function __construct(
        int $total_heap_size,
        int $total_heap_size_executable,
        int $total_physical_size,
        int $total_available_size,
        int $used_heap_size,
        int $heap_size_limit,
        bool $does_zap_garbage
    ) {
        $this->total_heap_size = $total_heap_size;
        $this->total_heap_size_executable = $total_heap_size_executable;
        $this->total_physical_size = $total_physical_size;
        $this->total_available_size = $total_available_size;
        $this->used_heap_size = $used_heap_size;
        $this->heap_size_limit = $heap_size_limit;
        $this->does_zap_garbage = $does_zap_garbage;
    }

    public function total_heap_size()
    {
        return $this->total_heap_size;
    }

    public function total_heap_size_executable()
    {
        return $this->total_heap_size_executable;
    }

    public function total_physical_size()
    {
        return $this->total_physical_size;
    }

    public function total_available_size()
    {
        return $this->total_available_size;
    }

    public function used_heap_size()
    {
        return $this->used_heap_size;
    }

    public function heap_size_limit()
    {
        return $this->heap_size_limit;
    }

    public function does_zap_garbage()
    {
        return $this->does_zap_garbage;
    }
}
