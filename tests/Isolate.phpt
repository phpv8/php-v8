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

$helper->header('Class constants');
$helper->dump_object_constants($isolate);
$helper->line();

$helper->method_export($isolate, 'getHeapStatistics');

$isolate->lowMemoryNotification();

$helper->line();

try {
    $isolate->memoryPressureNotification(-2);
} catch (\V8\Exceptions\ValueException $e) {
    $helper->exception_export($e);
}

$isolate->memoryPressureNotification(\V8\Isolate::MEMORY_PRESSURE_LEVEL_NONE);
$isolate->memoryPressureNotification(\V8\Isolate::MEMORY_PRESSURE_LEVEL_MODERATE);
$isolate->memoryPressureNotification(\V8\Isolate::MEMORY_PRESSURE_LEVEL_CRITICAL);
try {
    $isolate->memoryPressureNotification(42);
} catch (\V8\Exceptions\ValueException $e) {
    $helper->exception_export($e);
}

$helper->line();

try {
    $isolate->setRAILMode(-2);
} catch (\V8\Exceptions\ValueException $e) {
    $helper->exception_export($e);
}
$isolate->setRAILMode(\V8\RAILMode::PERFORMANCE_RESPONSE);
$isolate->setRAILMode(\V8\RAILMode::PERFORMANCE_ANIMATION);
$isolate->setRAILMode(\V8\RAILMode::PERFORMANCE_IDLE);
$isolate->setRAILMode(\V8\RAILMode::PERFORMANCE_LOAD);
try {
    $isolate->setRAILMode(42);
} catch (\V8\Exceptions\ValueException $e) {
    $helper->exception_export($e);
}


$isolate = null;

// EXPECTF: ---/float\(.+\)/
// EXPECTF: +++float(%f)
?>
--EXPECTF--
Object representation:
----------------------
object(V8\Isolate)#2 (0) {
}

Class constants:
----------------
V8\Isolate::MEMORY_PRESSURE_LEVEL_NONE = 0
V8\Isolate::MEMORY_PRESSURE_LEVEL_MODERATE = 1
V8\Isolate::MEMORY_PRESSURE_LEVEL_CRITICAL = 2

V8\Isolate->getHeapStatistics():
    object(V8\HeapStatistics)#28 (9) {
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

V8\Exceptions\ValueException: Invalid memory pressure level given. See V8\Isolate MEMORY_PRESSURE_LEVEL_* class constants for available levels.
V8\Exceptions\ValueException: Invalid memory pressure level given. See V8\Isolate MEMORY_PRESSURE_LEVEL_* class constants for available levels.

V8\Exceptions\ValueException: Invalid RAIL mode given. See V8\RAILMode class constants for available values.
V8\Exceptions\ValueException: Invalid RAIL mode given. See V8\RAILMode class constants for available values.
