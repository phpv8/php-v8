--TEST--
V8\ReturnValue - using in and outside context
--SKIPIF--
<?php if (!extension_loaded("v8")) {
    print "skip";
} ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);


$isolate = new V8\Isolate();
$context = new V8\Context($isolate);

$scalar = new V8\StringValue($isolate, "test");
$object = new V8\ObjectValue($context);

/** @var V8\ReturnValue $retval */
$retval = null;


$func = new V8\FunctionObject($context, function (\V8\FunctionCallbackInfo $info) use ($helper, $isolate, $context, &$retval) {
    $retval = $info->GetReturnValue();

    $helper->header('Object representation');
    $helper->dump($retval);
    $helper->space();

    $helper->assert('Return value object is in context', $retval->InContext());

    $helper->assert('Return value holds original isolate object', $retval->GetIsolate(), $isolate);
    $helper->assert('Return value holds original isolate object', $retval->GetContext(), $context);
    $helper->assert('Return value holds no value', $retval->Get()->IsUndefined());

    $retval->SetInteger(42);

    $helper->inline('Return value holds value', $retval->Get()->Value());
});

$context->GlobalObject()->Set($context, new \V8\StringValue($isolate, 'test'), $func);

$source = 'test(); "Script done";';
$file_name = 'test.js';


$script = new V8\Script($context, new \V8\StringValue($isolate, $source), new \V8\ScriptOrigin($file_name));

$script->Run($context);
$helper->space();

$helper->assert('Return value object is out of context', false === $retval->InContext());

try {
    $retval->Get();
} catch (Exception $e) {
    $helper->exception_export($e);
}

$helper->line();

$helper->header('Object representation (outside of context)');
$helper->dump($retval);
$helper->space();



?>
--EXPECT--
Object representation:
----------------------
object(V8\ReturnValue)#12 (2) {
  ["isolate":"V8\ReturnValue":private]=>
  object(V8\Isolate)#3 (4) {
    ["time_limit":"V8\Isolate":private]=>
    float(0)
    ["time_limit_hit":"V8\Isolate":private]=>
    bool(false)
    ["memory_limit":"V8\Isolate":private]=>
    int(0)
    ["memory_limit_hit":"V8\Isolate":private]=>
    bool(false)
  }
  ["context":"V8\ReturnValue":private]=>
  object(V8\Context)#4 (1) {
    ["isolate":"V8\Context":private]=>
    object(V8\Isolate)#3 (4) {
      ["time_limit":"V8\Isolate":private]=>
      float(0)
      ["time_limit_hit":"V8\Isolate":private]=>
      bool(false)
      ["memory_limit":"V8\Isolate":private]=>
      int(0)
      ["memory_limit_hit":"V8\Isolate":private]=>
      bool(false)
    }
  }
}


Return value object is in context: ok
Return value holds original isolate object: ok
Return value holds original isolate object: ok
Return value holds no value: ok
Return value holds value: 42


Return value object is out of context: ok
V8\Exceptions\Exception: Attempt to use return value out of calling function context

Object representation (outside of context):
-------------------------------------------
object(V8\ReturnValue)#12 (2) {
  ["isolate":"V8\ReturnValue":private]=>
  object(V8\Isolate)#3 (4) {
    ["time_limit":"V8\Isolate":private]=>
    float(0)
    ["time_limit_hit":"V8\Isolate":private]=>
    bool(false)
    ["memory_limit":"V8\Isolate":private]=>
    int(0)
    ["memory_limit_hit":"V8\Isolate":private]=>
    bool(false)
  }
  ["context":"V8\ReturnValue":private]=>
  object(V8\Context)#4 (1) {
    ["isolate":"V8\Context":private]=>
    object(V8\Isolate)#3 (4) {
      ["time_limit":"V8\Isolate":private]=>
      float(0)
      ["time_limit_hit":"V8\Isolate":private]=>
      bool(false)
      ["memory_limit":"V8\Isolate":private]=>
      int(0)
      ["memory_limit_hit":"V8\Isolate":private]=>
      bool(false)
    }
  }
}
