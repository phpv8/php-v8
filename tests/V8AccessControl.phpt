--TEST--
V8\AccessControl
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

// Bootstraps:
$obj = new V8\AccessControl();

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
object(V8\AccessControl)#1 (0) {
}


Class constants:
----------------
V8\AccessControl::DEFAULT_ACCESS = 0
V8\AccessControl::ALL_CAN_READ = 1
V8\AccessControl::ALL_CAN_WRITE = 2
