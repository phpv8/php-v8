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


namespace PhpV8\V8\Tests\Perf;


use V8\Context;
use V8\FunctionObject;
use V8\Isolate;
use V8\ObjectValue;
use V8\StringValue;


/**
 * @Warmup(2)
 * @Revs(100)
 * @Iterations(10)
 * @BeforeMethods("init")
 */
class SetObjectProperty
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
     * @var ObjectValue
     */
    private $obj;

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

        for ($i = 0; $i < 1000; $i++) {
            $this->pairs[] = [new StringValue($isolate, "key_{$i}"), new StringValue($isolate, "value_{$i}")];
        }

        $this->obj = new ObjectValue($context);

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


    protected function buildCallback()
    {
        $callback = function () {
            $context = $this->context;

            foreach ($this->pairs as $pair) {
                $this->obj->set($context, $pair[0], $pair[1]);
            }
        };

        return $callback;
    }
}
