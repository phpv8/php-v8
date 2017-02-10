--TEST--
V8\Context::GlobalObject()
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);


require '.tracking_dtors.php';

$isolate1 = new \V8\Isolate();
$context = new \V8\Context($isolate1);

$helper->method_matches_instanceof($context, 'GlobalObject', \V8\ObjectValue::class);

$global1 = $context->GlobalObject();
$global1->foo = 'bar';

$global2 = $context->GlobalObject();

$helper->assert('Global object on repeatable calls is the same', $global1 === $global2);
$helper->assert('Global object on repeatable calls holds extra props', $global1->foo === $global2->foo && $global2->foo === 'bar');

$context->DetachGlobal();

$context2 = new \V8\Context($isolate1, null, $global2);
$helper->method_matches_instanceof($context2, 'GlobalObject', \V8\ObjectValue::class);

echo 'Global object passed from one context to another is ', ($global1 === $global2 ? 'the same' : 'not the same'), PHP_EOL;



?>
--EXPECT--
V8\Context::GlobalObject() result is instance of V8\ObjectValue
Global object on repeatable calls is the same: ok
Global object on repeatable calls holds extra props: ok
V8\Context::GlobalObject() result is instance of V8\ObjectValue
Global object passed from one context to another is the same
