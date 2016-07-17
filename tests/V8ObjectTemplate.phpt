--TEST--
V8\ObjectTemplate
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php
/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

// Tests:

$isolate = new \V8\Isolate();

$value = new \V8\ObjectTemplate($isolate);

$helper->header('Object representation');
$helper->dump($value);
$helper->space();

$helper->assert('ObjectTemplate extends Template', $value instanceof \V8\Template);
$helper->line();

$helper->header('Accessors');
$helper->method_matches($value, 'GetIsolate', $isolate);
$helper->line();

$callback = function() {
  echo 'Should never be called', PHP_EOL;
};

$fnc = new \V8\FunctionTemplate($isolate, $callback);
$fnc->SetClassName(new \V8\StringValue($isolate, 'TestConstructor'));

$context = new \V8\Context($isolate);

$value = new \V8\ObjectTemplate($isolate, $fnc);
$instance = $value->NewInstance($context);

$helper->assert('ObjectTemplate instance has name from constructor', $instance->GetConstructorName()->Value() == 'TestConstructor');


?>
--EXPECT--
Object representation:
----------------------
object(V8\ObjectTemplate)#4 (1) {
  ["isolate":"V8\Template":private]=>
  object(V8\Isolate)#3 (5) {
    ["snapshot":"V8\Isolate":private]=>
    NULL
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


ObjectTemplate extends Template: ok

Accessors:
----------
V8\ObjectTemplate::GetIsolate() matches expected value

ObjectTemplate instance has name from constructor: ok
