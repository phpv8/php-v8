--TEST--
V8\FunctionTemplate::SetCallHandler
--SKIPIF--
<?php if (!extension_loaded("v8")) {
    print "skip";
} ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

$isolate1 = new \V8\Isolate();
$extensions1 = [];


$test_func_tpl = new \V8\FunctionTemplate($isolate1, function () {echo 'callback 1', PHP_EOL;});
$test_func_tpl->SetCallHandler(function () {echo 'callback test()', PHP_EOL;});

try {
    $test_func_tpl->SetCallHandler(null);
} catch (Throwable $e) {
    $helper->exception_export($e);
    $helper->line();
}

$change_func_tpl = new \V8\FunctionTemplate($isolate1, function () use ($test_func_tpl){
    $test_func_tpl->SetCallHandler(function () {echo 'callback change()', PHP_EOL;});
});

$global_template1 = new \V8\ObjectTemplate($isolate1);

$global_template1->Set(new \V8\StringValue($isolate1, 'test'), $test_func_tpl);
$global_template1->Set(new \V8\StringValue($isolate1, 'change'), $change_func_tpl);

$context1 = new \V8\Context($isolate1, $extensions1, $global_template1);


$source1 = 'test(); change(); test(); "Script done"';
$file_name1 = 'test.js';


$script1 = new \V8\Script($context1, new \V8\StringValue($isolate1, $source1), new \V8\ScriptOrigin($file_name1));

try {
    $helper->dump($script1->Run()->ToString($context1)->Value());
} catch (Exception $e) {
    $helper->exception_export($e);
}

echo 'We are done for now', PHP_EOL;

?>
EOF
--EXPECT--
TypeError: Argument 1 passed to V8\FunctionTemplate::SetCallHandler() must be callable, null given

callback test()
V8\Exceptions\GenericException: v8::FunctionTemplate::SetCallHandler FunctionTemplate already instantiated
We are done for now
EOF
