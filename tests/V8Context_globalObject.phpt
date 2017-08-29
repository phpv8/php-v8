--TEST--
V8\Context::globalObject()
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);


require '.tracking_dtors.php';

$isolate = new \V8\Isolate();
$context = new \V8\Context($isolate);

$helper->method_matches_instanceof($context, 'globalObject', \V8\ObjectValue::class);

$global1 = $context->globalObject();
$global1->foo = 'bar';

$global2 = $context->globalObject();

$helper->assert('Global object on repeatable calls is the same', $global1 === $global2);
$helper->assert('Global object on repeatable calls holds extra props', $global1->foo === $global2->foo && $global2->foo === 'bar');

$context->detachGlobal();

$context2 = new \V8\Context($isolate, null, $global2);
$helper->method_matches_instanceof($context2, 'globalObject', \V8\ObjectValue::class);

echo 'Global object passed from one context to another is ', ($global1 === $global2 ? 'the same' : 'not the same'), PHP_EOL;



?>
--EXPECT--
V8\Context::globalObject() result is instance of V8\ObjectValue
Global object on repeatable calls is the same: ok
Global object on repeatable calls holds extra props: ok
V8\Context::globalObject() result is instance of V8\ObjectValue
Global object passed from one context to another is the same
