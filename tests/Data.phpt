--TEST--
V8\Data
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--ENV--
HOME=/tmp/we-need-home-env-var-set-to-load-valgrindrc
--FILE--
<?php

// Bootstraps:

$isolate = new V8\Isolate();
$value = new V8\Data();

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

// Tests:

$helper->header('Object representation');
$helper->dump($value);
$helper->space();

?>
--EXPECT--
Object representation:
----------------------
object(V8\Data)#2 (0) {
}
