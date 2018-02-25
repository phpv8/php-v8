--TEST--
V8\ObjectValue::setLazyDataProperty()
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
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
    echo 'Userland lazy getter on property ', $property->toString($info->getContext())->value(), ' called, value is ', $prop_value, PHP_EOL;

    $info->getReturnValue()->set(new \V8\StringValue($info->getIsolate(), $prop_value));
};


$context = new V8\Context($isolate);

$obj = new V8\ObjectValue($context);
$obj->setLazyDataProperty($context, new \V8\StringValue($isolate, 'test'), $getter);

$context->globalObject()->set($context, new \V8\StringValue($isolate, 'obj'), $obj);

$v8_helper->injectConsoleLog($context);

$source    = '
console.log(obj.test);
obj.test = "bar";
console.log(obj.test);

"Script done";
';
$file_name = 'test.js';


$script = new V8\Script($context, new \V8\StringValue($isolate, $source), new \V8\ScriptOrigin($file_name));

$v8_helper->CompileRun($context, $source);

?>
--EXPECT--
Userland lazy getter on property test called, value is foo
foo
bar
