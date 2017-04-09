--TEST--
V8\Isolate::GetEnteredContext()
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php
/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);


$isolate = new \V8\Isolate();

try {
    $isolate->GetEnteredContext();
}catch (\V8\Exceptions\Exception $e) {
    $helper->exception_export($e);
}


$helper->line();

$func_tpl = new \V8\FunctionTemplate($isolate, function (\V8\FunctionCallbackInfo $info) use ($helper) {
    $helper->assert('Isolate has entered context', $info->GetIsolate()->GetEnteredContext() === $info->GetContext());
});


$global_tpl = new \V8\ObjectTemplate($isolate);
$global_tpl->Set(new \V8\StringValue($isolate, 'test'), $func_tpl);

$context = new \V8\Context($isolate, $global_tpl);

$v8_helper->CompileTryRun($context, 'test()');



?>
--EXPECT--
V8\Exceptions\Exception: Isolate doesn't have entered context

Isolate has entered context: ok
