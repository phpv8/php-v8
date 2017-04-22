--TEST--
V8\ObjectValue::IsNativeError()
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

$isolate = new \V8\Isolate();
$global_template = new V8\ObjectTemplate($isolate);
$context = new V8\Context($isolate, $global_template);

// THREADED_TEST(IsNativeError)

//v8::Handle<Value> syntax_error = CompileRun("var out = 0; try { eval(\"#\"); } catch(x) { out = x; } out; ");
$syntax_error = $v8_helper->CompileRun($context, "var out = 0; try { eval(\"#\"); } catch(x) { out = x; } out; ");

//  CHECK(syntax_error->IsNativeError());

$v8_helper->CHECK($syntax_error->IsNativeError(), '$syntax_error->IsNativeError()');

//  v8::Handle<Value> not_error = CompileRun("{a:42}");
$not_error = $v8_helper->CompileRun($context, "{a:42}");

//  CHECK(!not_error->IsNativeError());
$v8_helper->CHECK(!$not_error->IsNativeError(), '!$not_error->IsNativeError()');

//  v8::Handle<Value> not_object = CompileRun("42");
$not_object = $v8_helper->CompileRun($context, "42");

//  CHECK(!not_object->IsNativeError());
$v8_helper->CHECK(!$not_object->IsNativeError(), '!$not_object->IsNativeError()');



?>
--EXPECT--
CHECK $syntax_error->IsNativeError(): OK
CHECK !$not_error->IsNativeError(): OK
CHECK !$not_object->IsNativeError(): OK
