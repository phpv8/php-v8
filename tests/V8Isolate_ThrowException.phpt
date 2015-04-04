--TEST--
v8\Isolate::ThrowException()
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php
/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);


$isolate = new \v8\Isolate();

try {
    $isolate->ThrowException(new \v8\StringValue($isolate, 'test exception'));
} catch (\Exception $e) {
    $helper->exception_export($e);
}

$helper->line();

$func_tpl = new \v8\FunctionTemplate($isolate, function (\v8\FunctionCallbackInfo $info) {
    $value = $info->Length() ? $info->Arguments()[0] : new \v8\StringValue($info->GetIsolate(), "exception");

    $e = $info->GetIsolate()->ThrowException($value);

    $info->GetReturnValue()->Set($e);
});


$global_tpl = new \v8\ObjectTemplate($isolate);
$global_tpl->Set(new \v8\StringValue($isolate, 'e'), $func_tpl);
$global_tpl->Set(new \v8\StringValue($isolate, 'print'), $v8_helper->getPrintFunctionTemplate($isolate));

$context = new \v8\Context($isolate, [], $global_tpl);

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
$v8_helper->CHECK($res->StrictEquals(new \v8\StringValue($isolate, 'foo')), '$res->StrictEquals(new \v8\StringValue($isolate, \'foo\'))');
$helper->line();

$v8_helper->run_checks($res);


$source = '
    var ex;

    try {
        e({});
    } catch (exception) {
        print("exception: ", "\'", exception, "\'", "\n");
        print("exception.stack: ", exception.stack, "\n");
        ex = exception;
    }

    ex
';

$res = $v8_helper->CompileRun($context, $source);
$helper->line();

$v8_helper->run_checks($res);

$source = '
    var ex;

    try {
        throw new Error();
    } catch (exception) {
        print("exception: ", "\'", exception, "\'", "\n");
        print("exception.stack: ", exception.stack, "\n");
        ex = exception;
    }

    ex
';

$res = $v8_helper->CompileRun($context, $source);
$helper->line();

$v8_helper->run_checks($res);


?>
--EXPECT--
v8\Exceptions\GenericException: Not in context!

e(): v8\Exceptions\TryCatchException: exception
e("test"): v8\Exceptions\TryCatchException: test

exception: 'foo'
exception.stack: <undefined>

CHECK $res->StrictEquals(new \v8\StringValue($isolate, 'foo')): OK

Checks on v8\StringValue:
-------------------------
v8\StringValue->IsOneByte(): bool(true)
v8\StringValue(v8\Value)->IsUndefined(): bool(false)
v8\StringValue(v8\Value)->IsNull(): bool(false)
v8\StringValue(v8\Value)->IsTrue(): bool(false)
v8\StringValue(v8\Value)->IsFalse(): bool(false)
v8\StringValue(v8\Value)->IsName(): bool(true)
v8\StringValue(v8\Value)->IsString(): bool(true)
v8\StringValue(v8\Value)->IsSymbol(): bool(false)
v8\StringValue(v8\Value)->IsFunction(): bool(false)
v8\StringValue(v8\Value)->IsArray(): bool(false)
v8\StringValue(v8\Value)->IsObject(): bool(false)
v8\StringValue(v8\Value)->IsBoolean(): bool(false)
v8\StringValue(v8\Value)->IsNumber(): bool(false)
v8\StringValue(v8\Value)->IsInt32(): bool(false)
v8\StringValue(v8\Value)->IsUint32(): bool(false)
v8\StringValue(v8\Value)->IsDate(): bool(false)
v8\StringValue(v8\Value)->IsArgumentsObject(): bool(false)
v8\StringValue(v8\Value)->IsBooleanObject(): bool(false)
v8\StringValue(v8\Value)->IsNumberObject(): bool(false)
v8\StringValue(v8\Value)->IsStringObject(): bool(false)
v8\StringValue(v8\Value)->IsSymbolObject(): bool(false)
v8\StringValue(v8\Value)->IsNativeError(): bool(false)
v8\StringValue(v8\Value)->IsRegExp(): bool(false)


exception: '[object Object]'
exception.stack: <undefined>

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
v8\ObjectValue(v8\Value)->IsNativeError(): bool(false)
v8\ObjectValue(v8\Value)->IsRegExp(): bool(false)


exception: 'Error'
exception.stack: Error
    at test.js:5:15

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
