--TEST--
V8\FunctionTemplate - callback weakness
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--ENV--
HOME=/tmp/we-need-home-env-var-set-to-load-valgrindrc
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);


$isolate = new V8\Isolate();

$f1 = function () {};
$f2 = function () {};

$helper->header('Functions before setting as a callback');
debug_zval_dump($f1);
debug_zval_dump($f2);

$fnc = new \V8\FunctionTemplate($isolate, $f1);

$helper->header('Functions after f1 was set as a callback');
debug_zval_dump($f1);
debug_zval_dump($f2);

$fnc->setCallHandler($f2);

$helper->header('Functions after f2 was set as a callback');
debug_zval_dump($f1);
debug_zval_dump($f2);

$fnc = null;
$helper->header('Functions after function template was destroyed');

debug_zval_dump($f1);
debug_zval_dump($f2);

$isolate = null;
//for($i = 0; $i<1000; $i++) {
//  $isolate->lowMemoryNotification();
//}
$helper->header('Functions after isolate was destroyed');

debug_zval_dump($f1);
debug_zval_dump($f2);


echo 'We are done for now', PHP_EOL;

?>
--EXPECT--
Functions before setting as a callback:
---------------------------------------
object(Closure)#4 (0) refcount(2){
}
object(Closure)#5 (0) refcount(2){
}
Functions after f1 was set as a callback:
-----------------------------------------
object(Closure)#4 (0) refcount(3){
}
object(Closure)#5 (0) refcount(2){
}
Functions after f2 was set as a callback:
-----------------------------------------
object(Closure)#4 (0) refcount(2){
}
object(Closure)#5 (0) refcount(3){
}
Functions after function template was destroyed:
------------------------------------------------
object(Closure)#4 (0) refcount(2){
}
object(Closure)#5 (0) refcount(3){
}
Functions after isolate was destroyed:
--------------------------------------
object(Closure)#4 (0) refcount(2){
}
object(Closure)#5 (0) refcount(2){
}
We are done for now
