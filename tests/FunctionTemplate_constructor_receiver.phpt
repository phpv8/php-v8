--TEST--
V8\FunctionTemplate::__construct() - with receiver
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--ENV--
HOME=/tmp/we-need-home-env-var-set-to-load-valgrindrc
--FILE--
<?php
/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';
require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

require '.tracking_dtors.php';

// Tests:

$isolate = new \V8\Isolate();
$context = new \V8\Context($isolate);
$v8_helper->injectConsoleLog($context);


$cb = function (\V8\FunctionCallbackInfo $args) {
    echo 'Callback', PHP_EOL;
    $args->getReturnValue()->set(new \V8\IntegerValue($args->getIsolate(), 42));
};

$sig_obj = new \V8\FunctionTemplate($isolate);

$x = new \V8\FunctionTemplate($isolate, $cb, $sig_obj);

$context->globalObject()->set($context, new \V8\StringValue($isolate, 'sig_obj'), $sig_obj->getFunction($context));
$context->globalObject()->set($context, new \V8\StringValue($isolate, 'x'), $x->getFunction($context));

$v8_helper->CompileRun($context, "var s = new sig_obj();");

try {
    $v8_helper->CompileRun($context, "x()");
    $helper->fail();
}catch (\V8\Exceptions\TryCatchException $e) {
    $helper->exception_export($e);
}

try {
    $v8_helper->CompileRun($context, "x.call(1)");
    $helper->fail();
}catch (\V8\Exceptions\TryCatchException $e) {
    $helper->exception_export($e);
}

$res = $v8_helper->CompileRun($context, "s.x = x; s.x()");
$v8_helper->CHECK_EQ(42, $res->value());

$res = $v8_helper->CompileRun($context, "x.call(s)");
$v8_helper->CHECK_EQ(42, $res->value());


?>
--EXPECT--
V8\Exceptions\TryCatchException: TypeError: Illegal invocation
V8\Exceptions\TryCatchException: TypeError: Illegal invocation
Callback
CHECK_EQ: OK
Callback
CHECK_EQ: OK
