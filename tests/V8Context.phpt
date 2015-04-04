--TEST--
v8\Context
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

$isolate1 = new \v8\Isolate();
$extensions1 = [];
//$global_template1 = new v8\ObjectTemplate($isolate1);
//$global_template1->Set('print', $v8_helper->getPrintFunctionTemplate($isolate1), \v8\PropertyAttribute::DontDelete);

try{
    $context = new \v8\Context($isolate1, ['some', 'extensions']);
} catch(Exception $e) {
    $helper->exception_export($e);
}

$context = new \v8\Context($isolate1);
$helper->pretty_dump('Estimated memory usage size by this context', $context->EstimatedSize());

$helper->method_matches_instanceof($context, 'GlobalObject', \v8\ObjectValue::class);

$global = $context->GlobalObject();

$v8_helper->CHECK($global->SameValue($context->GlobalObject()), '$global->SameValue($context->GlobalObject())');

$helper->method_matches($context, 'IsCodeGenerationFromStringsAllowed', true);
$v8_helper->CompileTryRun($context, 'eval("1+1")');

$helper->assert('Code generation allowed', $context->IsCodeGenerationFromStringsAllowed() === true);

$context->AllowCodeGenerationFromStrings(false);

$helper->assert('Code generation is not allowed', $context->IsCodeGenerationFromStringsAllowed() === false);

$helper->method_matches_with_output($context, 'IsCodeGenerationFromStringsAllowed', false);
$res = $v8_helper->CompileTryRun($context, 'eval("1+1")');

$context->SetErrorMessageForCodeGenerationFromStrings(new \v8\StringValue($isolate1, 'Whoa! Nope. No eval this time, sorry.'));
$res = $v8_helper->CompileTryRun($context, 'eval("2+2")');

$helper->pretty_dump('Estimated memory usage size by this context', $context->EstimatedSize());

?>
--EXPECTF--
ErrorException: Extensions are not supported yet
Estimated memory usage size by this context: int(%d)
v8\Context::GlobalObject() result is instance of v8\ObjectValue
CHECK $global->SameValue($context->GlobalObject()): OK
v8\Context::IsCodeGenerationFromStringsAllowed() matches expected value
Code generation allowed: ok
Code generation is not allowed: ok
v8\Context::IsCodeGenerationFromStringsAllowed() matches expected false
eval("1+1"): v8\Exceptions\TryCatchException: EvalError: Code generation from strings disallowed for this context
eval("2+2"): v8\Exceptions\TryCatchException: EvalError: Whoa! Nope. No eval this time, sorry.
Estimated memory usage size by this context: int(%d)
