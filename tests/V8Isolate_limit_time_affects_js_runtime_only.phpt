--TEST--
V8\Isolate - time limit affects js runtime only
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

$helper->assert('Time limit accessor report no hit', false === $isolate->IsTimeLimitHit());
$helper->assert('Get time limit default value is zero', 0.0 === $isolate->GetTimeLimit());
$isolate->SetTimeLimit($time_limit);
$helper->assert('Get time limit returns valid value', $time_limit === $isolate->GetTimeLimit());

$helper->dump($isolate);
$helper->line();

// sleeping **before** running js should not affect js runtime timeout
sleep($time_limit);

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

// EXPECTF: ---/float\(.+\)/
// EXPECTF: +++float(%f)

// EXPECTF: ---/range \(.+, .+\)/
// EXPECTF: +++range (%f, %f)
?>
--EXPECTF--
Time limit accessor report no hit: ok
Get time limit default value is zero: ok
Get time limit returns valid value: ok
object(V8\Isolate)#3 (0) {
}

V8\Exceptions\TimeLimitException: Time limit exceeded
script execution terminated

float(%f)
Script execution time is within specified range (%f, %f): ok
Get time limit returns valid value: ok
Time limit accessor report hit: ok

object(V8\Isolate)#3 (0) {
}
