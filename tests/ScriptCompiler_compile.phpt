--TEST--
V8\ScriptCompiler::compile()
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);


$isolate = new V8\Isolate();
$context = new V8\Context($isolate);

$cache_data = null;
{
    $helper->header('Compiling');

    $source_string = new V8\StringValue($isolate, '"test"');
    $source        = new \V8\ScriptCompiler\Source($source_string);
    $script        = V8\ScriptCompiler::compile($context, $source);
    $helper->assert('Compile script', $script instanceof \V8\Script);

    $source_string = new V8\StringValue($isolate, 'var i = 0; while (true) {i++;}');
    $source        = new \V8\ScriptCompiler\Source($source_string);
    $script        = V8\ScriptCompiler::compile($context, $source);
    $helper->assert('Compile script', $script instanceof \V8\Script);

    try {
        $source_string = new V8\StringValue($isolate, 'garbage garbage garbage');
        $source        = new \V8\ScriptCompiler\Source($source_string);
        $script        = V8\ScriptCompiler::compile($context, $source);
    } catch (\V8\Exceptions\TryCatchException $e) {
        $helper->exception_export($e);
        //$helper->dump($e->getTryCatch());
    }

    try {
        $origin = new \V8\ScriptOrigin('test-module.js', null, null, null, "", $options = new \V8\ScriptOriginOptions(\V8\ScriptOriginOptions::IS_MODULE));

        $source_string = new V8\StringValue($isolate, '"test"');
        $source        = new \V8\ScriptCompiler\Source($source_string, $origin);
        $script        = V8\ScriptCompiler::compile($context, $source);
    } catch (\V8\Exceptions\Exception $e) {
        $helper->exception_export($e);
    }

    $helper->space();
}

{
    $helper->header('Testing');

    $source_string = new V8\StringValue($isolate, '"test " + status');
    $source = new \V8\ScriptCompiler\Source($source_string);
    $script = V8\ScriptCompiler::compile($context, $source);

    $context->globalObject()->set($context, new \V8\StringValue($isolate, 'status'), new \V8\StringValue($isolate, 'passed'));
    $helper->dump($script->run($context)->value());

    $helper->space();
}

{
    $helper->header('Test cache when no cache set');

    $source_string = new V8\StringValue($isolate, '"test " + status');
    $source    = new \V8\ScriptCompiler\Source($source_string);
    $helper->assert('Source cache data is not set', $source->getCachedData() === null);
    try {
        $script = V8\ScriptCompiler::compile($context, $source, V8\ScriptCompiler::OPTION_CONSUME_PARSER_CACHE);
    } catch (\V8\Exceptions\Exception $e) {
        $helper->exception_export($e);
    }
}

{
    $helper->header('Test generating code cache');
    $source_string = new V8\StringValue($isolate, '"test " + status');
    $source = new \V8\ScriptCompiler\Source($source_string);
    $helper->assert('Source cache data is NULL', $source->getCachedData() === null);
    $script = V8\ScriptCompiler::compile($context, $source, V8\ScriptCompiler::OPTION_PRODUCE_CODE_CACHE);
    $helper->assert('Source cache data is update', $source->getCachedData() != null);
    $helper->assert('Source cache data is not rejected', $source->getCachedData()->isRejected() === false);

    $cache_data = $source->getCachedData();
    $helper->line();
}

{
    $helper->header('Test consuming code cache');

    $source = new \V8\ScriptCompiler\Source($source_string, null, $cache_data);
    $helper->assert('Source cache data is set', $source->getCachedData() != null);
    $script = V8\ScriptCompiler::compile($context, $source, V8\ScriptCompiler::OPTION_CONSUME_CODE_CACHE);
    $helper->assert('Source cache data is still set', $source->getCachedData() != null);
    $helper->assert('Source cache data is not rejected', $source->getCachedData()->isRejected() === false);

    $helper->line();
}

{
    $helper->header('Test generating full code cache');
    $source_string = new V8\StringValue($isolate, '"test " + status');
    $source = new \V8\ScriptCompiler\Source($source_string);
    $helper->assert('Source cache data is NULL', $source->getCachedData() === null);
    $script = V8\ScriptCompiler::compile($context, $source, V8\ScriptCompiler::OPTION_PRODUCE_FULL_CODE_CACHE);
    $helper->assert('Source cache data is update', $source->getCachedData() != null);
    $helper->assert('Source cache data is not rejected', $source->getCachedData()->isRejected() === false);

    $cache_data = $source->getCachedData();
    $helper->line();
}

{
    $helper->header('Test consuming full code cache');

    $source = new \V8\ScriptCompiler\Source($source_string, null, $cache_data);
    $helper->assert('Source cache data is set', $source->getCachedData() != null);
    $script = V8\ScriptCompiler::compile($context, $source, V8\ScriptCompiler::OPTION_CONSUME_CODE_CACHE);
    $helper->assert('Source cache data is still set', $source->getCachedData() != null);
    $helper->assert('Source cache data is not rejected', $source->getCachedData()->isRejected() === false);

    $helper->line();
}

{
    $helper->header('Test consuming code cache for wrong source');
    $source_string = new V8\StringValue($isolate, '"other " + status');
    $source = new \V8\ScriptCompiler\Source($source_string, null, $cache_data);
    $helper->assert('Source cache data is set', $source->getCachedData() != null);
    $script = V8\ScriptCompiler::compile($context, $source, V8\ScriptCompiler::OPTION_CONSUME_CODE_CACHE);
    $helper->assert('Source cache data is still set', $source->getCachedData() != null);
    $helper->assert('Source cache data is rejected', $source->getCachedData()->isRejected() === true);

    $helper->line();
}

{
    $helper->header('Test consuming code cache for source with different formatting');
    $source_string = new V8\StringValue($isolate, '   "test "   +   status');
    $source    = new \V8\ScriptCompiler\Source($source_string, null, $cache_data);
    $helper->assert('Source cache data is set', $source->getCachedData() != null);
    $script = V8\ScriptCompiler::compile($context, $source, V8\ScriptCompiler::OPTION_CONSUME_CODE_CACHE);
    $helper->assert('Source cache data is still set', $source->getCachedData() != null);
    $helper->assert('Source cache data is not rejected', $source->getCachedData()->isRejected() !== false);

    $helper->line();
}

{
    $helper->header('Test generating code cache when it already set');
    $source_string = new V8\StringValue($isolate, '"test " + status');
    $source    = new \V8\ScriptCompiler\Source($source_string, null, $cache_data);
    $helper->assert('Source cache data is set', $source->getCachedData() != null);
    $script = V8\ScriptCompiler::compile($context, $source, V8\ScriptCompiler::OPTION_PRODUCE_CODE_CACHE);
    $helper->assert('Source cache data is still set', $source->getCachedData() != null);
    $helper->assert('Source cache data is rejected', $source->getCachedData()->isRejected() === true);

    $helper->line();
}

{
    $helper->header('Test consuming code cache when requesting parser cache');

    $source_string = new V8\StringValue($isolate, '"test " + status');
    $source    = new \V8\ScriptCompiler\Source($source_string, null, $cache_data);
    $helper->assert('Source cache data is set', $source->getCachedData() != null);
    $script = V8\ScriptCompiler::compile($context, $source, V8\ScriptCompiler::OPTION_CONSUME_PARSER_CACHE);
    $helper->assert('Source cache data is still set', $source->getCachedData() != null);
    $helper->assert('Source cache data is not rejected', $source->getCachedData()->isRejected() !== true);

    $helper->line();
}

{
    $helper->header('Test parser cache generated not for for all code');

    $source_string = new V8\StringValue($isolate, '"test " + status');
    $source = new \V8\ScriptCompiler\Source($source_string);
    $helper->assert('Source cache data is NULL', $source->getCachedData() === null);
    $script = V8\ScriptCompiler::compile($context, $source, V8\ScriptCompiler::OPTION_PRODUCE_PARSER_CACHE);
    $helper->assert('Source cache data is NOT updated', $source->getCachedData() === null);

    $helper->line();
}

$parser_cache_src= 'function test(arg1, args) {
    if (arg1 > arg2) {
        return 1+1;
    } else {
        return arg1 + arg2;
    }
}';

{
    $helper->header('Test generating parser cache');

    $source_string = new V8\StringValue($isolate, $parser_cache_src);
    $source = new \V8\ScriptCompiler\Source($source_string);
    $helper->assert('Source cache data is NULL', $source->getCachedData() === null);
    $script = V8\ScriptCompiler::compile($context, $source, V8\ScriptCompiler::OPTION_PRODUCE_PARSER_CACHE);
    $helper->assert('Source cache data is update', $source->getCachedData() != null);
    $helper->assert('Source cache data is not rejected', $source->getCachedData()->isRejected() === false);

    $cache_data = $source->getCachedData();

    //$helper->dump($cache_data->getData());
    $helper->line();
}

{
    $helper->header('Test consuming parser cache');

    $source_string = new V8\StringValue($isolate, $parser_cache_src);
    $source = new \V8\ScriptCompiler\Source($source_string, null, $cache_data);
    $helper->assert('Source cache data is set', $source->getCachedData() != null);
    $script = V8\ScriptCompiler::compile($context, $source, V8\ScriptCompiler::OPTION_CONSUME_PARSER_CACHE);
    $helper->assert('Source cache data is still set', $source->getCachedData() != null);
    $helper->assert('Source cache data is not rejected', $source->getCachedData()->isRejected() === false);

    $helper->line();
}

{
    $helper->header('Test consuming parser cache on different source (function with the same name)');

    $bin = $cache_data->getData();
    $source_string = new V8\StringValue($isolate, 'function test() { return 1+1;}');
    $source = new \V8\ScriptCompiler\Source($source_string, null, $cache_data);
    $helper->assert('Source cache data is set', $source->getCachedData() != null);
    try {
        $script = V8\ScriptCompiler::compile($context, $source, V8\ScriptCompiler::OPTION_CONSUME_PARSER_CACHE);
    } catch (\V8\Exceptions\TryCatchException $e) {
        $helper->exception_export($e);
    }

    $helper->line();
}

{
    $helper->header('Test consuming parser cache on different source');

    $bin = $cache_data->getData();
    $source_string = new V8\StringValue($isolate, 'function test2() { return 1+1;}');
    $source = new \V8\ScriptCompiler\Source($source_string, null, $cache_data);
    $helper->assert('Source cache data is set', $source->getCachedData() != null);
    $script = V8\ScriptCompiler::compile($context, $source, V8\ScriptCompiler::OPTION_CONSUME_PARSER_CACHE);
    $helper->assert('Source cache data is still set', $source->getCachedData() != null);
    $helper->assert('Source cache data is rejected', $source->getCachedData()->isRejected() === true);
    $helper->assert('Source cache data is not changed', $source->getCachedData()->getData() === $bin);

    $helper->line();
}

{
    $helper->header('Test consuming parser cache on the same source with different formatting');

    $source_string = new V8\StringValue($isolate, '           ' . $parser_cache_src);
    $source = new \V8\ScriptCompiler\Source($source_string, null, $cache_data);
    $helper->assert('Source cache data is set', $source->getCachedData() != null);
    $script = V8\ScriptCompiler::compile($context, $source, V8\ScriptCompiler::OPTION_CONSUME_PARSER_CACHE);
    $helper->assert('Source cache data is still set', $source->getCachedData() != null);
    $helper->assert('Source cache data is rejected', $source->getCachedData()->isRejected() === true);

    $helper->line();
}

{
    $helper->header('Test consuming code cache when parser cache given');

    $source_string = new V8\StringValue($isolate, $parser_cache_src);
    $source = new \V8\ScriptCompiler\Source($source_string, null, $cache_data);
    $helper->assert('Source cache data is set', $source->getCachedData() != null);
    $script = V8\ScriptCompiler::compile($context, $source, V8\ScriptCompiler::OPTION_CONSUME_CODE_CACHE);
    $helper->assert('Source cache data is still set', $source->getCachedData() != null);
    $helper->assert('Source cache data is not rejected', $source->getCachedData()->isRejected() === false);

    $helper->line();
}



?>
--EXPECT--
Compiling:
----------
Compile script: ok
Compile script: ok
V8\Exceptions\TryCatchException: SyntaxError: Unexpected identifier
V8\Exceptions\Exception: Unable to compile module as script


Testing:
--------
string(11) "test passed"


Test cache when no cache set:
-----------------------------
Source cache data is not set: ok
V8\Exceptions\Exception: Unable to consume cache when it's not set
Test generating code cache:
---------------------------
Source cache data is NULL: ok
Source cache data is update: ok
Source cache data is not rejected: ok

Test consuming code cache:
--------------------------
Source cache data is set: ok
Source cache data is still set: ok
Source cache data is not rejected: ok

Test generating full code cache:
--------------------------------
Source cache data is NULL: ok
Source cache data is update: ok
Source cache data is not rejected: ok

Test consuming full code cache:
-------------------------------
Source cache data is set: ok
Source cache data is still set: ok
Source cache data is not rejected: ok

Test consuming code cache for wrong source:
-------------------------------------------
Source cache data is set: ok
Source cache data is still set: ok
Source cache data is rejected: ok

Test consuming code cache for source with different formatting:
---------------------------------------------------------------
Source cache data is set: ok
Source cache data is still set: ok
Source cache data is not rejected: ok

Test generating code cache when it already set:
-----------------------------------------------
Source cache data is set: ok
Source cache data is still set: ok
Source cache data is rejected: ok

Test consuming code cache when requesting parser cache:
-------------------------------------------------------
Source cache data is set: ok
Source cache data is still set: ok
Source cache data is not rejected: ok

Test parser cache generated not for for all code:
-------------------------------------------------
Source cache data is NULL: ok
Source cache data is NOT updated: ok

Test generating parser cache:
-----------------------------
Source cache data is NULL: ok
Source cache data is update: ok
Source cache data is not rejected: ok

Test consuming parser cache:
----------------------------
Source cache data is set: ok
Source cache data is still set: ok
Source cache data is not rejected: ok

Test consuming parser cache on different source (function with the same name):
------------------------------------------------------------------------------
Source cache data is set: ok
V8\Exceptions\TryCatchException: SyntaxError: Unexpected end of input

Test consuming parser cache on different source:
------------------------------------------------
Source cache data is set: ok
Source cache data is still set: ok
Source cache data is rejected: ok
Source cache data is not changed: ok

Test consuming parser cache on the same source with different formatting:
-------------------------------------------------------------------------
Source cache data is set: ok
Source cache data is still set: ok
Source cache data is rejected: ok

Test consuming code cache when parser cache given:
--------------------------------------------------
Source cache data is set: ok
Source cache data is still set: ok
Source cache data is not rejected: ok
