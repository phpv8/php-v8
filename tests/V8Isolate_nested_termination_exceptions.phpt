--TEST--
V8\Isolate - nested termination exceptions
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.tracking_dtors.php';

$isolate = new V8\Isolate();
$global_template = new V8\ObjectTemplate($isolate);

$context = new V8\Context($isolate, $global_template);

$func = new V8\FunctionObject($context, function (\V8\FunctionCallbackInfo $info) {
    if (!$info->Arguments()) {
        $isolate = $info->GetIsolate();

        $script = new V8\Script($info->GetContext(), new \V8\StringValue($isolate, 'for(;;);'), new \V8\ScriptOrigin('wait_for_termination.js'));
        $isolate->TerminateExecution();

        try {
            $script->Run($info->GetContext());
        } catch (\V8\Exceptions\TerminationException $e) {
            echo 'wait loop terminated', PHP_EOL;
        }

        return;
    }

    $fnc= $info->Arguments()[0];

    try {
        $fnc->Call($info->GetContext(), $fnc);
    } catch (\V8\Exceptions\TerminationException $e) {
        echo 'function call terminated', PHP_EOL;
    }
});


$func->SetName(new \V8\StringValue($isolate, 'custom_name'));


$context->GlobalObject()->Set($context, new \V8\StringValue($isolate, 'test'), $func);

$source = 'test(test); delete print; "Script done"';
$file_name = 'test.js';


$script = new V8\Script($context, new \V8\StringValue($isolate, $source), new \V8\ScriptOrigin($file_name));

try {
    $script->Run($context);
} catch (\V8\Exceptions\TerminationException $e) {
    echo 'script execution terminated', PHP_EOL;
}

?>
--EXPECT--
wait loop terminated
function call terminated
script execution terminated
