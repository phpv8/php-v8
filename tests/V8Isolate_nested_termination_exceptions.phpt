--TEST--
V8\Isolate - nested termination exceptions
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.tracking_dtors.php';

$isolate1 = new V8\Isolate();
$global_template1 = new V8\ObjectTemplate($isolate1);

$context1 = new V8\Context($isolate1, $global_template1);

$func = new V8\FunctionObject($context1, function (\V8\FunctionCallbackInfo $info) {
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


$func->SetName(new \V8\StringValue($isolate1, 'custom_name'));


$context1->GlobalObject()->Set($context1, new \V8\StringValue($isolate1, 'test'), $func);

$source1 = 'test(test); delete print; "Script done"';
$file_name1 = 'test.js';


$script1 = new V8\Script($context1, new \V8\StringValue($isolate1, $source1), new \V8\ScriptOrigin($file_name1));

try {
    $script1->Run($context1);
} catch (\V8\Exceptions\TerminationException $e) {
    echo 'script execution terminated', PHP_EOL;
}

?>
--EXPECT--
wait loop terminated
function call terminated
script execution terminated
