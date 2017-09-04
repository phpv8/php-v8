--TEST--
V8\Isolate - nested termination exceptions
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--ENV--
HOME=/tmp/we-need-home-env-var-set-to-load-valgrindrc
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.tracking_dtors.php';

$isolate = new V8\Isolate();
$global_template = new V8\ObjectTemplate($isolate);

$context = new V8\Context($isolate, $global_template);

$func = new V8\FunctionObject($context, function (\V8\FunctionCallbackInfo $info) {
    if (!$info->arguments()) {
        $isolate = $info->getIsolate();

        $script = new V8\Script($info->getContext(), new \V8\StringValue($isolate, 'for(;;);'), new \V8\ScriptOrigin('wait_for_termination.js'));
        $isolate->terminateExecution();

        try {
            $script->run($info->getContext());
        } catch (\V8\Exceptions\TerminationException $e) {
            echo 'wait loop terminated', PHP_EOL;
        }

        return;
    }

    $fnc= $info->arguments()[0];

    try {
        $fnc->call($info->getContext(), $fnc);
    } catch (\V8\Exceptions\TerminationException $e) {
        echo 'function call terminated', PHP_EOL;
    }
});


$func->setName(new \V8\StringValue($isolate, 'custom_name'));


$context->globalObject()->set($context, new \V8\StringValue($isolate, 'test'), $func);

$source = 'test(test); delete print; "Script done"';
$file_name = 'test.js';


$script = new V8\Script($context, new \V8\StringValue($isolate, $source), new \V8\ScriptOrigin($file_name));

try {
    $script->run($context);
} catch (\V8\Exceptions\TerminationException $e) {
    echo 'script execution terminated', PHP_EOL;
}

?>
--EXPECT--
wait loop terminated
function call terminated
script execution terminated
