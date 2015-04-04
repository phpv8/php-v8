--TEST--
v8\Exception::SyntaxError()
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php
/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);
$isolate = new \v8\Isolate();
$context = new \v8\Context($isolate);


try {
    $error = v8\Exception::SyntaxError($context, new \v8\StringValue($isolate, 'test'));
    $helper->assert('Can create error when out of context', $error instanceof \v8\Value);
} catch (\Exception $e) {
    $helper->exception_export($e);
}

$helper->line();

$func_tpl = new \v8\FunctionTemplate($isolate, function (\v8\FunctionCallbackInfo $info) {
    $value = $info->Length() ? $info->Arguments()[0] : new \v8\StringValue($info->GetIsolate(), "exception");

    $e = $info->GetIsolate()->ThrowException(v8\Exception::SyntaxError($info->GetContext(), $value));

    $info->GetReturnValue()->Set($e);
});

$func_test_tpl = new \v8\FunctionTemplate($isolate, function (\v8\FunctionCallbackInfo $info) use ($helper, $v8_helper) {

    $message = new \v8\StringValue($info->GetIsolate(), "test");
    $value1 = v8\Exception::SyntaxError($info->GetContext(), $message);
    $value2 = v8\Exception::SyntaxError($info->GetContext(), $message);

    $context = $info->GetContext();

    $v8_helper->CHECK_NE($value1, $value2);
    $v8_helper->CHECK(!$value1->Equals($context, $value2), '!$value1->Equals($context, $value2)');
    $v8_helper->CHECK(!$value2->Equals($context, $value1), '!$value2->Equals($context, $value1)');

    $v8_helper->CHECK(!$value1->StrictEquals($value2), '!$value1->StrictEquals($value2)');
    $v8_helper->CHECK(!$value2->StrictEquals($value1), '!$value2->StrictEquals($value1)');

    $v8_helper->CHECK(!$value1->SameValue($value2), '!$value1->SameValue($value2)');
    $v8_helper->CHECK(!$value2->SameValue($value1), '!$value2->SameValue($value1)');

    $helper->line();
});

$global_tpl = new \v8\ObjectTemplate($isolate);
$global_tpl->Set(new \v8\StringValue($isolate, 'e'), $func_tpl);
$global_tpl->Set(new \v8\StringValue($isolate, 'print'), $v8_helper->getPrintFunctionTemplate($isolate));
$global_tpl->Set(new \v8\StringValue($isolate, 'test'), $func_test_tpl);

$context = new \v8\Context($isolate, [], $global_tpl);

$v8_helper->CompileTryRun($context, 'test()');
$v8_helper->CompileTryRun($context, 'e()');
$v8_helper->CompileTryRun($context, 'e("test")');

$source = '
    var ex;

    try {
        e("foo");
    } catch (exception) {
        print("exception: ", "\'", exception, "\'", "\n");
        print("exception.stack: ", exception.stack, "\n");
        ex = exception;
    }

    ex
';

$helper->line();

$res = $v8_helper->CompileRun($context, $source);
$helper->line();

$v8_helper->run_checks($res);
?>
--EXPECT--
Can create error when out of context: ok

CHECK_NE: OK
CHECK !$value1->Equals($context, $value2): OK
CHECK !$value2->Equals($context, $value1): OK
CHECK !$value1->StrictEquals($value2): OK
CHECK !$value2->StrictEquals($value1): OK
CHECK !$value1->SameValue($value2): OK
CHECK !$value2->SameValue($value1): OK

e(): v8\Exceptions\TryCatchException: SyntaxError: exception
e("test"): v8\Exceptions\TryCatchException: SyntaxError: test

exception: 'SyntaxError: foo'
exception.stack: SyntaxError: foo
    at SyntaxError (native)
    at test.js:5:9

Checks on v8\ObjectValue:
-------------------------
v8\ObjectValue->IsCallable(): bool(false)
v8\ObjectValue(v8\Value)->IsUndefined(): bool(false)
v8\ObjectValue(v8\Value)->IsNull(): bool(false)
v8\ObjectValue(v8\Value)->IsTrue(): bool(false)
v8\ObjectValue(v8\Value)->IsFalse(): bool(false)
v8\ObjectValue(v8\Value)->IsName(): bool(false)
v8\ObjectValue(v8\Value)->IsString(): bool(false)
v8\ObjectValue(v8\Value)->IsSymbol(): bool(false)
v8\ObjectValue(v8\Value)->IsFunction(): bool(false)
v8\ObjectValue(v8\Value)->IsArray(): bool(false)
v8\ObjectValue(v8\Value)->IsObject(): bool(true)
v8\ObjectValue(v8\Value)->IsBoolean(): bool(false)
v8\ObjectValue(v8\Value)->IsNumber(): bool(false)
v8\ObjectValue(v8\Value)->IsInt32(): bool(false)
v8\ObjectValue(v8\Value)->IsUint32(): bool(false)
v8\ObjectValue(v8\Value)->IsDate(): bool(false)
v8\ObjectValue(v8\Value)->IsArgumentsObject(): bool(false)
v8\ObjectValue(v8\Value)->IsBooleanObject(): bool(false)
v8\ObjectValue(v8\Value)->IsNumberObject(): bool(false)
v8\ObjectValue(v8\Value)->IsStringObject(): bool(false)
v8\ObjectValue(v8\Value)->IsSymbolObject(): bool(false)
v8\ObjectValue(v8\Value)->IsNativeError(): bool(true)
v8\ObjectValue(v8\Value)->IsRegExp(): bool(false)
