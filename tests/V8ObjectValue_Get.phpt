--TEST--
V8\ObjectValue::Get
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

$isolate = new \V8\Isolate();
$extensions = [];
$global_template = new V8\ObjectTemplate($isolate);

$context = new V8\Context($isolate, $extensions, $global_template);

$fnc = new \V8\FunctionObject($context, function () {echo 'I am fun', PHP_EOL;});

$object = new V8\ObjectValue($context);
$object->Set($context, new \V8\StringValue($isolate, 'scalar'), new \V8\StringValue($isolate, 'test'));
$object->Set($context, new \V8\StringValue($isolate, 'func'), $fnc);


$helper->value_instanceof($object->Get($context, new \V8\StringValue($isolate, 'scalar')), '\V8\StringValue');
$helper->value_instanceof($object->Get($context, new \V8\StringValue($isolate, 'func')), '\V8\FunctionObject');

$f1 = $object->Get($context, new \V8\StringValue($isolate, 'func'));
$f2 = $object->Get($context, new \V8\StringValue($isolate, 'func'));

$helper->value_matches_with_no_output($fnc, $f1);
$helper->value_matches_with_no_output($f1, $f2);

?>
--EXPECT--
Value is instance of \V8\StringValue
Value is instance of \V8\FunctionObject
Expected value is identical to actual value
Expected value is identical to actual value
