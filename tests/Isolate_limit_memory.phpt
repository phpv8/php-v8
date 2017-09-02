--TEST--
V8\Isolate - memory limit
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
<?php
/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

if ($helper->need_more_time()) {
    echo 'skip Random bugs on travis at this time under valgrind';
}
?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

// Tests:

$isolate = new V8\Isolate();
$context = new V8\Context($isolate);
$v8_helper->injectConsoleLog($context);

$source    = '
    var str = " ".repeat(1024); // 1kb
    var blob = [];
    while(true) {
      blob.push(str);
      //console.log(blob.length);
    }
';
$file_name = 'test.js';

$script = new V8\Script($context, new \V8\StringValue($isolate, $source), new \V8\ScriptOrigin($file_name));

$memory_limit = 1024 * 1024 * 10;
$helper->assert('Memory limit accessor report no hit', false === $isolate->isMemoryLimitHit());
$helper->assert('Get memory limit default value is zero', 0 === $isolate->getMemoryLimit());
$isolate->setMemoryLimit($memory_limit);
$helper->assert('Get memory limit returns valid value', $memory_limit === $isolate->getMemoryLimit());

$helper->dump($isolate);
$helper->line();

try {
  $res = $script->run($context);
} catch(\V8\Exceptions\MemoryLimitException $e) {
  $helper->exception_export($e);
  echo 'script execution terminated', PHP_EOL;
}

$helper->assert('Get memory limit returns valid value', $memory_limit === $isolate->getMemoryLimit());
$helper->assert('Memory limit accessor report hit', true === $isolate->isMemoryLimitHit());


$helper->line();
$helper->dump($isolate);
$helper->dump($isolate->getHeapStatistics());

// EXPECTF: ---/float\(.+\)/
// EXPECTF: +++float(%f)
?>
--EXPECTF--
Memory limit accessor report no hit: ok
Get memory limit default value is zero: ok
Get memory limit returns valid value: ok
object(V8\Isolate)#3 (0) {
}

V8\Exceptions\MemoryLimitException: Memory limit exceeded
script execution terminated
Get memory limit returns valid value: ok
Memory limit accessor report hit: ok

object(V8\Isolate)#3 (0) {
}
object(V8\HeapStatistics)#10 (9) {
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
