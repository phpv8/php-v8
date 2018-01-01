--TEST--
V8\Isolate::isDead()
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);


$isolate = new V8\Isolate();
$helper->method_export($isolate, 'isDead');


?>
--EXPECT--
V8\Isolate->isDead(): bool(false)
