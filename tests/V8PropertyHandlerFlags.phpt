--TEST--
v8\PropertyHandlerFlags
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

// Bootstraps:
$obj = new v8\PropertyHandlerFlags();

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
object(v8\PropertyHandlerFlags)#1 (0) {
}


Class constants:
----------------
v8\PropertyHandlerFlags::kNone = 0
v8\PropertyHandlerFlags::kAllCanRead = 1
v8\PropertyHandlerFlags::kNonMasking = 2
v8\PropertyHandlerFlags::kOnlyInterceptStrings = 4
