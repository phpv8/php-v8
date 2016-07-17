--TEST--
V8\FunctionTemplate: exception in php thrown
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

$test_func_tpl = new \V8\FunctionTemplate($isolate1, function (\V8\FunctionCallbackInfo $info) {
    throw new Exception('Unexpected exception');
});


$global_template1 = new V8\ObjectTemplate($isolate1);
$global_template1->Set(new \V8\StringValue($isolate1, 'test'), $test_func_tpl, \V8\PropertyAttribute::DontDelete);

$context1 = new V8\Context($isolate1, $extensions1, $global_template1);

$source1 = 'test(); "Script done"';
$file_name1 = 'test.js';

$script1 = new V8\Script($context1, new \V8\StringValue($isolate1, $source1), new \V8\ScriptOrigin($file_name1));

try {
    $helper->dump($script1->Run()->ToString($context1)->Value());
} catch (Exception $e) {
    $helper->exception_export($e);
}

echo 'We are done for now', PHP_EOL;

?>
--EXPECTF--
Exception: Unexpected exception
We are done for now
