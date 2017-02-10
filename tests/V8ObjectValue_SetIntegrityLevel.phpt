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

$isolate1         = new \V8\Isolate();
$global_template1 = new V8\ObjectTemplate($isolate1);

$context1 = new V8\Context($isolate1, $global_template1);
$v8_helper->injectConsoleLog($context1);


$obj0 = new \V8\ObjectValue($context1);
$obj0->Set($context1, new \V8\StringValue($isolate1, 'test'), new \V8\IntegerValue($isolate1, 42));
$context1->GlobalObject()->Set($context1, new \V8\StringValue($isolate1, 'obj0'), $obj0);

$obj1 = new \V8\ObjectValue($context1);
$obj1->Set($context1, new \V8\StringValue($isolate1, 'test'), new \V8\IntegerValue($isolate1, 42));
$obj1->SetIntegrityLevel($context1, \V8\IntegrityLevel::kFrozen);
$context1->GlobalObject()->Set($context1, new \V8\StringValue($isolate1, 'obj1'), $obj1);

$obj2 = new \V8\ObjectValue($context1);
$obj2->Set($context1, new \V8\StringValue($isolate1, 'test'), new \V8\IntegerValue($isolate1, 42));
$obj2->SetIntegrityLevel($context1, \V8\IntegrityLevel::kSealed);
$context1->GlobalObject()->Set($context1, new \V8\StringValue($isolate1, 'obj2'), $obj2);

$source1    = '
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
$file_name1 = 'test.js';

$script1 = new V8\Script($context1, new \V8\StringValue($isolate1, $source1), new \V8\ScriptOrigin($file_name1));

$script1->Run($context1);

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
