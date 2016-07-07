--TEST--
v8\HeapStatistics
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

$hs = new \v8\HeapStatistics(1, 2, 3, 4, 5, 6, true);

$helper->header('Object representation');
$helper->dump($hs);
$helper->line();

$helper->dump_object_methods($hs);

?>
--EXPECT--
Object representation:
----------------------
object(v8\HeapStatistics)#2 (7) {
  ["total_heap_size":"v8\HeapStatistics":private]=>
  float(1)
  ["total_heap_size_executable":"v8\HeapStatistics":private]=>
  float(2)
  ["total_physical_size":"v8\HeapStatistics":private]=>
  float(3)
  ["total_available_size":"v8\HeapStatistics":private]=>
  float(4)
  ["used_heap_size":"v8\HeapStatistics":private]=>
  float(5)
  ["heap_size_limit":"v8\HeapStatistics":private]=>
  float(6)
  ["does_zap_garbage":"v8\HeapStatistics":private]=>
  bool(true)
}

v8\HeapStatistics->total_heap_size(): float(1)
v8\HeapStatistics->total_heap_size_executable(): float(2)
v8\HeapStatistics->total_physical_size(): float(3)
v8\HeapStatistics->total_available_size(): float(4)
v8\HeapStatistics->used_heap_size(): float(5)
v8\HeapStatistics->heap_size_limit(): float(6)
v8\HeapStatistics->does_zap_garbage(): bool(true)
