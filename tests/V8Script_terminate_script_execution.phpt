--TEST--
V8\Script::Run - terminate script execution
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

require '.tracking_dtors.php';

$isolate1 = new v8Tests\TrackingDtors\Isolate();

//$isolate1->SetCaptureStackTraceForUncaughtExceptions(true);

$global_template1 = new V8\ObjectTemplate($isolate1);

$timer = 0;
$terminate = new V8\FunctionTemplate($isolate1, function (\V8\FunctionCallbackInfo $info) use (&$timer) {
    echo 'Going to terminate', PHP_EOL;
    $isolate = $info->GetIsolate();

//    throw new Exception('test');

    $timer = microtime(true);
    $script = new V8\Script($info->GetContext(), new \V8\StringValue($isolate, 'for(;;);'), new \V8\ScriptOrigin('wait_for_termination.js'));
    $isolate->TerminateExecution();
    try {
        $script->Run($info->GetContext());
    } catch (\V8\Exceptions\TerminationException $e) {
        echo 'wait loop terminated', PHP_EOL;
    }

    $e = null;
});

$global_template1->Set(new \V8\StringValue($isolate1, 'print'), $v8_helper->getPrintFunctionTemplate($isolate1), \V8\PropertyAttribute::DontDelete);
$global_template1->Set(new \V8\StringValue($isolate1, 'terminate'), $terminate, \V8\PropertyAttribute::DontDelete);

$context1 = new V8\Context($isolate1, $global_template1);
$global_template1 = null;

$source1 = '
cnt = 0;
print("before terminate\n");
terminate();
while (true) {
    print("after terminate ", cnt, "\n");
    cnt++;
}
';
$file_name1 = 'test.js';

$script1 = new V8\Script($context1, new \V8\StringValue($isolate1, $source1), new \V8\ScriptOrigin($file_name1));
try {
$res1 = $script1->Run($context1);
} catch (\V8\Exceptions\TerminationException $e) {
   $helper->exception_export($e);
} finally {
    $timer = microtime(true) - $timer;
    echo "Shutdown took: ", $timer, 'sec', PHP_EOL;
    // TODO:what about valgrind impact
    // this is not very clean metric, but if it takes more than 1 sec than it is definitely something wrong.
    echo "Shutdown is less than 1sec: ", (($timer < 1) ? 'yes' : 'no'), PHP_EOL;
}

$e          = null;
$terminate  = null;
$res1       = null;
$script1    = null;
$context1   = null;
$isolate1   = null;

echo 'Done here', PHP_EOL;
?>
EOF
--EXPECTF--
before terminate
Going to terminate
wait loop terminated
V8\Exceptions\TerminationException: Execution terminated
Shutdown took: %fsec
Shutdown is less than 1sec: yes
Isolate dies now!
Done here
EOF
