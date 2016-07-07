--TEST--
v8\Isolate - time limit
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

// Tests:

$isolate = new v8\Isolate();
$context = new v8\Context($isolate);


$source    = '
    var i = 0;
    while(true) { i++};
';
$file_name = 'test.js';

$script = new v8\Script($context, new \v8\StringValue($isolate, $source), new \v8\ScriptOrigin($file_name));

$time_limit = 1.5;
$helper->assert('Time limit accessor report no hit', false === $isolate->IsTimeLimitHit());
$helper->assert('Get time limit default value is zero', 0.0 === $isolate->GetTimeLimit());
$isolate->SetTimeLimit($time_limit);
$helper->assert('Get time limit returns valid value', $time_limit === $isolate->GetTimeLimit());

$helper->dump($isolate);
$helper->line();

$t = microtime(true);
try {
  $res = $script->Run();
} catch(\v8\Exceptions\TimeLimitException $e) {
  $helper->exception_export($e);
  echo 'script execution terminated', PHP_EOL;
} finally {
  $helper->line();
  $t = microtime(true) - $t;
  $helper->dump(round($t, 9));
  $helper->assert('Script execution time is between 1.500 and 1.600', $t >= 1.500 && $t < 1.599);
}

$helper->assert('Get time limit returns valid value', $time_limit === $isolate->GetTimeLimit());
$helper->assert('Time limit accessor report hit', true === $isolate->IsTimeLimitHit());

$helper->line();
$helper->dump($isolate);
?>
--EXPECTF--
Time limit accessor report no hit: ok
Get time limit default value is zero: ok
Get time limit returns valid value: ok
object(v8\Isolate)#3 (5) {
  ["snapshot":"v8\Isolate":private]=>
  NULL
  ["time_limit":"v8\Isolate":private]=>
  float(1.5)
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
Get time limit returns valid value: ok
Time limit accessor report hit: ok

object(v8\Isolate)#3 (5) {
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
