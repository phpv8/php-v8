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
$helper->assert('ObjectTemplate implements AdjustableExternalMemoryInterface', $value instanceof \V8\AdjustableExternalMemoryInterface);
$helper->line();

$helper->header('Accessors');
$helper->method_matches($value, 'getIsolate', $isolate);
$helper->line();

$callback = function() {
  echo 'Should never be called', PHP_EOL;
};

$fnc = new \V8\FunctionTemplate($isolate, $callback);
$fnc->setClassName(new \V8\StringValue($isolate, 'TestConstructor'));

$context = new \V8\Context($isolate);

$value = new \V8\ObjectTemplate($isolate, $fnc);
$instance = $value->newInstance($context);

$helper->assert('ObjectTemplate instance has name from constructor', $instance->getConstructorName()->value() == 'TestConstructor');


?>
--EXPECT--
Object representation:
----------------------
object(V8\ObjectTemplate)#4 (1) {
  ["isolate":"V8\Template":private]=>
  object(V8\Isolate)#3 (0) {
  }
}


ObjectTemplate extends Template: ok
ObjectTemplate implements AdjustableExternalMemoryInterface: ok

Accessors:
----------
V8\ObjectTemplate::getIsolate() matches expected value

ObjectTemplate instance has name from constructor: ok
