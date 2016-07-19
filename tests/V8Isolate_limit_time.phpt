--TEST--
V8\Isolate - time limit
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
$context = new V8\Context($isolate);


$source    = '
    var i = 0;
    while(true) { i++};
';
$file_name = 'test.js';

$script = new V8\Script($context, new \V8\StringValue($isolate, $source), new \V8\ScriptOrigin($file_name));

// NOTE: this check is a bit fragile but should fits our need
$needs_more_time = isset($_ENV['TRAVIS']) && isset($_ENV['TEST_PHP_ARGS']) && $_ENV['TEST_PHP_ARGS'] == '-m';

if ($needs_more_time) {
  // On travis when valgrind active it takes more time to complete all operations so we just increase initial limits
  $time_limit = 5.0;
  $low_range = 4.5;
  $high_range = 7.5;
} else {
  $time_limit = 1.5;
  $low_range = 1.45;
  $high_range = 1.6;
}

$helper->assert('Time limit accessor report no hit', false === $isolate->IsTimeLimitHit());
$helper->assert('Get time limit default value is zero', 0.0 === $isolate->GetTimeLimit());
$isolate->SetTimeLimit($time_limit);
$helper->assert('Get time limit returns valid value', $time_limit === $isolate->GetTimeLimit());

$helper->dump($isolate);
$helper->line();

$t = microtime(true);
try {
  $res = $script->Run($context);
} catch(\V8\Exceptions\TimeLimitException $e) {
  $helper->exception_export($e);
  echo 'script execution terminated', PHP_EOL;
} finally {
  $helper->line();
  $t = microtime(true) - $t;
  $helper->dump(round($t, 9));
  $helper->assert("Script execution time is within specified range ({$low_range}, {$high_range})", $t >= $low_range && $t < $high_range);
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
object(V8\Isolate)#3 (5) {
  ["snapshot":"V8\Isolate":private]=>
  NULL
  ["time_limit":"V8\Isolate":private]=>
  float(%f)
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
Get time limit returns valid value: ok
Time limit accessor report hit: ok

object(V8\Isolate)#3 (5) {
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
