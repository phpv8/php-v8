--TEST--
V8\ConstructorBehavior
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

// Bootstraps:
$obj = new V8\ConstructorBehavior();

// Tests:

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

$helper->header('Object representation');
$helper->dump($obj);
$helper->space();


$helper->header('Class constants');
$helper->dump_object_constants($obj);
$helper->space();

?>
--EXPECT--
Object representation:
----------------------
object(V8\ConstructorBehavior)#1 (0) {
}


Class constants:
----------------
V8\ConstructorBehavior::kThrow = 0
V8\ConstructorBehavior::kAllow = 1
