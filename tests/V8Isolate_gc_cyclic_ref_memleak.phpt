--TEST--
v8\Isolate - cyclic references should not cause memleak
--SKIPIF--
<?php if (!extension_loaded("v8")) { print "skip"; } ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

// Tests:

$isolate1 = new \v8\Isolate();
$extensions1 = [];
$global_template1 = new v8\ObjectTemplate($isolate1);

$func_tpl = new \v8\FunctionTemplate($isolate1, function () {});
$global_template1->Set(new \v8\StringValue($isolate1, 'func'), $func_tpl, \v8\PropertyAttribute::DontDelete);

$foo = new v8\ObjectTemplate($isolate1);;
$name = new v8\StringValue($isolate1, 'test');

$getter = function ($index) use (&$foo, &$name, $helper) {
    echo 'I am getter for ' . $index . ' !', PHP_EOL;

    $helper->value_export(func_get_args());
};

$handlers = new \v8\IndexedPropertyHandlerConfiguration($getter);
$getter = null;

$test_obj_tpl = new \v8\ObjectTemplate($isolate1);
$test_obj_tpl->SetHandlerForIndexedProperty($handlers);

$global_template1->Set(new \v8\StringValue($isolate1, 'test'), $test_obj_tpl);

$handlers = null;

$context1 = new \v8\Context($isolate1, [], $global_template1);

$obj = new v8\ObjectValue($context1);
$obj->SetAccessor($context1, new \v8\StringValue($isolate1, 'test'), function () use (&$isolate1, &$foo, &$name) {});

echo 'Done here for now', PHP_EOL;
?>
--EXPECT--
Done here for now
