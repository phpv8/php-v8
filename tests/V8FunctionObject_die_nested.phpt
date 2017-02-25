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

$isolate1         = new v8Tests\TrackingDtors\Isolate();
$global_template1 = new V8\ObjectTemplate($isolate1);
$context1         = new V8\Context($isolate1, $global_template1);


$die_func = new v8Tests\TrackingDtors\FunctionObject($context1, function (\V8\FunctionCallbackInfo $info) {
    echo 'going to die...', PHP_EOL;
    die();
});

$die_func->destructor_test_message = 'die() function dtored';

$teste_nested_func = new v8Tests\TrackingDtors\FunctionObject($context1, function (\V8\FunctionCallbackInfo $info) {
    echo 'calling nested...', PHP_EOL;
    $context = $info->GetContext();

    $context->GlobalObject()->Get($context, new \V8\StringValue($context->GetIsolate(), 'die'))->Call($context, $context->GlobalObject());
});

$teste_nested_func->destructor_test_message = 'test_nested() function dtored';


$context1->GlobalObject()->Set($context1, new \V8\StringValue($isolate1, 'die'), $die_func);
$context1->GlobalObject()->Set($context1, new \V8\StringValue($isolate1, 'test_nested'), $teste_nested_func);


$res = $v8_helper->CompileRun($context1, 'test_nested(); "Script done"');

$helper->pretty_dump('Script result', $res->ToString($context1)->Value());

echo 'We are done for now', PHP_EOL;

?>
--EXPECT--
calling nested...
going to die...
test_nested() function dtored
Isolate dies now!
die() function dtored
