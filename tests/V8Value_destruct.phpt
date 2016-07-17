--TEST--
V8\Value (destruct)
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.tracking_dtors.php';

$isolate = new V8\Isolate();
$value = new \v8Tests\TrackingDtors\Value($isolate);


$helper->dump($value);

$value = null;

echo "Done here", PHP_EOL;
?>
--EXPECT--
object(v8Tests\TrackingDtors\Value)#3 (1) {
  ["isolate":"V8\Value":private]=>
  object(V8\Isolate)#2 (5) {
    ["snapshot":"V8\Isolate":private]=>
    NULL
    ["time_limit":"V8\Isolate":private]=>
    float(0)
    ["time_limit_hit":"V8\Isolate":private]=>
    bool(false)
    ["memory_limit":"V8\Isolate":private]=>
    int(0)
    ["memory_limit_hit":"V8\Isolate":private]=>
    bool(false)
  }
}
Value dies now!
Done here
