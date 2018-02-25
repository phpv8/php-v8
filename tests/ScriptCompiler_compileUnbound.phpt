--TEST--
V8\ScriptCompiler::compileUnboundScript()
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
    $unbound        = V8\ScriptCompiler::compileUnboundScript($context, $source);
    $helper->assert('Compile script', $unbound instanceof \V8\UnboundScript);

    $source_string = new V8\StringValue($isolate, 'var i = 0; while (true) {i++;}');
    $source        = new \V8\ScriptCompiler\Source($source_string);
    $unbound        = V8\ScriptCompiler::compileUnboundScript($context, $source);
    $helper->assert('Compile script', $unbound instanceof \V8\UnboundScript);

    try {
        $source_string = new V8\StringValue($isolate, 'garbage garbage garbage');
        $source        = new \V8\ScriptCompiler\Source($source_string);
        $unbound        = V8\ScriptCompiler::compileUnboundScript($context, $source);
    } catch (\V8\Exceptions\TryCatchException $e) {
        $helper->exception_export($e);
        //$helper->dump($e->getTryCatch());
    }

    try {
        $origin = new \V8\ScriptOrigin('test-module.js', null, null, null, "", new \V8\ScriptOriginOptions(\V8\ScriptOriginOptions::IS_MODULE));

        $source_string = new V8\StringValue($isolate, '"test"');
        $source        = new \V8\ScriptCompiler\Source($source_string, $origin);
        $unbound        = V8\ScriptCompiler::compileUnboundScript($context, $source);
    } catch (\V8\Exceptions\Exception $e) {
        $helper->exception_export($e);
    }

    $helper->space();
}

{
    $helper->header('Testing');

    $source_string = new V8\StringValue($isolate, '"test " + status');
    $source = new \V8\ScriptCompiler\Source($source_string);
    $unbound = V8\ScriptCompiler::compileUnboundScript($context, $source);

    $context->globalObject()->set($context, new \V8\StringValue($isolate, 'status'), new \V8\StringValue($isolate, 'passed'));

    $context2 = new \V8\Context($isolate);
    $context2->globalObject()->set($context2, new \V8\StringValue($isolate, 'status'), new \V8\StringValue($isolate, 'passed for second context'));

    
    $helper->dump($unbound->bindToContext($context)->run($context)->value());
    $helper->dump($unbound->bindToContext($context2)->run($context)->value());
    $helper->dump($unbound->bindToContext($context2)->run($context2)->value());
    $helper->dump($unbound->bindToContext($context)->run($context2)->value());

    $helper->space();
}



?>
--EXPECT--
Compiling:
----------
Compile script: ok
Compile script: ok
V8\Exceptions\TryCatchException: SyntaxError: Unexpected identifier
V8\Exceptions\Exception: Unable to compile module as unbound script


Testing:
--------
string(11) "test passed"
string(30) "test passed for second context"
string(30) "test passed for second context"
string(11) "test passed"
