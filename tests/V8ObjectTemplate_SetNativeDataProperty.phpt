--TEST--
V8\ObjectTemplate::SetNativeDataProperty
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

$getter = function (\V8\NameValue $property, \V8\PropertyCallbackInfo $info) use (&$prop_value) {
    echo 'Userland native getter on property ', $property->ToString($info->GetContext())->Value(), ' called, value is ', $prop_value, PHP_EOL;

    $info->GetReturnValue()->Set(new \V8\StringValue($info->GetIsolate(), $prop_value));
};

$setter = function (\V8\NameValue $property, \V8\Value $value, \V8\PropertyCallbackInfo $info) use (& $prop_value) {
    $val = $value->ToString($info->GetContext())->Value();
    echo 'Userland native setter on property ', $property->ToString($info->GetContext())->Value(), ' called with ', $val, ', value is ', $prop_value, PHP_EOL;

    $prop_value = $val;
};

$tpl = new V8\ObjectTemplate($isolate1);
$tpl->SetNativeDataProperty(new \V8\StringValue($isolate1, 'test'), $getter, $setter);


$context1 = new V8\Context($isolate1);
$context1->GlobalObject()->Set($context1, new \V8\StringValue($isolate1, 'obj'), $tpl->NewInstance($context1));

$v8_helper->injectConsoleLog($context1);

$source1    = '
console.log(obj.test, "\n");
obj.test = "bar";
console.log(obj.test, "\n");

"Script done";
';
$file_name1 = 'test.js';


$script1 = new V8\Script($context1, new \V8\StringValue($isolate1, $source1), new \V8\ScriptOrigin($file_name1));

$v8_helper->CompileRun($context1, $source1);

?>
--EXPECT--
Userland native getter on property test called, value is foo
foo

Userland native setter on property test called with bar, value is foo
Userland native getter on property test called, value is bar
bar
