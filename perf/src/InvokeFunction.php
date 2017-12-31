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


namespace Pinepain\V8\Tests\Perf;


use V8\Context;
use V8\FunctionObject;
use V8\Isolate;


/**
 * @Warmup(2)
 * @Revs(100)
 * @Iterations(10)
 * @BeforeMethods("init")
 */
class InvokeFunction
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
     * @var FunctionObject
     */
    private $fnc;
    /**
     * @var callable
     */
    private $callback;

    private $stub_callback;

    public function init()
    {
        $this->isolate = $isolate = new Isolate();
        $this->context = $context = new Context($isolate);

        $this->fnc = new FunctionObject($context, function () {
            ($this->callback)();
        });

        $inner = new FunctionObject($context, function () {
        });

        $this->callback = function () use ($context, $inner) {
            $inner->call($context, $inner);
        };

        $this->stub_callback = function () {
        };
    }

    public function benchOutsideContext()
    {
        $cb             = $this->callback;
        $this->callback = $this->stub_callback;

        $this->fnc->call($this->context, $this->fnc); // outer

        ($cb)(); // inner run
    }

    public function benchWithinContext()
    {
        $cb = $this->callback;

        $this->fnc->call($this->context, $this->fnc); // outer + inner

        $this->callback = $this->stub_callback;
    }
}
