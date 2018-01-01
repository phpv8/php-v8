--TEST--
V8\ObjectValue::isArgumentsObject()
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

$isolate = new \V8\Isolate();
$global_template = new V8\ObjectTemplate($isolate);
$context = new V8\Context($isolate, $global_template);

//THREADED_TEST(ArgumentsObject)

//  v8::Handle<Value> arguments_object = CompileRun("var out = 0; (function(){ out = arguments; })(1,2,3); out;");
$arguments_object = $v8_helper->CompileRun($context, "var out = 0; (function(){ out = arguments; })(1,2,3); out;");

//  CHECK(arguments_object->isArgumentsObject());
$v8_helper->CHECK($arguments_object->isArgumentsObject(), '$arguments_object->isArgumentsObject()');

//  v8::Handle<Value> array = CompileRun("[1,2,3]");
$array = $v8_helper->CompileRun($context, "[1,2,3]");

//  CHECK(!array->isArgumentsObject());
$v8_helper->CHECK(!$array->isArgumentsObject(), '!$array->isArgumentsObject()');

//  v8::Handle<Value> object = CompileRun("{a:42}");
$object = $v8_helper->CompileRun($context, "{a:42}");

//  CHECK(!object->isArgumentsObject());
$v8_helper->CHECK(!$object->isArgumentsObject(), '!$object->isArgumentsObject()');


?>
--EXPECT--
CHECK $arguments_object->isArgumentsObject(): OK
CHECK !$array->isArgumentsObject(): OK
CHECK !$object->isArgumentsObject(): OK
