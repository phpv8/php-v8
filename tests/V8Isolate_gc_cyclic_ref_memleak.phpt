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

$isolate = new \V8\Isolate();
$global_template = new V8\ObjectTemplate($isolate);

$func_tpl = new \V8\FunctionTemplate($isolate, function () {});
$global_template->Set(new \V8\StringValue($isolate, 'func'), $func_tpl, \V8\PropertyAttribute::DontDelete);

$foo = new V8\ObjectTemplate($isolate);;
$name = new V8\StringValue($isolate, 'test');

$getter = function ($index) use (&$foo, &$name, $helper) {
    echo 'I am getter for ' . $index . ' !', PHP_EOL;

    $helper->value_export(func_get_args());
};

$handlers = new \V8\IndexedPropertyHandlerConfiguration($getter);
$getter = null;

$test_obj_tpl = new \V8\ObjectTemplate($isolate);
$test_obj_tpl->SetHandlerForIndexedProperty($handlers);

$global_template->Set(new \V8\StringValue($isolate, 'test'), $test_obj_tpl);

$handlers = null;

$context = new \V8\Context($isolate, $global_template);

$obj = new V8\ObjectValue($context);
$obj->SetAccessor($context, new \V8\StringValue($isolate, 'test'), function () use (&$isolate, &$foo, &$name) {});

echo 'Done here for now', PHP_EOL;
?>
--EXPECT--
Done here for now
