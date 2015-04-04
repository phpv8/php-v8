--TEST--
v8\Value (destruct)
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

require '.tracking_dtors.php';

$isolate = new v8\Isolate();
$value = new \v8Tests\TrackingDtors\Value($isolate);


debug_zval_dump($value);

$value = null;

echo "Done here", PHP_EOL;
?>
--EXPECT--
object(v8Tests\TrackingDtors\Value)#2 (1) refcount(2){
  ["isolate":"v8\Value":private]=>
  object(v8\Isolate)#1 (1) refcount(2){
    ["snapshot":"v8\Isolate":private]=>
    NULL
  }
}
Value dies now!
Done here
