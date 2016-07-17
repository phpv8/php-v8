--TEST--
v8\Isolate - nested time limit exceptions
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
    if (!$info->Arguments()) {
        $isolate = $info->GetIsolate();

        $script = new v8\Script($info->GetContext(), new \v8\StringValue($isolate, 'for(;;);'), new \v8\ScriptOrigin('wait_for_termination.js'));

        try {
            $script->Run();
        } catch (\v8\Exceptions\TimeLimitException $e) {
            $helper->exception_export($e);
            echo 'wait loop terminated', PHP_EOL;
            $helper->line();
        }

        return;
    }

    $fnc= $info->Arguments()[0];

    try {
        $fnc->Call($info->GetContext(), $fnc);
    } catch (\v8\Exceptions\TimeLimitException $e) {
        $helper->exception_export($e);
        echo 'function call terminated', PHP_EOL;
        $helper->line();
    }
});


$func->SetName(new \v8\StringValue($isolate1, 'custom_name'));


$context1->GlobalObject()->Set($context1, new \v8\StringValue($isolate1, 'test'), $func);

$source1 = 'test(test); delete print; "Script done"';
$file_name1 = 'test.js';


$script1 = new v8\Script($context1, new \v8\StringValue($isolate1, $source1), new \v8\ScriptOrigin($file_name1));

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

$isolate1->SetTimeLimit($time_limit);
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
    $helper->assert("Script execution time is within specified range ({$low_range}, {$high_range})", $t >= $low_range && $t < $high_range);
}

$helper->line();
$helper->dump($isolate1);
?>
--EXPECTF--
object(v8\Isolate)#2 (5) {
  ["snapshot":"v8\Isolate":private]=>
  NULL
  ["time_limit":"v8\Isolate":private]=>
  float(%f)
  ["time_limit_hit":"v8\Isolate":private]=>
  bool(false)
  ["memory_limit":"v8\Isolate":private]=>
  int(0)
  ["memory_limit_hit":"v8\Isolate":private]=>
  bool(false)
}

v8\Exceptions\TimeLimitException: Time limit exceeded
wait loop terminated

v8\Exceptions\TimeLimitException: Time limit exceeded
function call terminated

v8\Exceptions\TimeLimitException: Time limit exceeded
script execution terminated

float(%f)
Script execution time is within specified range (%f, %f): ok

object(v8\Isolate)#2 (5) {
  ["snapshot":"v8\Isolate":private]=>
  NULL
  ["time_limit":"v8\Isolate":private]=>
  float(%f)
  ["time_limit_hit":"v8\Isolate":private]=>
  bool(true)
  ["memory_limit":"v8\Isolate":private]=>
  int(0)
  ["memory_limit_hit":"v8\Isolate":private]=>
  bool(false)
}