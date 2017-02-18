--TEST--
V8\ScriptCompiler::CompileUnboundScript
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
    $unbound        = V8\ScriptCompiler::CompileUnboundScript($context, $source);
    $helper->assert('Compile script', $unbound instanceof \V8\UnboundScript);

    $source_string = new V8\StringValue($isolate, 'var i = 0; while (true) {i++;}');
    $source        = new \V8\ScriptCompiler\Source($source_string);
    $unbound        = V8\ScriptCompiler::CompileUnboundScript($context, $source);
    $helper->assert('Compile script', $unbound instanceof \V8\UnboundScript);

    try {
        $source_string = new V8\StringValue($isolate, 'garbage garbage garbage');
        $source        = new \V8\ScriptCompiler\Source($source_string);
        $unbound        = V8\ScriptCompiler::CompileUnboundScript($context, $source);
    } catch (\V8\Exceptions\TryCatchException $e) {
        $helper->exception_export($e);
        //$helper->dump($e->GetTryCatch());
    }

    try {
        $origin = new \V8\ScriptOrigin('test-module.js', 0, 0, false, 0, "", false, false, true);

        $source_string = new V8\StringValue($isolate, '"test"');
        $source        = new \V8\ScriptCompiler\Source($source_string, $origin);
        $unbound        = V8\ScriptCompiler::CompileUnboundScript($context, $source);
    } catch (\V8\Exceptions\GenericException $e) {
        $helper->exception_export($e);
    }

    $helper->space();
}

{
    $helper->header('Testing');

    $source_string = new V8\StringValue($isolate, '"test " + status');
    $source = new \V8\ScriptCompiler\Source($source_string);
    $unbound = V8\ScriptCompiler::CompileUnboundScript($context, $source);

    $context->GlobalObject()->Set($context, new \V8\StringValue($isolate, 'status'), new \V8\StringValue($isolate, 'passed'));

    $context2 = new \V8\Context($isolate);
    $context2->GlobalObject()->Set($context2, new \V8\StringValue($isolate, 'status'), new \V8\StringValue($isolate, 'passed for second context'));

    
    $helper->dump($unbound->BindToContext($context)->Run($context)->Value());
    $helper->dump($unbound->BindToContext($context2)->Run($context)->Value());
    $helper->dump($unbound->BindToContext($context2)->Run($context2)->Value());
    $helper->dump($unbound->BindToContext($context)->Run($context2)->Value());

    $helper->space();
}

{
    $helper->header('Test cache when no cache set');

    $source_string = new V8\StringValue($isolate, '"test " + status');;
    $source    = new \V8\ScriptCompiler\Source($source_string);
    $helper->assert('Source cache data is not set', $source->GetCachedData() === null);
    try {
        $unbound = V8\ScriptCompiler::CompileUnboundScript($context, $source, V8\ScriptCompiler\CompileOptions::kConsumeParserCache);
    } catch (\V8\Exceptions\GenericException $e) {
        $helper->exception_export($e);
    }
}

{
    $helper->header('Test generating code cache');
    $source_string = new V8\StringValue($isolate, '"test " + status');;
    $source = new \V8\ScriptCompiler\Source($source_string);
    $helper->assert('Source cache data is NULL', $source->GetCachedData() === null);
    $unbound = V8\ScriptCompiler::CompileUnboundScript($context, $source, V8\ScriptCompiler\CompileOptions::kProduceCodeCache);
    $helper->assert('Source cache data is update', $source->GetCachedData() != null);
    $helper->assert('Source cache data is not rejected', $source->GetCachedData()->isRejected() === false);

    $cache_data = $source->GetCachedData();
    $helper->line();
}

{
    $helper->header('Test consuming code cache');

    $source = new \V8\ScriptCompiler\Source($source_string, null, $cache_data);
    $helper->assert('Source cache data is set', $source->GetCachedData() != null);
    $unbound = V8\ScriptCompiler::CompileUnboundScript($context, $source, V8\ScriptCompiler\CompileOptions::kConsumeCodeCache);
    $helper->assert('Source cache data is still set', $source->GetCachedData() != null);
    $helper->assert('Source cache data is not rejected', $source->GetCachedData()->isRejected() === false);

    $helper->line();
}

{
    $helper->header('Test consuming code cache for wrong source');
    $source_string = new V8\StringValue($isolate, '"other " + status');;
    $source = new \V8\ScriptCompiler\Source($source_string, null, $cache_data);
    $helper->assert('Source cache data is set', $source->GetCachedData() != null);
    $unbound = V8\ScriptCompiler::CompileUnboundScript($context, $source, V8\ScriptCompiler\CompileOptions::kConsumeCodeCache);
    $helper->assert('Source cache data is still set', $source->GetCachedData() != null);
    $helper->assert('Source cache data is rejected', $source->GetCachedData()->isRejected() === true);

    $helper->line();
}

{
    $helper->header('Test consuming code cache for source with different formatting');
    $source_string = new V8\StringValue($isolate, '   "test "   +   status');;
    $source    = new \V8\ScriptCompiler\Source($source_string, null, $cache_data);
    $helper->assert('Source cache data is set', $source->GetCachedData() != null);
    $unbound = V8\ScriptCompiler::CompileUnboundScript($context, $source, V8\ScriptCompiler\CompileOptions::kConsumeCodeCache);
    $helper->assert('Source cache data is still set', $source->GetCachedData() != null);
    $helper->assert('Source cache data is not rejected', $source->GetCachedData()->isRejected() !== false);

    $helper->line();
}

{
    $helper->header('Test generating code cache when it already set');
    $source_string = new V8\StringValue($isolate, '"test " + status');;
    $source    = new \V8\ScriptCompiler\Source($source_string, null, $cache_data);
    $helper->assert('Source cache data is set', $source->GetCachedData() != null);
    $unbound = V8\ScriptCompiler::CompileUnboundScript($context, $source, V8\ScriptCompiler\CompileOptions::kProduceCodeCache);
    $helper->assert('Source cache data is still set', $source->GetCachedData() != null);
    $helper->assert('Source cache data is rejected', $source->GetCachedData()->isRejected() === true);

    $helper->line();
}

{
    $helper->header('Test consuming code cache when requesting parser cache');

    $source_string = new V8\StringValue($isolate, '"test " + status');;
    $source    = new \V8\ScriptCompiler\Source($source_string, null, $cache_data);
    $helper->assert('Source cache data is set', $source->GetCachedData() != null);
    $unbound = V8\ScriptCompiler::CompileUnboundScript($context, $source, V8\ScriptCompiler\CompileOptions::kConsumeParserCache);
    $helper->assert('Source cache data is still set', $source->GetCachedData() != null);
    $helper->assert('Source cache data is not rejected', $source->GetCachedData()->isRejected() !== true);

    $helper->line();
}

{
    $helper->header('Test parser cache genereted not for for all code');

    $source_string = new V8\StringValue($isolate, '"test " + status');;
    $source = new \V8\ScriptCompiler\Source($source_string);
    $helper->assert('Source cache data is NULL', $source->GetCachedData() === null);
    $unbound = V8\ScriptCompiler::CompileUnboundScript($context, $source, V8\ScriptCompiler\CompileOptions::kProduceParserCache);
    $helper->assert('Source cache data is NOT updated', $source->GetCachedData() === null);

    $helper->line();
}

{
    $helper->header('Test generating parser cache');

    $source_string = new V8\StringValue($isolate, 'function test() { return 1+1;}');;
    $source = new \V8\ScriptCompiler\Source($source_string);
    $helper->assert('Source cache data is NULL', $source->GetCachedData() === null);
    $unbound = V8\ScriptCompiler::CompileUnboundScript($context, $source, V8\ScriptCompiler\CompileOptions::kProduceParserCache);
    $helper->assert('Source cache data is update', $source->GetCachedData() != null);
    $helper->assert('Source cache data is not rejected', $source->GetCachedData()->isRejected() === false);

    $cache_data = $source->GetCachedData();
    $helper->line();
}


{
    $helper->header('Test consuming parser cache');

    $source_string = new V8\StringValue($isolate, 'function test() { return 1+1;}');;
    $source = new \V8\ScriptCompiler\Source($source_string, null, $cache_data);
    $helper->assert('Source cache data is set', $source->GetCachedData() != null);
    $unbound = V8\ScriptCompiler::CompileUnboundScript($context, $source, V8\ScriptCompiler\CompileOptions::kConsumeParserCache);
    $helper->assert('Source cache data is still set', $source->GetCachedData() != null);
    $helper->assert('Source cache data is not rejected', $source->GetCachedData()->isRejected() === false);

    $helper->line();
}

{
    $helper->header('Test consuming code cache when parser cache given');

    $source_string = new V8\StringValue($isolate, 'function test() { return 1+1;}');;
    $source = new \V8\ScriptCompiler\Source($source_string, null, $cache_data);
    $helper->assert('Source cache data is set', $source->GetCachedData() != null);
    $unbound = V8\ScriptCompiler::CompileUnboundScript($context, $source, V8\ScriptCompiler\CompileOptions::kConsumeCodeCache);
    $helper->assert('Source cache data is still set', $source->GetCachedData() != null);
    $helper->assert('Source cache data is not rejected', $source->GetCachedData()->isRejected() === false);

    $helper->line();
}



?>
--EXPECT--
Compiling:
----------
Compile script: ok
Compile script: ok
V8\Exceptions\TryCatchException: SyntaxError: Unexpected identifier
V8\Exceptions\GenericException: Unable to compile module as unbound script


Testing:
--------
string(11) "test passed"
string(30) "test passed for second context"
string(30) "test passed for second context"
string(11) "test passed"


Test cache when no cache set:
-----------------------------
Source cache data is not set: ok
V8\Exceptions\GenericException: Unable to consume cache when it's not set
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

Test parser cache genereted not for for all code:
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

Test consuming code cache when parser cache given:
--------------------------------------------------
Source cache data is set: ok
Source cache data is still set: ok
Source cache data is not rejected: ok
