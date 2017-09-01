--TEST--
V8\Exception::typeError()
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
    $error = V8\Exception::typeError($context, new \V8\StringValue($isolate, 'test'));
    $helper->assert('Can create error when out of context', $error instanceof \V8\Value);
} catch (\Exception $e) {
    $helper->exception_export($e);
}

$helper->line();

$func_tpl = new \V8\FunctionTemplate($isolate, function (\V8\FunctionCallbackInfo $info) {
    $value = count($info->arguments()) ? $info->arguments()[0] : new \V8\StringValue($info->getIsolate(), "exception");

    $info->getIsolate()->throwException($info->getContext(), V8\Exception::typeError($info->getContext(), $value));
});

$func_test_tpl = new \V8\FunctionTemplate($isolate, function (\V8\FunctionCallbackInfo $info) use ($helper, $v8_helper) {

    $message = new \V8\StringValue($info->getIsolate(), "test");
    $value1 = V8\Exception::typeError($info->getContext(), $message);
    $value2 = V8\Exception::typeError($info->getContext(), $message);

    $context = $info->getContext();

    $v8_helper->CHECK_NE($value1, $value2);
    $v8_helper->CHECK(!$value1->equals($context, $value2), '!$value1->equals($context, $value2)');
    $v8_helper->CHECK(!$value2->equals($context, $value1), '!$value2->equals($context, $value1)');

    $v8_helper->CHECK(!$value1->strictEquals($value2), '!$value1->strictEquals($value2)');
    $v8_helper->CHECK(!$value2->strictEquals($value1), '!$value2->strictEquals($value1)');

    $v8_helper->CHECK(!$value1->sameValue($value2), '!$value1->sameValue($value2)');
    $v8_helper->CHECK(!$value2->sameValue($value1), '!$value2->sameValue($value1)');

    $helper->line();
});

$global_tpl = new \V8\ObjectTemplate($isolate);
$global_tpl->set(new \V8\StringValue($isolate, 'e'), $func_tpl);
$global_tpl->set(new \V8\StringValue($isolate, 'test'), $func_test_tpl);

$context = new \V8\Context($isolate, $global_tpl);
$v8_helper->injectConsoleLog($context);

$v8_helper->CompileTryRun($context, 'test()');
$v8_helper->CompileTryRun($context, 'e()');
$v8_helper->CompileTryRun($context, 'e("test")');

$source = '
    var ex;

    try {
        e("foo");
    } catch (exception) {
        console.log("exception: ", "\'", exception, "\'");
        console.log("exception.stack: ", exception.stack);
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
CHECK !$value1->equals($context, $value2): OK
CHECK !$value2->equals($context, $value1): OK
CHECK !$value1->strictEquals($value2): OK
CHECK !$value2->strictEquals($value1): OK
CHECK !$value1->sameValue($value2): OK
CHECK !$value2->sameValue($value1): OK

e(): V8\Exceptions\TryCatchException: TypeError: exception
e("test"): V8\Exceptions\TryCatchException: TypeError: test

exception: 'TypeError: foo'
exception.stack: TypeError: foo
    at test.js:5:9

Checks on V8\ObjectValue:
-------------------------
V8\ObjectValue(V8\Value)->typeOf(): V8\StringValue->value(): string(6) "object"

V8\ObjectValue->isCallable(): bool(false)
V8\ObjectValue->isConstructor(): bool(false)
V8\ObjectValue(V8\Value)->isUndefined(): bool(false)
V8\ObjectValue(V8\Value)->isNull(): bool(false)
V8\ObjectValue(V8\Value)->isNullOrUndefined(): bool(false)
V8\ObjectValue(V8\Value)->isTrue(): bool(false)
V8\ObjectValue(V8\Value)->isFalse(): bool(false)
V8\ObjectValue(V8\Value)->isName(): bool(false)
V8\ObjectValue(V8\Value)->isString(): bool(false)
V8\ObjectValue(V8\Value)->isSymbol(): bool(false)
V8\ObjectValue(V8\Value)->isFunction(): bool(false)
V8\ObjectValue(V8\Value)->isArray(): bool(false)
V8\ObjectValue(V8\Value)->isObject(): bool(true)
V8\ObjectValue(V8\Value)->isBoolean(): bool(false)
V8\ObjectValue(V8\Value)->isNumber(): bool(false)
V8\ObjectValue(V8\Value)->isInt32(): bool(false)
V8\ObjectValue(V8\Value)->isUint32(): bool(false)
V8\ObjectValue(V8\Value)->isDate(): bool(false)
V8\ObjectValue(V8\Value)->isArgumentsObject(): bool(false)
V8\ObjectValue(V8\Value)->isBooleanObject(): bool(false)
V8\ObjectValue(V8\Value)->isNumberObject(): bool(false)
V8\ObjectValue(V8\Value)->isStringObject(): bool(false)
V8\ObjectValue(V8\Value)->isSymbolObject(): bool(false)
V8\ObjectValue(V8\Value)->isNativeError(): bool(true)
V8\ObjectValue(V8\Value)->isRegExp(): bool(false)
V8\ObjectValue(V8\Value)->isAsyncFunction(): bool(false)
V8\ObjectValue(V8\Value)->isGeneratorFunction(): bool(false)
V8\ObjectValue(V8\Value)->isGeneratorObject(): bool(false)
V8\ObjectValue(V8\Value)->isPromise(): bool(false)
V8\ObjectValue(V8\Value)->isMap(): bool(false)
V8\ObjectValue(V8\Value)->isSet(): bool(false)
V8\ObjectValue(V8\Value)->isMapIterator(): bool(false)
V8\ObjectValue(V8\Value)->isSetIterator(): bool(false)
V8\ObjectValue(V8\Value)->isWeakMap(): bool(false)
V8\ObjectValue(V8\Value)->isWeakSet(): bool(false)
V8\ObjectValue(V8\Value)->isArrayBuffer(): bool(false)
V8\ObjectValue(V8\Value)->isArrayBufferView(): bool(false)
V8\ObjectValue(V8\Value)->isTypedArray(): bool(false)
V8\ObjectValue(V8\Value)->isUint8Array(): bool(false)
V8\ObjectValue(V8\Value)->isUint8ClampedArray(): bool(false)
V8\ObjectValue(V8\Value)->isInt8Array(): bool(false)
V8\ObjectValue(V8\Value)->isUint16Array(): bool(false)
V8\ObjectValue(V8\Value)->isInt16Array(): bool(false)
V8\ObjectValue(V8\Value)->isUint32Array(): bool(false)
V8\ObjectValue(V8\Value)->isInt32Array(): bool(false)
V8\ObjectValue(V8\Value)->isFloat32Array(): bool(false)
V8\ObjectValue(V8\Value)->isFloat64Array(): bool(false)
V8\ObjectValue(V8\Value)->isDataView(): bool(false)
V8\ObjectValue(V8\Value)->isSharedArrayBuffer(): bool(false)
V8\ObjectValue(V8\Value)->isProxy(): bool(false)
