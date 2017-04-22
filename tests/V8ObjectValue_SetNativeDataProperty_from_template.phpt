--TEST--
V8\ObjectValue::SetNativeDataProperty - when object created from template
--SKIPIF--
<?php if (!extension_loaded("v8")) {
    print "skip";
} ?>
--FILE--
<?php

// TODO: check object with callbacks persistance!

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';
require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

$isolate1         = new \V8\Isolate();

$prop_value = 'foo';

$getter_obj = function (\V8\NameValue $property, \V8\PropertyCallbackInfo $info) use (&$prop_value) {
    echo 'Userland native getter on property (obj) ', $property->ToString($info->GetContext())->Value(), ' called, value is ', $prop_value, PHP_EOL;

    $info->GetReturnValue()->Set(new \V8\StringValue($info->GetIsolate(), $prop_value));
};

$setter_obj = function (\V8\NameValue $property, \V8\Value $value, \V8\PropertyCallbackInfo $info) use (& $prop_value) {
    $val = $value->ToString($info->GetContext())->Value();
    echo 'Userland native setter on property (obj) ', $property->ToString($info->GetContext())->Value(), ' called with ', $val, ', value is ', $prop_value, PHP_EOL;

    $prop_value = $val;
};

$getter_tpl = function (\V8\NameValue $property, \V8\PropertyCallbackInfo $info) use (&$prop_value) {
    echo 'Userland native getter on property (tpl) ', $property->ToString($info->GetContext())->Value(), ' called, value is ', $prop_value, PHP_EOL;

    $info->GetReturnValue()->Set(new \V8\StringValue($info->GetIsolate(), $prop_value));
};

$setter_tpl = function (\V8\NameValue $property, \V8\Value $value, \V8\PropertyCallbackInfo $info) use (& $prop_value) {
    $val = $value->ToString($info->GetContext())->Value();
    echo 'Userland native setter on property (tpl) ', $property->ToString($info->GetContext())->Value(), ' called with ', $val, ', value is ', $prop_value, PHP_EOL;

    $prop_value = $val;
};
$context1 = new V8\Context($isolate1);

$tpl = new V8\ObjectTemplate($isolate1);
$tpl->SetNativeDataProperty(new \V8\StringValue($isolate1, 'test'), $getter_tpl, $setter_tpl);

$obj = new V8\ObjectValue($context1);
$obj->SetNativeDataProperty($context1, new \V8\StringValue($isolate1, 'test'), $getter_obj, $setter_obj);

$obj1 = $tpl->NewInstance($context1);
$obj1->SetNativeDataProperty($context1, new \V8\StringValue($isolate1, 'test'), $getter_obj, $setter_obj);

$obj2 = $tpl->NewInstance($context1);


$context1->GlobalObject()->Set($context1, new \V8\StringValue($isolate1, 'obj'), $obj);
$context1->GlobalObject()->Set($context1, new \V8\StringValue($isolate1, 'obj1'), $obj1);
$context1->GlobalObject()->Set($context1, new \V8\StringValue($isolate1, 'obj2'), $obj2);

$v8_helper->injectConsoleLog($context1);

$source1    = '
console.log(obj.test, "\n");
obj.test = "bar";
console.log(obj.test, "\n");

console.log(obj1.test, "\n");
obj1.test = "foo";
console.log(obj1.test, "\n");

console.log(obj2.test, "\n");
obj2.test = "bar";
console.log(obj2.test, "\n");

"Script done";
';
$file_name1 = 'test.js';


$script1 = new V8\Script($context1, new \V8\StringValue($isolate1, $source1), new \V8\ScriptOrigin($file_name1));

$v8_helper->CompileRun($context1, $source1);

?>
--EXPECT--
Userland native getter on property (obj) test called, value is foo
foo

Userland native setter on property (obj) test called with bar, value is foo
Userland native getter on property (obj) test called, value is bar
bar

Userland native getter on property (obj) test called, value is bar
bar

Userland native setter on property (obj) test called with foo, value is bar
Userland native getter on property (obj) test called, value is foo
foo

Userland native getter on property (tpl) test called, value is foo
foo

Userland native setter on property (tpl) test called with bar, value is foo
Userland native getter on property (tpl) test called, value is bar
bar
