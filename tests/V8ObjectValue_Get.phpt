--TEST--
v8\ObjectValue::Get
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

$isolate = new \v8\Isolate();
$extensions = [];
$global_template = new v8\ObjectTemplate($isolate);

$context = new v8\Context($isolate, $extensions, $global_template);

$fnc = new \v8\FunctionObject($context, function () {echo 'I am fun', PHP_EOL;});

$object = new v8\ObjectValue($context);
$object->Set($context, new \v8\StringValue($isolate, 'scalar'), new \v8\StringValue($isolate, 'test'));
$object->Set($context, new \v8\StringValue($isolate, 'func'), $fnc);


$helper->value_instanceof($object->Get($context, new \v8\StringValue($isolate, 'scalar')), '\v8\StringValue');
$helper->value_instanceof($object->Get($context, new \v8\StringValue($isolate, 'func')), '\v8\FunctionObject');

$f1 = $object->Get($context, new \v8\StringValue($isolate, 'func'));
$f2 = $object->Get($context, new \v8\StringValue($isolate, 'func'));

$helper->value_matches_with_no_output($fnc, $f1);
$helper->value_matches_with_no_output($f1, $f2);

?>
--EXPECT--
Value is instance of \v8\StringValue
Value is instance of \v8\FunctionObject
Expected value is identical to actual value
Expected value is identical to actual value
