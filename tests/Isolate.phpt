--TEST--
V8\Isolate
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--ENV--
HOME=/tmp/we-need-home-env-var-set-to-load-valgrindrc
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';

$isolate = new V8\Isolate();

$helper->header('Object representation');
$helper->dump($isolate);
$helper->line();

$helper->method_export($isolate, 'getHeapStatistics');

$isolate = null;

// EXPECTF: ---/float\(.+\)/
// EXPECTF: +++float(%f)
?>
--EXPECTF--
Object representation:
----------------------
object(V8\Isolate)#2 (0) {
}

V8\Isolate->getHeapStatistics():
    object(V8\HeapStatistics)#26 (9) {
      ["total_heap_size":"V8\HeapStatistics":private]=>
      float(%f)
      ["total_heap_size_executable":"V8\HeapStatistics":private]=>
      float(%f)
      ["total_physical_size":"V8\HeapStatistics":private]=>
      float(%f)
      ["total_available_size":"V8\HeapStatistics":private]=>
      float(%f)
      ["used_heap_size":"V8\HeapStatistics":private]=>
      float(%f)
      ["heap_size_limit":"V8\HeapStatistics":private]=>
      float(%f)
      ["malloced_memory":"V8\HeapStatistics":private]=>
      float(%f)
      ["peak_malloced_memory":"V8\HeapStatistics":private]=>
      float(%f)
      ["does_zap_garbage":"V8\HeapStatistics":private]=>
      bool(false)
    }
