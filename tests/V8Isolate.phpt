--TEST--
V8\Isolate
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';

$isolate = new V8\Isolate();

$helper->header('Object representation');
$helper->dump($isolate);
$helper->line();

$helper->method_export($isolate, 'GetHeapStatistics');

$isolate = null;

// EXPECTF: ---/float\(.+\)"/
// EXPECTF: +++float(%f)
?>
--EXPECTF--
Object representation:
----------------------
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

V8\Isolate->GetHeapStatistics():
    object(V8\HeapStatistics)#26 (9) {
      ["total_heap_size":"V8\HeapStatistics":private]=>
      float(3244032)
      ["total_heap_size_executable":"V8\HeapStatistics":private]=>
      float(3145728)
      ["total_physical_size":"V8\HeapStatistics":private]=>
      float(1676112)
      ["total_available_size":"V8\HeapStatistics":private]=>
      float(1496291688)
      ["used_heap_size":"V8\HeapStatistics":private]=>
      float(1583440)
      ["heap_size_limit":"V8\HeapStatistics":private]=>
      float(1501560832)
      ["malloced_memory":"V8\HeapStatistics":private]=>
      float(8192)
      ["peak_malloced_memory":"V8\HeapStatistics":private]=>
      float(8192)
      ["does_zap_garbage":"V8\HeapStatistics":private]=>
      bool(false)
    }
