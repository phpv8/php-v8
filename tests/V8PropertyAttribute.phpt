--TEST--
V8\PropertyAttribute
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

// Bootstraps:
$obj = new V8\PropertyAttribute();

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
object(V8\PropertyAttribute)#1 (0) {
}


Class constants:
----------------
V8\PropertyAttribute::None = 0
V8\PropertyAttribute::ReadOnly = 1
V8\PropertyAttribute::DontEnum = 2
V8\PropertyAttribute::DontDelete = 4
