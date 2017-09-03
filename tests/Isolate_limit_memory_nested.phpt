--TEST--
V8\Isolate - nested memory limit exceptions
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--ENV--
HOME=/tmp/we-need-home-env-var-set-to-load-valgrindrc
<?php
/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

if ($helper->need_more_time()) {
    echo 'skip Random bugs on travis at this time under valgrind';
}
?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.tracking_dtors.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

// Tests:

$isolate = new V8\Isolate();
$context = new V8\Context($isolate);
$v8_helper->injectConsoleLog($context);

$func = new V8\FunctionObject($context, function (\V8\FunctionCallbackInfo $info) use (&$helper) {
    if (!$info->arguments()) {
        $isolate = $info->getIsolate();

        $source = '
            var str = " ".repeat(1024); // 1kb
            var blob = [];
            while(true) {
              blob.push(str);
              //console.log(blob.length);
            }
        ';

        $script = new V8\Script($info->getContext(), new \V8\StringValue($isolate, $source), new \V8\ScriptOrigin('wait_for_termination.js'));

        try {
            $script->run($info->getContext());
        } catch (\V8\Exceptions\MemoryLimitException $e) {
            $helper->exception_export($e);
            echo 'wait loop terminated', PHP_EOL;
            $helper->line();
        }

        return;
    }

    $fnc= $info->arguments()[0];

    try {
        $fnc->call($info->getContext(), $fnc);
    } catch (\V8\Exceptions\MemoryLimitException $e) {
        $helper->exception_export($e);
        echo 'function call terminated', PHP_EOL;
        $helper->line();
    }
});


$func->setName(new \V8\StringValue($isolate, 'custom_name'));


$context->globalObject()->set($context, new \V8\StringValue($isolate, 'test'), $func);

$source = 'test(test); delete print; "Script done"';
$file_name = 'test.js';


$script = new V8\Script($context, new \V8\StringValue($isolate, $source), new \V8\ScriptOrigin($file_name));

$isolate->setMemoryLimit(1024 * 1024 * 10);
$helper->dump($isolate);
$helper->line();

$t = microtime(true);
try {
    $script->run($context);
} catch(\V8\Exceptions\MemoryLimitException $e) {
    $helper->exception_export($e);
    echo 'script execution terminated', PHP_EOL;
}

$helper->line();
$helper->dump($isolate);
?>
--EXPECT--
object(V8\Isolate)#3 (0) {
}

V8\Exceptions\MemoryLimitException: Memory limit exceeded
wait loop terminated

V8\Exceptions\MemoryLimitException: Memory limit exceeded
function call terminated

V8\Exceptions\MemoryLimitException: Memory limit exceeded
script execution terminated

object(V8\Isolate)#3 (0) {
}
