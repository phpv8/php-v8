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


namespace Pinepain\V8\Tests\Perf;


use V8\BooleanValue;
use V8\Context;
use V8\FunctionObject;
use V8\Isolate;
use V8\NullValue;
use V8\NumberValue;
use V8\ObjectValue;
use V8\StringValue;
use V8\UndefinedValue;


/**
 * @Warmup(2)
 * @Revs(100)
 * @Iterations(10)
 * @BeforeMethods("init")
 */
class CreatePrimitiveValue
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
     * @var Context
     */
    private $context2;
    /**
     * @var FunctionObject
     */
    private $fnc2;

    /**
     * @var callable
     */
    private $callback;

    private $pairs = [];

    public function init()
    {
        $this->isolate = $isolate = new Isolate();
        $this->context = $context = new Context($isolate);

        $this->fnc = new FunctionObject($context, function () {
            if ($this->callback) {
                ($this->callback)();
            }
        });

        $this->context2 = $context2 = new Context($isolate);

        $this->fnc2 = new FunctionObject($context2, function () {
            if ($this->callback) {
                ($this->callback)();
            }
        });
    }

    public function benchOutsideContext()
    {
        $callback = $this->buildCallback();

        $callback();
        $this->fnc->call($this->context, $this->fnc);
    }

    public function benchOutsideContextWithWarm()
    {
        $callback = $this->buildCallback();

        $this->fnc->call($this->context, $this->fnc);
        $callback();
    }

    public function benchWithinContext()
    {
        $this->callback = $this->buildCallback();
        $this->fnc->call($this->context, $this->fnc);
    }

    public function benchWithinContext2()
    {
        $this->callback = $this->buildCallback();
        $this->fnc2->call($this->context2, $this->fnc2);
    }

    public function benchWithinIsolate()
    {
        $cb = $this->buildCallback();
        $this->fnc->call($this->context, $this->fnc);

        $this->isolate->within($cb);
    }

    public function benchWithinIsolateLight()
    {
        $cb = $this->buildCallback();

        $this->isolate->within($cb);
    }

    public function benchWithinContextNew()
    {
        $cb = $this->buildCallback();
        $this->fnc->call($this->context, $this->fnc);

        $this->context->within($cb);
    }

    public function benchWithinContextNewLight()
    {
        $cb = $this->buildCallback();

        $this->context->within($cb);
    }

    protected function buildCallback()
    {
        $callback = function ($isolate = null) {
            $isolate = $isolate ?: $this->isolate;

            $values =[];
            for ($i = 0; $i < 200; $i++) {
                $values[] = new UndefinedValue($isolate);
                $values[] = new NullValue($isolate);
                $values[] = new BooleanValue($isolate, true);
                $values[] = new NumberValue($isolate, 123.456);
                $values[] = new StringValue($isolate, 'foo');
            }
        };

        return $callback;
    }
}
