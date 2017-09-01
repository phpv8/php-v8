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

$isolate = new \V8\Isolate();

$test_func_tpl = new \V8\FunctionTemplate($isolate, function (\V8\FunctionCallbackInfo $info) {
    throw new Exception('Unexpected exception');
});


$global_template = new V8\ObjectTemplate($isolate);
$global_template->set(new \V8\StringValue($isolate, 'test'), $test_func_tpl, \V8\PropertyAttribute::DONT_DELETE);

$context = new V8\Context($isolate, $global_template);

$source = 'test(); "Script done"';
$file_name = 'test.js';

$script = new V8\Script($context, new \V8\StringValue($isolate, $source), new \V8\ScriptOrigin($file_name));

try {
    $helper->dump($script->run($context)->toString($context)->value());
} catch (Exception $e) {
    $helper->exception_export($e);
}

echo 'We are done for now', PHP_EOL;

?>
--EXPECTF--
Exception: Unexpected exception
We are done for now
