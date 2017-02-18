--TEST--
V8\Isolate - cyclic references should not cause memleak
--SKIPIF--
<?php if (!extension_loaded("v8")) { print "skip"; } ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

// Tests:

$isolate1 = new \V8\Isolate();
$global_template1 = new V8\ObjectTemplate($isolate1);

$func_tpl = new \V8\FunctionTemplate($isolate1, function () {});
$global_template1->Set(new \V8\StringValue($isolate1, 'func'), $func_tpl, \V8\PropertyAttribute::DontDelete);

$foo = new V8\ObjectTemplate($isolate1);;
$name = new V8\StringValue($isolate1, 'test');

$getter = function ($index) use (&$foo, &$name, $helper) {
    echo 'I am getter for ' . $index . ' !', PHP_EOL;

    $helper->value_export(func_get_args());
};

$handlers = new \V8\IndexedPropertyHandlerConfiguration($getter);
$getter = null;

$test_obj_tpl = new \V8\ObjectTemplate($isolate1);
$test_obj_tpl->SetHandlerForIndexedProperty($handlers);

$global_template1->Set(new \V8\StringValue($isolate1, 'test'), $test_obj_tpl);

$handlers = null;

$context1 = new \V8\Context($isolate1, $global_template1);

$obj = new V8\ObjectValue($context1);
$obj->SetAccessor($context1, new \V8\StringValue($isolate1, 'test'), function () use (&$isolate1, &$foo, &$name) {});

echo 'Done here for now', PHP_EOL;
?>
--EXPECT--
Done here for now
