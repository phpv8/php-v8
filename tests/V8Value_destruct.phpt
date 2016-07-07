--TEST--
v8\Value (destruct)
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.tracking_dtors.php';

$isolate = new v8\Isolate();
$value = new \v8Tests\TrackingDtors\Value($isolate);


$helper->dump($value);

$value = null;

echo "Done here", PHP_EOL;
?>
--EXPECT--
object(v8Tests\TrackingDtors\Value)#3 (1) {
  ["isolate":"v8\Value":private]=>
  object(v8\Isolate)#2 (1) {
    ["snapshot":"v8\Isolate":private]=>
    NULL
  }
}
Value dies now!
Done here
