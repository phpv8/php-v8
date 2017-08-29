--TEST--
V8\FunctionObject - test die() called from different Isolate
--SKIPIF--
<?php if (!extension_loaded("v8")) {
    print "skip";
} ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

require '.tracking_dtors.php';

$isolate_inner = new v8Tests\TrackingDtors\Isolate();
$context_inner = new V8\Context($isolate_inner);

$die_func = new v8Tests\TrackingDtors\FunctionObject($context_inner, function (\V8\FunctionCallbackInfo $info) {
    echo 'going to die...', PHP_EOL;
    die();
});

$context_inner->globalObject()->set($context_inner, new \V8\StringValue($isolate_inner, 'die'), $die_func);

$die_func->destructor_test_message      = 'die() function from inner isolate dtored';
$isolate_inner->destructor_test_message = 'inner isolate dtored';


$isolate_outer = new v8Tests\TrackingDtors\Isolate();
$context_outer = new V8\Context($isolate_outer);


$test_other_func = new v8Tests\TrackingDtors\FunctionObject($context_outer, function (\V8\FunctionCallbackInfo $info) use ($context_inner) {
    echo 'calling inner...', PHP_EOL;
    $isolate_inner = $context_inner->getIsolate();
    $global_inner = $context_inner->globalObject();

    $global_inner->get($context_inner, new \V8\StringValue($isolate_inner, 'die'))->call($context_inner, $global_inner);
});

$test_other_func->destructor_test_message = 'test_other() function from outer isolate dtored';
$isolate_outer->destructor_test_message = 'outer isolate dtored';


$context_outer->globalObject()->set($context_outer, new \V8\StringValue($isolate_outer, 'test_other'), $test_other_func);


$res = $v8_helper->CompileRun($context_outer, 'test_other(); "Script done"');

$helper->pretty_dump('Script result', $res->toString($context_outer)->value());

echo 'We are done for now', PHP_EOL;

?>
--EXPECT--
calling inner...
going to die...
test_other() function from outer isolate dtored
inner isolate dtored
die() function from inner isolate dtored
outer isolate dtored
