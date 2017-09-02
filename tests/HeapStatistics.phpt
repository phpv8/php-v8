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

V8\HeapStatistics->getTotalHeapSize(): float(1)
V8\HeapStatistics->getTotalHeapSizeExecutable(): float(2)
V8\HeapStatistics->getTotalPhysicalSize(): float(3)
V8\HeapStatistics->getTotalAvailableSize(): float(4)
V8\HeapStatistics->getUsedHeapSize(): float(5)
V8\HeapStatistics->getHeapSizeLimit(): float(6)
V8\HeapStatistics->getMallocedMemory(): float(7)
V8\HeapStatistics->getPeakMallocedMemory(): float(8)
V8\HeapStatistics->doesZapGarbage(): bool(true)
