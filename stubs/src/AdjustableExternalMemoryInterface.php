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


interface AdjustableExternalMemoryInterface
{
    /**
     * Adjusts the amount of registered external memory. Used to give V8 an
     * indication of the amount of externally allocated memory that is kept alive
     * by JavaScript object. V8 uses this to decide when to perform global
     * garbage collections. Registering externally allocated memory will trigger
     * global garbage collections more often than it would otherwise in an attempt
     * to garbage collect the JavaScript objects that keep the externally
     * allocated memory alive.
     *
     * NOTE: this is non-standard method. It is adapted v8::Isolate::AdjustAmountOfExternalAllocatedMemory() method idea
     *       for PHP V8 ObjectValue, FunctionTemplate and ObjectTemplate objects. Such object may hold some external
     *       data associated with them and that data can be hold by V8 after PHP object death when object/template
     *       becomes weak. It is mainly useful to give a V8 a hint about that extra data, thought, it should be used
     *       carefully while large ExternalAllocatedMemory will lead to more frequent GC triggering, which hurts
     *       performance. The rule of thumb is not to use this method unless you fully understand what
     *       v8::Isolate::AdjustAmountOfExternalAllocatedMemory() does. There is an underlying php-v8 mechanism which
     *       will notify V8 about some basic memory hold by object/template which may be enough in most cases to keep
     *       balance between performance and resources usage.
     *
     * @param int $change_in_bytes the change in externally allocated memory that is kept alive by JavaScript objects.
     *
     * @return int the adjusted value.
     *
     * NOTE: returned adjusted value can't be less then zero.
     */
    public function adjustExternalAllocatedMemory(int $change_in_bytes): int;

    /**
     * Get external allocated memory hint value.
     *
     * @return int
     */
    public function getExternalAllocatedMemory(): int;
}
