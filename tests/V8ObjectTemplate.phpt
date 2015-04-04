--TEST--
v8\ObjectTemplate
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php
/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

// Tests:

$isolate = new \v8\Isolate();

$value = new \v8\ObjectTemplate($isolate);

$helper->header('Object representation');
debug_zval_dump($value);
$helper->space();

$helper->assert('ObjectTemplate extends Template', $value instanceof \v8\Template);
$helper->line();

$helper->header('Accessors');
$helper->method_matches($value, 'GetIsolate', $isolate);
$helper->line();

$callback = function() {
  echo 'Should never be called', PHP_EOL;
};

$fnc = new \v8\FunctionTemplate($isolate, $callback);
$fnc->SetClassName(new \v8\StringValue($isolate, 'TestConstructor'));

$context = new \v8\Context($isolate);

$value = new \v8\ObjectTemplate($isolate, $fnc);
$instance = $value->NewInstance($context);

$helper->assert('ObjectTemplate instance has name from constructor', $instance->GetConstructorName()->Value() == 'TestConstructor');


?>
--EXPECT--
Object representation:
----------------------
object(v8\ObjectTemplate)#4 (1) refcount(2){
  ["isolate":"v8\Template":private]=>
  object(v8\Isolate)#3 (1) refcount(2){
    ["snapshot":"v8\Isolate":private]=>
    NULL
  }
}


ObjectTemplate extends Template: ok

Accessors:
----------
v8\ObjectTemplate::GetIsolate() matches expected value

ObjectTemplate instance has name from constructor: ok
