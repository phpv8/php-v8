--TEST--
V8\Isolate - memory limit
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

// Tests:

$isolate = new V8\Isolate();
$extensions = [];
$global_template = new V8\ObjectTemplate($isolate);
$global_template->Set(new \V8\StringValue($isolate, 'print'), $v8_helper->getPrintFunctionTemplate($isolate), \V8\PropertyAttribute::DontDelete);

$context = new V8\Context($isolate, $extensions, $global_template);


$source    = '
    var str = " ".repeat(1024); // 1kb
    var blob = [];
    while(true) {
      blob.push(str);
      //print(blob.length, "\n");
    }
';
$file_name = 'test.js';

$script = new V8\Script($context, new \V8\StringValue($isolate, $source), new \V8\ScriptOrigin($file_name));

$memory_limit = 1024 * 1024 * 10;
$helper->assert('Memory limit accessor report no hit', false === $isolate->IsMemoryLimitHit());
$helper->assert('Get memory limit default value is zero', 0 === $isolate->GetMemoryLimit());
$isolate->SetMemoryLimit($memory_limit);
$helper->assert('Get memory limit returns valid value', $memory_limit === $isolate->GetMemoryLimit());

$helper->dump($isolate);
$helper->line();

try {
  $res = $script->Run($context);
} catch(\V8\Exceptions\MemoryLimitException $e) {
  $helper->exception_export($e);
  echo 'script execution terminated', PHP_EOL;
}

$helper->assert('Get memory limit returns valid value', $memory_limit === $isolate->GetMemoryLimit());
$helper->assert('Memory limit accessor report hit', true === $isolate->IsMemoryLimitHit());


$helper->line();
$helper->dump($isolate);
$helper->dump($isolate->GetHeapStatistics());
?>
--EXPECTF--
Memory limit accessor report no hit: ok
Get memory limit default value is zero: ok
Get memory limit returns valid value: ok
object(V8\Isolate)#3 (5) {
  ["snapshot":"V8\Isolate":private]=>
  NULL
  ["time_limit":"V8\Isolate":private]=>
  float(0)
  ["time_limit_hit":"V8\Isolate":private]=>
  bool(false)
  ["memory_limit":"V8\Isolate":private]=>
  int(10485760)
  ["memory_limit_hit":"V8\Isolate":private]=>
  bool(false)
}

V8\Exceptions\MemoryLimitException: Memory limit exceeded
script execution terminated
Get memory limit returns valid value: ok
Memory limit accessor report hit: ok

object(V8\Isolate)#3 (5) {
  ["snapshot":"V8\Isolate":private]=>
  NULL
  ["time_limit":"V8\Isolate":private]=>
  float(0)
  ["time_limit_hit":"V8\Isolate":private]=>
  bool(false)
  ["memory_limit":"V8\Isolate":private]=>
  int(10485760)
  ["memory_limit_hit":"V8\Isolate":private]=>
  bool(true)
}
object(V8\HeapStatistics)#14 (7) {
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
  ["does_zap_garbage":"V8\HeapStatistics":private]=>
  bool(false)
}
