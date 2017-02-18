--TEST--
V8\Isolate::IsDead()
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);


$isolate = new V8\Isolate();
$helper->method_export($isolate, 'IsDead');


?>
--EXPECT--
V8\Isolate->IsDead(): bool(false)
