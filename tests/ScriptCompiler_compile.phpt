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
        $script = V8\ScriptCompiler::compile($context, $source, V8\ScriptCompiler::OPTION_CONSUME_CODE_CACHE);
    } catch (\V8\Exceptions\Exception $e) {
        $helper->exception_export($e);
    }
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
