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
use V8\Isolate;
use V8\ScriptCompiler;
use V8\StartupData;
use V8\StringValue;


/**
 * @Warmup(10)
 * @Revs(100)
 * @Iterations(25)
 * @BeforeMethods("init")
 */
class IsolateSnapshotAndScriptCaching
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
     * @var StartupData
     */
    private $startup_data;
    /**
     * @var ScriptCompiler\CachedData
     */
    private $cached_data;

    private $source = 'function test_snapshot() { return "hello, world";}';

    public function init()
    {
        $this->source       = file_get_contents(__DIR__ . '/base.js');
        $this->startup_data = StartupData::createFromSource($this->source);
        //file_put_contents(__DIR__ . '/base.js.cache', $this->startup_data->getData());

        $this->isolate = $isolate = new Isolate();
        $this->context = $context = new Context($isolate);

        $source_string = new StringValue($isolate, $this->source);
        $source        = new ScriptCompiler\Source($source_string);

        $unbound_script = ScriptCompiler::compileUnboundScript($context, $source);

        $this->cached_data = ScriptCompiler::createCodeCache($unbound_script, $source_string);
    }

    public function benchColdIsolateNoScriptCache()
    {
        $isolate = new Isolate();
        $context = new Context($isolate);

        $source_string = new StringValue($isolate, $this->source);
        $source        = new ScriptCompiler\Source($source_string);
        ScriptCompiler::compile($context, $source)->run($context);
    }

    public function benchColdIsolateWithScriptCache()
    {
        $isolate = new Isolate();
        $context = new Context($isolate);

        $source_string = new StringValue($isolate, $this->source);
        $source        = new ScriptCompiler\Source($source_string, null, $this->cached_data);
        ScriptCompiler::compile($context, $source, ScriptCompiler::OPTION_CONSUME_CODE_CACHE)->run($context);
    }

    public function benchWarmIsolateNoScriptCache()
    {
        $isolate = new Isolate($this->startup_data);
        $context = new Context($isolate);

        $source_string = new StringValue($isolate, $this->source);
        $source        = new ScriptCompiler\Source($source_string);
        ScriptCompiler::compile($context, $source)->run($context);
    }

    public function benchWarmIsolateWithScriptCache()
    {
        $isolate = new Isolate($this->startup_data);
        $context = new Context($isolate);

        $source_string = new StringValue($isolate, $this->source);
        $source        = new ScriptCompiler\Source($source_string, null, $this->cached_data);
        ScriptCompiler::compile($context, $source, ScriptCompiler::OPTION_CONSUME_CODE_CACHE)->run($context);
    }
}
