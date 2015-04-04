--TEST--
v8\PropertyAttribute
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

// Bootstraps:
$obj = new v8\PropertyAttribute();

// Tests:

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

$helper->header('Object representation');
debug_zval_dump($obj);
$helper->space();


$helper->header('Class constants');
$helper->dump_object_constants($obj);
$helper->space();

?>
--EXPECT--
Object representation:
----------------------
object(v8\PropertyAttribute)#1 (0) refcount(2){
}


Class constants:
----------------
v8\PropertyAttribute::None = 0
v8\PropertyAttribute::ReadOnly = 1
v8\PropertyAttribute::DontEnum = 2
v8\PropertyAttribute::DontDelete = 4
