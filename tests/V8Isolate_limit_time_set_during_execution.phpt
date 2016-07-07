--TEST--
v8\Isolate - time limit set during execution
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.tracking_dtors.php';

$isolate1 = new v8\Isolate();
$extensions1 = [];

$global_template1 = new v8\ObjectTemplate($isolate1);

$context1 = new v8\Context($isolate1, $extensions1, $global_template1);

$func = new v8\FunctionObject($context1, function (\v8\FunctionCallbackInfo $info) use (&$helper) {
    $isolate = $info->GetIsolate();
    $isolate->SetTimeLimit(1.5);
});


$context1->GlobalObject()->Set($context1, new \v8\StringValue($isolate1, 'test'), $func);

$source1 = '
test();
for(;;);
"Script done"';
$file_name1 = 'test.js';

$script1 = new v8\Script($context1, new \v8\StringValue($isolate1, $source1), new \v8\ScriptOrigin($file_name1));

$helper->dump($isolate1);
$helper->line();

$t = microtime(true);
try {
    $script1->Run();
} catch(\v8\Exceptions\TimeLimitException $e) {
    $helper->exception_export($e);
    echo 'script execution terminated', PHP_EOL;
} finally {
    $helper->line();
    $t = microtime(true) - $t;
    $helper->dump(round($t, 9));
    $helper->assert('Script execution time is between 1.500 and 1.600', $t >= 1.500 && $t < 1.599);
}

$helper->line();
$helper->dump($isolate1);

?>
--EXPECTF--
object(v8\Isolate)#2 (5) {
  ["snapshot":"v8\Isolate":private]=>
  NULL
  ["time_limit":"v8\Isolate":private]=>
  float(0)
  ["time_limit_hit":"v8\Isolate":private]=>
  bool(false)
  ["memory_limit":"v8\Isolate":private]=>
  int(0)
  ["memory_limit_hit":"v8\Isolate":private]=>
  bool(false)
}

v8\Exceptions\TimeLimitException: Time limit exceeded
script execution terminated

float(1.5%d)
Script execution time is between 1.500 and 1.600: ok

object(v8\Isolate)#2 (5) {
  ["snapshot":"v8\Isolate":private]=>
  NULL
  ["time_limit":"v8\Isolate":private]=>
  float(1.5)
  ["time_limit_hit":"v8\Isolate":private]=>
  bool(true)
  ["memory_limit":"v8\Isolate":private]=>
  int(0)
  ["memory_limit_hit":"v8\Isolate":private]=>
  bool(false)
}
