--TEST--
V8\ScriptCompiler::compileFunctionInContext()
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


$helper->header('Compiling');

$source_string = new V8\StringValue($isolate, '"test"');
$source = new \V8\ScriptCompiler\Source($source_string);
$function = V8\ScriptCompiler::compileFunctionInContext($context, $source);
$helper->assert('Compile function', $function instanceof \V8\FunctionObject);

$source_string = new V8\StringValue($isolate, 'var i = 0; while (true) {i++;}');
$source = new \V8\ScriptCompiler\Source($source_string);
$function = V8\ScriptCompiler::compileFunctionInContext($context, $source);
$helper->assert('Compile function', $function instanceof \V8\FunctionObject);

$origin = new \V8\ScriptOrigin('test.js', null, null, null, "", new \V8\ScriptOriginOptions(\V8\ScriptOriginOptions::IS_SHARED_CROSS_ORIGIN));
$source_string = new V8\StringValue($isolate, '"test"');
$source = new \V8\ScriptCompiler\Source($source_string, $origin);
$function = V8\ScriptCompiler::compileFunctionInContext($context, $source);
$helper->assert('Compile shared cross origin script as function', $function instanceof \V8\FunctionObject);

$origin = new \V8\ScriptOrigin('test.js', null, null, null, "", new \V8\ScriptOriginOptions(\V8\ScriptOriginOptions::IS_OPAQUE));
$source_string = new V8\StringValue($isolate, '"test"');
$source = new \V8\ScriptCompiler\Source($source_string, $origin);
$function = V8\ScriptCompiler::compileFunctionInContext($context, $source);
$helper->assert('Compile opaque script as function', $function instanceof \V8\FunctionObject);

$origin = new \V8\ScriptOrigin('test.js', null, null, null, "", new \V8\ScriptOriginOptions(\V8\ScriptOriginOptions::IS_WASM));
$source_string = new V8\StringValue($isolate, '"test"');
$source = new \V8\ScriptCompiler\Source($source_string, $origin);
$function = V8\ScriptCompiler::compileFunctionInContext($context, $source);
$helper->assert('Compile wasm as function', $function instanceof \V8\FunctionObject);

try {
    $origin = new \V8\ScriptOrigin('test.js', null, null, null, "", new \V8\ScriptOriginOptions(\V8\ScriptOriginOptions::IS_MODULE));
    $source_string = new V8\StringValue($isolate, '"test"');
    $source = new \V8\ScriptCompiler\Source($source_string, $origin);
    $function = V8\ScriptCompiler::compileFunctionInContext($context, $source);
    $helper->assert('Compile module as function', $function instanceof \V8\FunctionObject);
} catch (\V8\Exceptions\ValueException $e) {
    $helper->exception_export($e);
}

try {
    $source_string = new V8\StringValue($isolate, 'garbage garbage garbage');
    $source = new \V8\ScriptCompiler\Source($source_string);
    $function = V8\ScriptCompiler::compileFunctionInContext($context, $source);
} catch (\V8\Exceptions\TryCatchException $e) {
    $helper->exception_export($e);
    //$helper->dump($e->getTryCatch());
}


$source_string = new V8\StringValue($isolate, 'test');
$source = new \V8\ScriptCompiler\Source($source_string);
$function = V8\ScriptCompiler::compileFunctionInContext($context, $source, [new V8\StringValue($isolate, 'test')]);
$helper->assert('Compile function', $function instanceof \V8\FunctionObject);

$source_string = new V8\StringValue($isolate, 'test');
$source = new \V8\ScriptCompiler\Source($source_string);
$function = V8\ScriptCompiler::compileFunctionInContext($context, $source, [new V8\StringValue($isolate, 'test')], [new \V8\ObjectValue($context)]);
$helper->assert('Compile function', $function instanceof \V8\FunctionObject);

$source_string = new V8\StringValue($isolate, '"test"');
$source = new \V8\ScriptCompiler\Source($source_string);
$function = V8\ScriptCompiler::compileFunctionInContext($context, $source, [], [new \V8\ObjectValue($context)]);
$helper->assert('Compile function', $function instanceof \V8\FunctionObject);

$source_string = new V8\StringValue($isolate, '"test"');
$source = new \V8\ScriptCompiler\Source($source_string);
$function = V8\ScriptCompiler::compileFunctionInContext($context, $source, [], [new V8\StringObject($context, new V8\StringValue($isolate, 'test'))]);
$helper->assert('Compile function', $function instanceof \V8\FunctionObject);


try {
    $source_string = new V8\StringValue($isolate, '"test"');
    $source = new \V8\ScriptCompiler\Source($source_string);
    $function = V8\ScriptCompiler::compileFunctionInContext($context, $source, [new V8\StringObject($context, new V8\StringValue($isolate, 'test'))]);
    $helper->assert('Compile function', $function instanceof \V8\FunctionObject);
} catch (TypeError $e) {
    $helper->exception_export($e);
}

try {
    $source_string = new V8\StringValue($isolate, '"test"');
    $source = new \V8\ScriptCompiler\Source($source_string);
    $function = V8\ScriptCompiler::compileFunctionInContext($context, $source, [], [new V8\StringValue($isolate, 'test')]);
    $helper->assert('Compile function', $function instanceof \V8\FunctionObject);
} catch (TypeError $e) {
    $helper->exception_export($e);
}

$helper->space();


$helper->header('Testing');

$source_string = new V8\StringValue($isolate, 'return "test";');
$source = new \V8\ScriptCompiler\Source($source_string);
$function = V8\ScriptCompiler::compileFunctionInContext($context, $source);

$context->globalObject()->set($context, new \V8\StringValue($isolate, 'test_simple'), $function);

$helper->dump($v8_helper->CompileRun($context, 'test_simple("passed")')->value());


$source_string = new V8\StringValue($isolate, 'return "test " + status;');
$source = new \V8\ScriptCompiler\Source($source_string);
$function = V8\ScriptCompiler::compileFunctionInContext($context, $source, [new \V8\StringValue($isolate, 'status')]);

$context->globalObject()->set($context, new \V8\StringValue($isolate, 'test_with_parameter'), $function);

$helper->dump($v8_helper->CompileRun($context, 'test_with_parameter("passed")')->value());


$ctx_a = new \V8\ObjectValue($context);
$ctx_a->set($context, new \V8\StringValue($isolate, 'foo'), new \V8\StringValue($isolate, 'foo from A'));
$ctx_a->set($context, new \V8\StringValue($isolate, 'bar'), new \V8\StringValue($isolate, 'bar from A'));

$ctx_b = new \V8\ObjectValue($context);
$ctx_b->set($context, new \V8\StringValue($isolate, 'foo'), new \V8\StringValue($isolate, 'foo from B'));
$ctx_b->set($context, new \V8\StringValue($isolate, 'baz'), new \V8\StringValue($isolate, 'baz from B'));


$source_string = new V8\StringValue($isolate, 'return "test " + status + " " + foo + " " + bar + " " + baz;');
$source = new \V8\ScriptCompiler\Source($source_string);
$function = V8\ScriptCompiler::compileFunctionInContext($context, $source, [new \V8\StringValue($isolate, 'status')], [$ctx_a, $ctx_b]);

$context->globalObject()->set($context, new \V8\StringValue($isolate, 'test_with_parameter_and_contexts'), $function);
$helper->dump($v8_helper->CompileRun($context, 'test_with_parameter_and_contexts("passed")')->value());

?>
--EXPECT--
Compiling:
----------
Compile function: ok
Compile function: ok
Compile shared cross origin script as function: ok
Compile opaque script as function: ok
Compile wasm as function: ok
V8\Exceptions\ValueException: Compiling module as a function in context is not supported
V8\Exceptions\TryCatchException: SyntaxError: Unexpected identifier
Compile function: ok
Compile function: ok
Compile function: ok
Compile function: ok
TypeError: Argument 3 passed to V8\ScriptCompiler::compileFunctionInContext() must be an array of \V8\StringValue, instance of V8\StringObject given at 0 offset
TypeError: Argument 4 passed to V8\ScriptCompiler::compileFunctionInContext() must be an array of \V8\ObjectValue, instance of V8\StringValue given at 0 offset


Testing:
--------
string(4) "test"
string(11) "test passed"
string(44) "test passed foo from B bar from A baz from B"
