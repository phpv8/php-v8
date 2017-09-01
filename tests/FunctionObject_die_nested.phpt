--TEST--
V8\FunctionObject - test die() during nested calling
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

require '.tracking_dtors.php';

$isolate         = new v8Tests\TrackingDtors\Isolate();
$global_template = new V8\ObjectTemplate($isolate);
$context         = new V8\Context($isolate, $global_template);


$die_func = new v8Tests\TrackingDtors\FunctionObject($context, function (\V8\FunctionCallbackInfo $info) {
    echo 'going to die...', PHP_EOL;
    die();
});

$die_func->destructor_test_message = 'die() function dtored';

$teste_nested_func = new v8Tests\TrackingDtors\FunctionObject($context, function (\V8\FunctionCallbackInfo $info) {
    echo 'calling nested...', PHP_EOL;
    $context = $info->getContext();

    $context->globalObject()->get($context, new \V8\StringValue($context->getIsolate(), 'die'))->call($context, $context->globalObject());
});

$teste_nested_func->destructor_test_message = 'test_nested() function dtored';


$context->globalObject()->set($context, new \V8\StringValue($isolate, 'die'), $die_func);
$context->globalObject()->set($context, new \V8\StringValue($isolate, 'test_nested'), $teste_nested_func);


$res = $v8_helper->CompileRun($context, 'test_nested(); "Script done"');

$helper->pretty_dump('Script result', $res->toString($context)->value());

echo 'We are done for now', PHP_EOL;

?>
--EXPECT--
calling nested...
going to die...
test_nested() function dtored
Isolate dies now!
die() function dtored
