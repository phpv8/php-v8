--TEST--
v8\IndexedPropertyHandlerConfiguration
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php
/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

$getter = function () {};

$helper->header('Getter released');

debug_zval_dump($getter);

// Bootstraps:
$obj = new v8\IndexedPropertyHandlerConfiguration($getter);
debug_zval_dump($getter);
$helper->space();

// Tests:


$helper->header('Object representation');
debug_zval_dump($obj);
$helper->space();


$helper->header('Getter released');
debug_zval_dump($getter);
$obj = null;
debug_zval_dump($getter);

?>
--EXPECT--
Getter released:
----------------
object(Closure)#2 (0) refcount(2){
}
object(Closure)#2 (0) refcount(3){
}


Object representation:
----------------------
object(v8\IndexedPropertyHandlerConfiguration)#3 (0) refcount(2){
}


Getter released:
----------------
object(Closure)#2 (0) refcount(3){
}
object(Closure)#2 (0) refcount(2){
}