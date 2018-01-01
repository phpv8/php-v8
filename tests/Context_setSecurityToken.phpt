--TEST--
V8\Context::setSecurityToken()
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';
require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);


$isolate = new \V8\Isolate();

$context = new \V8\Context($isolate);
$v8_helper->injectConsoleLog($context);

$other = new \V8\Context($isolate);
$v8_helper->injectConsoleLog($other);

$obj_own = new \V8\ObjectValue($context);
$obj_own->set($context, new \V8\StringValue($isolate, 'test'), new \V8\StringValue($isolate, 'own'));

$obj_other = new \V8\ObjectValue($context);
$obj_other->set($context, new \V8\StringValue($isolate, 'test'), new \V8\StringValue($isolate, 'other'));


try {
    $context->globalObject()->set($context, new \V8\StringValue($isolate, 'own'), $obj_own);
    $context->globalObject()->set($other, new \V8\StringValue($isolate, 'other'), $obj_other);
    $helper->assert('There is no cross-context access by default', false);
} catch (\V8\Exceptions\TryCatchException $e) {
    $helper->exception_export($e);
}

$context->setSecurityToken(new \V8\StringValue($isolate, 'secret 1'));
$other->setSecurityToken(new \V8\StringValue($isolate, 'secret 2'));

try {
    $context->globalObject()->set($context, new \V8\StringValue($isolate, 'own'), $obj_own);
    $context->globalObject()->set($other, new \V8\StringValue($isolate, 'other'), $obj_other);
    $helper->assert('Different security tokens should not grant cross-context access', false);
} catch (\V8\Exceptions\TryCatchException $e) {
    $helper->exception_export($e);
}


$context->setSecurityToken(new \V8\StringValue($isolate, 'secret'));
$other->setSecurityToken(new \V8\StringValue($isolate, 'secret'));

try {
    $context->globalObject()->set($context, new \V8\StringValue($isolate, 'own'), $obj_own);
    $context->globalObject()->set($other, new \V8\StringValue($isolate, 'other'), $obj_other);
    $helper->assert('Different security tokens with the same value should not grant cross-context access', false);
} catch (\V8\Exceptions\TryCatchException $e) {
    $helper->exception_export($e);
}


$secret = new \V8\StringValue($isolate, 'secret');

$context->setSecurityToken($secret);
$other->setSecurityToken($secret);

$context->globalObject()->set($context, new \V8\StringValue($isolate, 'own'), $obj_own);
$context->globalObject()->set($other, new \V8\StringValue($isolate, 'other'), $obj_other);

$helper->line();


$v8_helper->CompileRun($context, <<<'SCRIPT'
console.log('own.test: ', own.test);
console.log('other.test: ', other.test);
SCRIPT
);

echo 'We are done for now', PHP_EOL;
?>
EOF
--EXPECT--
V8\Exceptions\TryCatchException: TypeError: no access
V8\Exceptions\TryCatchException: TypeError: no access
V8\Exceptions\TryCatchException: TypeError: no access

own.test: own
other.test: other
We are done for now
EOF
