--TEST--
V8\Context
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

$isolate = new \V8\Isolate();

$context = new \V8\Context($isolate);

$helper->method_matches_instanceof($context, 'globalObject', \V8\ObjectValue::class);

$global = $context->globalObject();

$v8_helper->CHECK($global->sameValue($context->globalObject()), '$global->sameValue($context->globalObject())');

$helper->method_matches($context, 'isCodeGenerationFromStringsAllowed', true);
$v8_helper->CompileTryRun($context, 'eval("1+1")');

$helper->assert('Code generation allowed', $context->isCodeGenerationFromStringsAllowed() === true);

$context->allowCodeGenerationFromStrings(false);

$helper->assert('Code generation is not allowed', $context->isCodeGenerationFromStringsAllowed() === false);

$helper->method_matches_with_output($context, 'isCodeGenerationFromStringsAllowed', false);
$res = $v8_helper->CompileTryRun($context, 'eval("1+1")');

$context->setErrorMessageForCodeGenerationFromStrings(new \V8\StringValue($isolate, 'Whoa! Nope. No eval this time, sorry.'));
$res = $v8_helper->CompileTryRun($context, 'eval("2+2")');


?>
--EXPECT--
V8\Context::globalObject() result is instance of V8\ObjectValue
CHECK $global->sameValue($context->globalObject()): OK
V8\Context::isCodeGenerationFromStringsAllowed() matches expected value
Code generation allowed: ok
Code generation is not allowed: ok
V8\Context::isCodeGenerationFromStringsAllowed() matches expected false
eval("1+1"): V8\Exceptions\TryCatchException: EvalError: Code generation from strings disallowed for this context
eval("2+2"): V8\Exceptions\TryCatchException: EvalError: Whoa! Nope. No eval this time, sorry.
