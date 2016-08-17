--TEST--
V8\Exception::Error()
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

try {
    $error = V8\Exception::Error($context, new \V8\StringValue($isolate, 'test'));
    $helper->assert('Can create error when out of context', $error instanceof \V8\Value);
} catch (\Exception $e) {
    $helper->exception_export($e);
}

$helper->line();

$func_tpl = new \V8\FunctionTemplate($isolate, function (\V8\FunctionCallbackInfo $info) {
    $value = $info->Length() ? $info->Arguments()[0] : new \V8\StringValue($info->GetIsolate(), "exception");

    $e = $info->GetIsolate()->ThrowException(V8\Exception::Error($info->GetContext(), $value));

    $info->GetReturnValue()->Set($e);
});

$func_test_tpl = new \V8\FunctionTemplate($isolate, function (\V8\FunctionCallbackInfo $info) use ($helper, $v8_helper) {

    $message = new \V8\StringValue($info->GetIsolate(), "test");
    $value1 = V8\Exception::Error($info->GetContext(), $message);
    $value2 = V8\Exception::Error($info->GetContext(), $message);

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

$global_tpl = new \V8\ObjectTemplate($isolate);
$global_tpl->Set(new \V8\StringValue($isolate, 'e'), $func_tpl);
$global_tpl->Set(new \V8\StringValue($isolate, 'print'), $v8_helper->getPrintFunctionTemplate($isolate));
$global_tpl->Set(new \V8\StringValue($isolate, 'test'), $func_test_tpl);

$context = new \V8\Context($isolate, [], $global_tpl);

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

e(): V8\Exceptions\TryCatchException: Error: exception
e("test"): V8\Exceptions\TryCatchException: Error: test

exception: 'Error: foo'
exception.stack: Error: foo
    at test.js:5:9

Checks on V8\ObjectValue:
-------------------------
V8\ObjectValue(V8\Value)->TypeOf(): V8\StringValue->Value(): string(6) "object"

V8\ObjectValue->IsCallable(): bool(false)
V8\ObjectValue(V8\Value)->IsUndefined(): bool(false)
V8\ObjectValue(V8\Value)->IsNull(): bool(false)
V8\ObjectValue(V8\Value)->IsTrue(): bool(false)
V8\ObjectValue(V8\Value)->IsFalse(): bool(false)
V8\ObjectValue(V8\Value)->IsName(): bool(false)
V8\ObjectValue(V8\Value)->IsString(): bool(false)
V8\ObjectValue(V8\Value)->IsSymbol(): bool(false)
V8\ObjectValue(V8\Value)->IsFunction(): bool(false)
V8\ObjectValue(V8\Value)->IsArray(): bool(false)
V8\ObjectValue(V8\Value)->IsObject(): bool(true)
V8\ObjectValue(V8\Value)->IsBoolean(): bool(false)
V8\ObjectValue(V8\Value)->IsNumber(): bool(false)
V8\ObjectValue(V8\Value)->IsInt32(): bool(false)
V8\ObjectValue(V8\Value)->IsUint32(): bool(false)
V8\ObjectValue(V8\Value)->IsDate(): bool(false)
V8\ObjectValue(V8\Value)->IsArgumentsObject(): bool(false)
V8\ObjectValue(V8\Value)->IsBooleanObject(): bool(false)
V8\ObjectValue(V8\Value)->IsNumberObject(): bool(false)
V8\ObjectValue(V8\Value)->IsStringObject(): bool(false)
V8\ObjectValue(V8\Value)->IsSymbolObject(): bool(false)
V8\ObjectValue(V8\Value)->IsNativeError(): bool(true)
V8\ObjectValue(V8\Value)->IsRegExp(): bool(false)
