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

V8\Exceptions\GenericException: Not in context!

V8\Isolate->GetHeapStatistics():
    object(V8\HeapStatistics)#29 (9) {
      ["total_heap_size":"V8\HeapStatistics":private]=>
      float(%d)
      ["total_heap_size_executable":"V8\HeapStatistics":private]=>
      float(%d)
      ["total_physical_size":"V8\HeapStatistics":private]=>
      float(%d)
      ["total_available_size":"V8\HeapStatistics":private]=>
      float(%d)
      ["used_heap_size":"V8\HeapStatistics":private]=>
      float(%d)
      ["heap_size_limit":"V8\HeapStatistics":private]=>
      float(%d)
      ["malloced_memory":"V8\HeapStatistics":private]=>
      float(%d)
      ["peak_malloced_memory":"V8\HeapStatistics":private]=>
      float(%d)
      ["does_zap_garbage":"V8\HeapStatistics":private]=>
      bool(false)
    }
