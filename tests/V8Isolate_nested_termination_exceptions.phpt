--TEST--
v8\Isolate - nested termination exceptions
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

$func = new v8\FunctionObject($context1, function (\v8\FunctionCallbackInfo $info) {
    if (!$info->Arguments()) {
        $isolate = $info->GetIsolate();

        $script = new v8\Script($info->GetContext(), new \v8\StringValue($isolate, 'for(;;);'), new \v8\ScriptOrigin('wait_for_termination.js'));
        $isolate->TerminateExecution();

        try {
            $script->Run();
        } catch (\v8\Exceptions\TerminationException $e) {
            echo 'wait loop terminated', PHP_EOL;
        }

        return;
    }

    $fnc= $info->Arguments()[0];

    try {
        $fnc->Call($info->GetContext(), $fnc);
    } catch (\v8\Exceptions\TerminationException $e) {
        echo 'function call terminated', PHP_EOL;
    }
});


$func->SetName(new \v8\StringValue($isolate1, 'custom_name'));


$context1->GlobalObject()->Set($context1, new \v8\StringValue($isolate1, 'test'), $func);

$source1 = 'test(test); delete print; "Script done"';
$file_name1 = 'test.js';


$script1 = new v8\Script($context1, new \v8\StringValue($isolate1, $source1), new \v8\ScriptOrigin($file_name1));

try {
    $script1->Run();
} catch (\v8\Exceptions\TerminationException $e) {
    echo 'script execution terminated', PHP_EOL;
}

?>
--EXPECT--
wait loop terminated
function call terminated
script execution terminated
