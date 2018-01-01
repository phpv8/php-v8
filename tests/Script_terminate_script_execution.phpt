--TEST--
V8\Script::run() - terminate script execution
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

require '.tracking_dtors.php';

$isolate = new v8Tests\TrackingDtors\Isolate();

//$isolate->setCaptureStackTraceForUncaughtExceptions(true);

$global_template = new V8\ObjectTemplate($isolate);

$timer = 0;
$terminate = new V8\FunctionTemplate($isolate, function (\V8\FunctionCallbackInfo $info) use (&$timer) {
    echo 'Going to terminate', PHP_EOL;
    $isolate = $info->getIsolate();

//    throw new Exception('test');

    $timer = microtime(true);
    $script = new V8\Script($info->getContext(), new \V8\StringValue($isolate, 'for(;;);'), new \V8\ScriptOrigin('wait_for_termination.js'));
    $isolate->terminateExecution();
    try {
        $script->run($info->getContext());
    } catch (\V8\Exceptions\TerminationException $e) {
        echo 'wait loop terminated', PHP_EOL;
    }

    $e = null;
});

$global_template->set(new \V8\StringValue($isolate, 'terminate'), $terminate, \V8\PropertyAttribute::DONT_DELETE);

$context = new V8\Context($isolate, $global_template);
$v8_helper->injectConsoleLog($context);

$global_template = null;

$source = '
cnt = 0;
console.log("before terminate");
terminate();
while (true) {
    console.log("after terminate ", cnt);
    cnt++;
}
';
$file_name = 'test.js';

try {
    $res = $v8_helper->CompileRun($context, $source);
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
$res       = null;
$context   = null;
$isolate   = null;

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
