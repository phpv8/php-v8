--TEST--
V8\ObjectValue::setNativeDataProperty - when object created from template
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

$isolate         = new \V8\Isolate();

$prop_value = 'foo';

$getter_obj = function (\V8\NameValue $property, \V8\PropertyCallbackInfo $info) use (&$prop_value) {
    echo 'Userland native getter on property (obj) ', $property->toString($info->getContext())->value(), ' called, value is ', $prop_value, PHP_EOL;

    $info->getReturnValue()->set(new \V8\StringValue($info->getIsolate(), $prop_value));
};

$setter_obj = function (\V8\NameValue $property, \V8\Value $value, \V8\PropertyCallbackInfo $info) use (& $prop_value) {
    $val = $value->toString($info->getContext())->value();
    echo 'Userland native setter on property (obj) ', $property->toString($info->getContext())->value(), ' called with ', $val, ', value is ', $prop_value, PHP_EOL;

    $prop_value = $val;
};

$getter_tpl = function (\V8\NameValue $property, \V8\PropertyCallbackInfo $info) use (&$prop_value) {
    echo 'Userland native getter on property (tpl) ', $property->toString($info->getContext())->value(), ' called, value is ', $prop_value, PHP_EOL;

    $info->getReturnValue()->set(new \V8\StringValue($info->getIsolate(), $prop_value));
};

$setter_tpl = function (\V8\NameValue $property, \V8\Value $value, \V8\PropertyCallbackInfo $info) use (& $prop_value) {
    $val = $value->toString($info->getContext())->value();
    echo 'Userland native setter on property (tpl) ', $property->toString($info->getContext())->value(), ' called with ', $val, ', value is ', $prop_value, PHP_EOL;

    $prop_value = $val;
};
$context = new V8\Context($isolate);

$tpl = new V8\ObjectTemplate($isolate);
$tpl->setNativeDataProperty(new \V8\StringValue($isolate, 'test'), $getter_tpl, $setter_tpl);

$obj = new V8\ObjectValue($context);
$obj->setNativeDataProperty($context, new \V8\StringValue($isolate, 'test'), $getter_obj, $setter_obj);

$obj1 = $tpl->newInstance($context);
$obj1->setNativeDataProperty($context, new \V8\StringValue($isolate, 'test'), $getter_obj, $setter_obj);

$obj2 = $tpl->newInstance($context);


$context->globalObject()->set($context, new \V8\StringValue($isolate, 'obj'), $obj);
$context->globalObject()->set($context, new \V8\StringValue($isolate, 'obj1'), $obj1);
$context->globalObject()->set($context, new \V8\StringValue($isolate, 'obj2'), $obj2);

$v8_helper->injectConsoleLog($context);

$source    = '
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
$file_name = 'test.js';


$script = new V8\Script($context, new \V8\StringValue($isolate, $source), new \V8\ScriptOrigin($file_name));

$v8_helper->CompileRun($context, $source);

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
