--TEST--
v8\Isolate
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';

$isolate = new v8\Isolate();

$helper->header('Object representation');
$helper->dump($isolate);
$helper->line();

try {
  $isolate->GetCurrentContext();
} catch (Exception $e) {
  $helper->exception_export($e);
}

$helper->line();
$helper->method_export($isolate, 'GetHeapStatistics');

$isolate = null;
?>
--EXPECTF--
Object representation:
----------------------
object(v8\Isolate)#2 (5) {
  ["snapshot":"v8\Isolate":private]=>
  NULL
  ["time_limit":"v8\Isolate":private]=>
  float(0)
  ["time_limit_hit":"v8\Isolate":private]=>
  bool(false)
  ["memory_limit":"v8\Isolate":private]=>
  int(0)
  ["memory_limit_hit":"v8\Isolate":private]=>
  bool(false)
}

v8\Exceptions\GenericException: Not in context!

v8\Isolate->GetHeapStatistics():
    object(v8\HeapStatistics)#28 (7) {
      ["total_heap_size":"v8\HeapStatistics":private]=>
      float(%d)
      ["total_heap_size_executable":"v8\HeapStatistics":private]=>
      float(%d)
      ["total_physical_size":"v8\HeapStatistics":private]=>
      float(%d)
      ["total_available_size":"v8\HeapStatistics":private]=>
      float(%d)
      ["used_heap_size":"v8\HeapStatistics":private]=>
      float(%d)
      ["heap_size_limit":"v8\HeapStatistics":private]=>
      float(%d)
      ["does_zap_garbage":"v8\HeapStatistics":private]=>
      bool(false)
    }
