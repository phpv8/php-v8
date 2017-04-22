--TEST--
V8\Context::SetSecurityToken()
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';
require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);


$isolate1 = new \V8\Isolate();

$context = new \V8\Context($isolate1);
$v8_helper->injectConsoleLog($context);

$other = new \V8\Context($isolate1);
$v8_helper->injectConsoleLog($other);

$obj_own = new \V8\ObjectValue($context);
$obj_own->Set($context, new \V8\StringValue($isolate1, 'test'), new \V8\StringValue($isolate1, 'own'));

$obj_other = new \V8\ObjectValue($context);
$obj_other->Set($context, new \V8\StringValue($isolate1, 'test'), new \V8\StringValue($isolate1, 'other'));


try {
    $context->GlobalObject()->Set($context, new \V8\StringValue($isolate1, 'own'), $obj_own);
    $context->GlobalObject()->Set($other, new \V8\StringValue($isolate1, 'other'), $obj_other);
    $helper->assert('There is no cross-context access by default', false);
} catch (\V8\Exceptions\TryCatchException $e) {
    $helper->exception_export($e);
}

$context->SetSecurityToken(new \V8\StringValue($isolate1, 'secret 1'));
$other->SetSecurityToken(new \V8\StringValue($isolate1, 'secret 2'));

try {
    $context->GlobalObject()->Set($context, new \V8\StringValue($isolate1, 'own'), $obj_own);
    $context->GlobalObject()->Set($other, new \V8\StringValue($isolate1, 'other'), $obj_other);
    $helper->assert('Different security tokens should not grant cross-context access', false);
} catch (\V8\Exceptions\TryCatchException $e) {
    $helper->exception_export($e);
}


$context->SetSecurityToken(new \V8\StringValue($isolate1, 'secret'));
$other->SetSecurityToken(new \V8\StringValue($isolate1, 'secret'));

try {
    $context->GlobalObject()->Set($context, new \V8\StringValue($isolate1, 'own'), $obj_own);
    $context->GlobalObject()->Set($other, new \V8\StringValue($isolate1, 'other'), $obj_other);
    $helper->assert('Different security tokens with the same value should not grant cross-context access', false);
} catch (\V8\Exceptions\TryCatchException $e) {
    $helper->exception_export($e);
}


$secret = new \V8\StringValue($isolate1, 'secret');

$context->SetSecurityToken($secret);
$other->SetSecurityToken($secret);

$context->GlobalObject()->Set($context, new \V8\StringValue($isolate1, 'own'), $obj_own);
$context->GlobalObject()->Set($other, new \V8\StringValue($isolate1, 'other'), $obj_other);

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
