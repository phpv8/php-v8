--TEST--
V8\ObjectValue::SetIntegrityLevel()
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

$isolate         = new \V8\Isolate();
$global_template = new V8\ObjectTemplate($isolate);

$context = new V8\Context($isolate, $global_template);
$v8_helper->injectConsoleLog($context);


$obj0 = new \V8\ObjectValue($context);
$obj0->Set($context, new \V8\StringValue($isolate, 'test'), new \V8\IntegerValue($isolate, 42));
$context->GlobalObject()->Set($context, new \V8\StringValue($isolate, 'obj0'), $obj0);

$obj1 = new \V8\ObjectValue($context);
$obj1->Set($context, new \V8\StringValue($isolate, 'test'), new \V8\IntegerValue($isolate, 42));
$obj1->SetIntegrityLevel($context, \V8\IntegrityLevel::kFrozen);
$context->GlobalObject()->Set($context, new \V8\StringValue($isolate, 'obj1'), $obj1);

$obj2 = new \V8\ObjectValue($context);
$obj2->Set($context, new \V8\StringValue($isolate, 'test'), new \V8\IntegerValue($isolate, 42));
$obj2->SetIntegrityLevel($context, \V8\IntegrityLevel::kSealed);
$context->GlobalObject()->Set($context, new \V8\StringValue($isolate, 'obj2'), $obj2);

$source    = '
console.log(Object.isFrozen(obj0));
console.log(Object.isSealed(obj0));
console.log(obj0.test);
console.log(obj0.test1);
obj0.test = "foo";
obj0.test1 = "bar";
console.log(obj0.test);
console.log(obj0.test1);
console.log();


console.log(Object.isFrozen(obj1));
console.log(Object.isSealed(obj1));
console.log(obj1.test);
console.log(obj1.test1);
obj1.test = "foo";
obj1.test1 = "bar";
console.log(obj1.test);
console.log(obj1.test1);
console.log();


console.log(Object.isFrozen(obj2));
console.log(Object.isSealed(obj2));
console.log(obj2.test);
console.log(obj2.test1);
obj2.test = "foo";
obj2.test1 = "bar";
console.log(obj2.test);
console.log(obj2.test1);
console.log();


';
$file_name = 'test.js';

$script = new V8\Script($context, new \V8\StringValue($isolate, $source), new \V8\ScriptOrigin($file_name));

$script->Run($context);

?>
--EXPECT--
false
false
42
<undefined>
foo
bar

true
true
42
<undefined>
42
<undefined>

false
true
42
<undefined>
foo
<undefined>
