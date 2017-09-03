--TEST--
V8\NamedPropertyHandlerConfiguration
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--ENV--
HOME=/tmp/we-need-home-env-var-set-to-load-valgrindrc
--FILE--
<?php
/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

$getter = function () {};

$helper->header('Getter released');

$helper->dump($getter);

// Bootstraps:
$obj = new V8\NamedPropertyHandlerConfiguration($getter);
$helper->dump($getter);
$helper->space();

// Tests:


$helper->header('Object representation');
$helper->dump($obj);
$helper->space();


$helper->header('Getter released');
$helper->dump($getter);
$obj = null;
$helper->dump($getter);

?>
--EXPECT--
Getter released:
----------------
object(Closure)#2 (0) {
}
object(Closure)#2 (0) {
}


Object representation:
----------------------
object(V8\NamedPropertyHandlerConfiguration)#3 (0) {
}


Getter released:
----------------
object(Closure)#2 (0) {
}
object(Closure)#2 (0) {
}
