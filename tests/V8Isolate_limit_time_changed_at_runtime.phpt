--TEST--
V8\Isolate - time limit changed at runtime
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';
require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);


$isolate = new V8\Isolate();
$context = new V8\Context($isolate);
$v8_helper->injectConsoleLog($context);


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

// We sleep twice in php callback that called in js runtime
$low_range *=2;
$high_range *=2.2; // 2x +10%

if (!$helper->need_more_time() && $helper->is_memory_test()) {
    $high_range *=2; // valgrind adds some time overhead to test, so to avoid flaky test, we just set this to max sane value
}

$func = new V8\FunctionObject($context, function (\V8\FunctionCallbackInfo $args) use ($helper) {
    $t = microtime(true);

    $isolate = $args->GetIsolate();

    $sleep_1 = $isolate->GetTimeLimit() + $isolate->GetTimeLimit()/10; // +10% to make 100% sure we hit time limit
    $sleep_2 = $isolate->GetTimeLimit() + $isolate->GetTimeLimit()/10;

    $helper->assert('Time limit is not hit', !$isolate->IsTimeLimitHit());
    echo 'sleep ', $sleep_1, 'sec', PHP_EOL;
    usleep($sleep_1*1000000);
    $helper->assert('Time limit is hit', $isolate->IsTimeLimitHit());

    $args->GetIsolate()->SetTimeLimit($isolate->GetTimeLimit()); // Setting timeout will reset any previous timeout

    $helper->assert('Setting time limit from php resets time limit hit', !$isolate->IsTimeLimitHit());
    echo 'sleep ', $sleep_2, 'sec', PHP_EOL;
    usleep($sleep_2*1000000);
    $helper->assert('However, it is hit once again', $isolate->IsTimeLimitHit());

    echo 'total in function: ', microtime(true) - $t, 'sec', PHP_EOL;
});

$context->GlobalObject()->Set($context, new \V8\StringValue($isolate, 'sleep'), $func);

$isolate->SetTimeLimit($time_limit);

$t = microtime(true);
try {
    // pause execution in php, and then trigger timeout exception by running some native js operations
    $v8_helper->CompileRun($context, 'sleep(); let i = 0; while(true) {i++;}');
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
$helper->dump($isolate);

// EXPECTF: ---/\d\.\d+sec/
// EXPECTF: +++%fsec

// EXPECTF: ---/float\(.+\)/
// EXPECTF: +++float(%f)

// EXPECTF: ---/range \(.+, .+\)/
// EXPECTF: +++range (%f, %f)
?>
--EXPECTF--
Time limit is not hit: ok
sleep %fsec
Time limit is hit: ok
Setting time limit from php resets time limit hit: ok
sleep %fsec
However, it is hit once again: ok
total in function: %fsec
V8\Exceptions\TimeLimitException: Time limit exceeded
script execution terminated

float(%f)
Script execution time is within specified range (%f, %f): ok

object(V8\Isolate)#3 (0) {
}
