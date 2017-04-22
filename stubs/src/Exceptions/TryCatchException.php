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


namespace V8\Exceptions;


use V8\Context;
use V8\Isolate;
use V8\TryCatch;

class TryCatchException extends Exception
{
    /**
     * @var Isolate
     */
    private $isolate;
    /**
     * @var Context
     */
    private $context;
    /**
     * @var TryCatch
     */
    private $try_catch;

    public function __construct(Isolate $isolate, Context $context, TryCatch $try_catch)
    {
        $this->isolate   = $isolate;
        $this->context   = $context;
        $this->try_catch = $try_catch;
    }

    /**
     * @return Isolate
     */
    public function GetIsolate(): Isolate
    {
        return $this->isolate;
    }

    /**
     * @return Context
     */
    public function GetContext(): Context
    {
        return $this->context;
    }

    /**
     * @return TryCatch
     */
    public function GetTryCatch(): TryCatch
    {
        return $this->try_catch;
    }
}
