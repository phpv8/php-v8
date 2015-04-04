--TEST--
v8\Data
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

// Bootstraps:

$isolate = new v8\Isolate();
$value = new v8\Data();

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

// Tests:

$helper->header('Object representation');
debug_zval_dump($value);
$helper->space();

?>
--EXPECT--
Object representation:
----------------------
object(v8\Data)#2 (0) refcount(2){
}
