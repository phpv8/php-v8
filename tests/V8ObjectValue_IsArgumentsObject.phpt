--TEST--
V8\ObjectValue::IsArgumentsObject()
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

$isolate1 = new \V8\Isolate();
$global_template1 = new V8\ObjectTemplate($isolate1);
$context1 = new V8\Context($isolate1, $global_template1);

//THREADED_TEST(ArgumentsObject)

//  v8::Handle<Value> arguments_object = CompileRun("var out = 0; (function(){ out = arguments; })(1,2,3); out;");
$arguments_object = $v8_helper->CompileRun($context1, "var out = 0; (function(){ out = arguments; })(1,2,3); out;");

//  CHECK(arguments_object->IsArgumentsObject());
$v8_helper->CHECK($arguments_object->IsArgumentsObject(), '$arguments_object->IsArgumentsObject()');

//  v8::Handle<Value> array = CompileRun("[1,2,3]");
$array = $v8_helper->CompileRun($context1, "[1,2,3]");

//  CHECK(!array->IsArgumentsObject());
$v8_helper->CHECK(!$array->IsArgumentsObject(), '!$array->IsArgumentsObject()');

//  v8::Handle<Value> object = CompileRun("{a:42}");
$object = $v8_helper->CompileRun($context1, "{a:42}");

//  CHECK(!object->IsArgumentsObject());
$v8_helper->CHECK(!$object->IsArgumentsObject(), '!$object->IsArgumentsObject()');


?>
--EXPECT--
CHECK $arguments_object->IsArgumentsObject(): OK
CHECK !$array->IsArgumentsObject(): OK
CHECK !$object->IsArgumentsObject(): OK
