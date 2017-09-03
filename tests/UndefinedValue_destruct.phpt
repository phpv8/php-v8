--TEST--
V8\UndefinedValue (destruct)
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--ENV--
HOME=/tmp/we-need-home-env-var-set-to-load-valgrindrc
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.tracking_dtors.php';

$isolate = new V8\Isolate();
$value = new \v8Tests\TrackingDtors\UndefinedValue($isolate);


$helper->dump($value);

$value = null;

echo "Done here", PHP_EOL;
?>
--EXPECT--
object(v8Tests\TrackingDtors\UndefinedValue)#3 (1) {
  ["isolate":"V8\Value":private]=>
  object(V8\Isolate)#2 (0) {
  }
}
UndefinedValue dies now!
Done here
