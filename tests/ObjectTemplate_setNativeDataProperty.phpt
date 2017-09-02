--TEST--
V8\ObjectTemplate::setNativeDataProperty()
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

$getter = function (\V8\NameValue $property, \V8\PropertyCallbackInfo $info) use (&$prop_value) {
    echo 'Userland native getter on property ', $property->toString($info->getContext())->value(), ' called, value is ', $prop_value, PHP_EOL;

    $info->getReturnValue()->set(new \V8\StringValue($info->getIsolate(), $prop_value));
};

$setter = function (\V8\NameValue $property, \V8\Value $value, \V8\PropertyCallbackInfo $info) use (& $prop_value) {
    $val = $value->toString($info->getContext())->value();
    echo 'Userland native setter on property ', $property->toString($info->getContext())->value(), ' called with ', $val, ', value is ', $prop_value, PHP_EOL;

    $prop_value = $val;
};

$tpl = new V8\ObjectTemplate($isolate);
$tpl->setNativeDataProperty(new \V8\StringValue($isolate, 'test'), $getter, $setter);


$context = new V8\Context($isolate);
$context->globalObject()->set($context, new \V8\StringValue($isolate, 'obj'), $tpl->newInstance($context));

$v8_helper->injectConsoleLog($context);

$source    = '
console.log(obj.test, "\n");
obj.test = "bar";
console.log(obj.test, "\n");

"Script done";
';
$file_name = 'test.js';


$script = new V8\Script($context, new \V8\StringValue($isolate, $source), new \V8\ScriptOrigin($file_name));

$v8_helper->CompileRun($context, $source);

?>
--EXPECT--
Userland native getter on property test called, value is foo
foo

Userland native setter on property test called with bar, value is foo
Userland native getter on property test called, value is bar
bar
