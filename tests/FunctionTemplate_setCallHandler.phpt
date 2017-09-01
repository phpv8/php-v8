--TEST--
V8\FunctionTemplate::setCallHandler()
--SKIPIF--
<?php if (!extension_loaded("v8")) {
    print "skip";
} ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

$isolate = new \V8\Isolate();


$test_func_tpl = new \V8\FunctionTemplate($isolate, function () {echo 'callback 1', PHP_EOL;});
$test_func_tpl->setCallHandler(function () {echo 'callback test()', PHP_EOL;});

try {
    $test_func_tpl->setCallHandler(null);
} catch (Throwable $e) {
    $helper->exception_export($e);
    $helper->line();
}

$change_func_tpl = new \V8\FunctionTemplate($isolate, function () use ($test_func_tpl){
    $test_func_tpl->setCallHandler(function () {echo 'callback change()', PHP_EOL;});
});

$global_template = new \V8\ObjectTemplate($isolate);

$global_template->set(new \V8\StringValue($isolate, 'test'), $test_func_tpl);
$global_template->set(new \V8\StringValue($isolate, 'change'), $change_func_tpl);

$context = new \V8\Context($isolate, $global_template);


$source = 'test(); change(); test(); "Script done"';
$file_name = 'test.js';


$script = new \V8\Script($context, new \V8\StringValue($isolate, $source), new \V8\ScriptOrigin($file_name));

try {
    $helper->dump($script->run($context)->toString($context)->value());
} catch (Exception $e) {
    $helper->exception_export($e);
}

echo 'We are done for now', PHP_EOL;

?>
EOF
--EXPECT--
TypeError: Argument 1 passed to V8\FunctionTemplate::setCallHandler() must be callable, null given

callback test()
V8\Exceptions\Exception: v8::FunctionTemplate::SetCallHandler FunctionTemplate already instantiated
We are done for now
EOF
