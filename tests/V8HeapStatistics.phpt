--TEST--
V8\HeapStatistics
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

$hs = new \V8\HeapStatistics(1, 2, 3, 4, 5, 6, 7, 8, true);

$helper->header('Object representation');
$helper->dump($hs);
$helper->line();

$helper->dump_object_methods($hs);

?>
--EXPECT--
Object representation:
----------------------
object(V8\HeapStatistics)#2 (9) {
  ["total_heap_size":"V8\HeapStatistics":private]=>
  float(1)
  ["total_heap_size_executable":"V8\HeapStatistics":private]=>
  float(2)
  ["total_physical_size":"V8\HeapStatistics":private]=>
  float(3)
  ["total_available_size":"V8\HeapStatistics":private]=>
  float(4)
  ["used_heap_size":"V8\HeapStatistics":private]=>
  float(5)
  ["heap_size_limit":"V8\HeapStatistics":private]=>
  float(6)
  ["malloced_memory":"V8\HeapStatistics":private]=>
  float(7)
  ["peak_malloced_memory":"V8\HeapStatistics":private]=>
  float(8)
  ["does_zap_garbage":"V8\HeapStatistics":private]=>
  bool(true)
}

V8\HeapStatistics->total_heap_size(): float(1)
V8\HeapStatistics->total_heap_size_executable(): float(2)
V8\HeapStatistics->total_physical_size(): float(3)
V8\HeapStatistics->total_available_size(): float(4)
V8\HeapStatistics->used_heap_size(): float(5)
V8\HeapStatistics->heap_size_limit(): float(6)
V8\HeapStatistics->malloced_memory(): float(7)
V8\HeapStatistics->peak_malloced_memory(): float(8)
V8\HeapStatistics->does_zap_garbage(): bool(true)
