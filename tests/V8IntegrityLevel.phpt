--TEST--
V8\PropertyAttribute
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

// Bootstraps:
$obj = new V8\IntegrityLevel();

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
object(V8\IntegrityLevel)#1 (0) {
}


Class constants:
----------------
V8\IntegrityLevel::kFrozen = 0
V8\IntegrityLevel::kSealed = 1
