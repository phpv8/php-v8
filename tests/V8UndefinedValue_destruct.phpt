--TEST--
V8\UndefinedValue (destruct)
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
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
  object(V8\Isolate)#2 (4) {
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
UndefinedValue dies now!
Done here
