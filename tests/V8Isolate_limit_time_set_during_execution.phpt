--TEST--
V8\Isolate - time limit set during execution
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.tracking_dtors.php';

$isolate1 = new V8\Isolate();
$extensions1 = [];

$global_template1 = new V8\ObjectTemplate($isolate1);

$context1 = new V8\Context($isolate1, $extensions1, $global_template1);

if ($helper->need_more_time()) {
    // On travis when valgrind active it takes more time to complete all operations so we just increase initial limits
    $time_limit = 5.0;
    $low_range = $time_limit/2;
    $high_range = $time_limit*20;
} else {
    $time_limit = 1.5;
    $low_range = 1.45;
    $high_range = 1.65;
}

$func = new V8\FunctionObject($context1, function (\V8\FunctionCallbackInfo $info) use (&$helper, $time_limit) {
    $isolate = $info->GetIsolate();
    $isolate->SetTimeLimit($time_limit);
});


$context1->GlobalObject()->Set($context1, new \V8\StringValue($isolate1, 'test'), $func);

$source1 = '
test();
for(;;);
"Script done"';
$file_name1 = 'test.js';

$script1 = new V8\Script($context1, new \V8\StringValue($isolate1, $source1), new \V8\ScriptOrigin($file_name1));

$helper->dump($isolate1);
$helper->line();

$t = microtime(true);
try {
    $script1->Run($context1);
} catch(\V8\Exceptions\TimeLimitException $e) {
    $helper->exception_export($e);
    echo 'script execution terminated', PHP_EOL;
} finally {
    $helper->line();
    $t = microtime(true) - $t;
    $helper->dump(round($t, 9));
    $helper->assert("Script execution time is within specified range ({$low_range}, {$high_range})", $t >= $low_range && $t < $high_range);
}

$helper->line();
$helper->dump($isolate1);

?>
--EXPECTF--
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

V8\Exceptions\TimeLimitException: Time limit exceeded
script execution terminated

float(%f)
Script execution time is within specified range (%f, %f): ok

object(V8\Isolate)#2 (5) {
  ["snapshot":"V8\Isolate":private]=>
  NULL
  ["time_limit":"V8\Isolate":private]=>
  float(%f)
  ["time_limit_hit":"V8\Isolate":private]=>
  bool(true)
  ["memory_limit":"V8\Isolate":private]=>
  int(0)
  ["memory_limit_hit":"V8\Isolate":private]=>
  bool(false)
}
