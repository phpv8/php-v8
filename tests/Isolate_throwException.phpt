--TEST--
V8\Isolate::throwException()
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

$func_tpl = new \V8\FunctionTemplate($isolate, function (\V8\FunctionCallbackInfo $info) {
    $value = count($info->arguments()) ? $info->arguments()[0] : new \V8\StringValue($info->getIsolate(), "exception");

    $info->getIsolate()->throwException($info->getContext(), $value);
});


$global_tpl = new \V8\ObjectTemplate($isolate);
$global_tpl->set(new \V8\StringValue($isolate, 'e'), $func_tpl);

$context = new \V8\Context($isolate, $global_tpl);
$v8_helper->injectConsoleLog($context);


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
$v8_helper->CHECK($res->strictEquals(new \V8\StringValue($isolate, 'foo')), '$res->strictEquals(new \V8\StringValue($isolate, \'foo\'))');
$helper->line();

$v8_helper->run_checks($res);


$source = '
    var ex;

    try {
        e({});
    } catch (exception) {
        console.log("exception: ", "\'", exception, "\'");
        console.log("exception.stack: ", exception.stack);
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
        console.log("exception: ", "\'", exception, "\'");
        console.log("exception.stack: ", exception.stack);
        ex = exception;
    }

    ex
';

$res = $v8_helper->CompileRun($context, $source);
$helper->line();

$v8_helper->run_checks($res);


?>
--EXPECT--
e(): V8\Exceptions\TryCatchException: exception
e("test"): V8\Exceptions\TryCatchException: test

exception: 'foo'
exception.stack: <undefined>

CHECK $res->strictEquals(new \V8\StringValue($isolate, 'foo')): OK

Checks on V8\StringValue:
-------------------------
V8\StringValue(V8\Value)->typeOf(): V8\StringValue->value(): string(6) "string"

V8\StringValue->isOneByte(): bool(true)
V8\StringValue(V8\Value)->isUndefined(): bool(false)
V8\StringValue(V8\Value)->isNull(): bool(false)
V8\StringValue(V8\Value)->isNullOrUndefined(): bool(false)
V8\StringValue(V8\Value)->isTrue(): bool(false)
V8\StringValue(V8\Value)->isFalse(): bool(false)
V8\StringValue(V8\Value)->isName(): bool(true)
V8\StringValue(V8\Value)->isString(): bool(true)
V8\StringValue(V8\Value)->isSymbol(): bool(false)
V8\StringValue(V8\Value)->isFunction(): bool(false)
V8\StringValue(V8\Value)->isArray(): bool(false)
V8\StringValue(V8\Value)->isObject(): bool(false)
V8\StringValue(V8\Value)->isBoolean(): bool(false)
V8\StringValue(V8\Value)->isNumber(): bool(false)
V8\StringValue(V8\Value)->isInt32(): bool(false)
V8\StringValue(V8\Value)->isUint32(): bool(false)
V8\StringValue(V8\Value)->isDate(): bool(false)
V8\StringValue(V8\Value)->isArgumentsObject(): bool(false)
V8\StringValue(V8\Value)->isBooleanObject(): bool(false)
V8\StringValue(V8\Value)->isNumberObject(): bool(false)
V8\StringValue(V8\Value)->isStringObject(): bool(false)
V8\StringValue(V8\Value)->isSymbolObject(): bool(false)
V8\StringValue(V8\Value)->isNativeError(): bool(false)
V8\StringValue(V8\Value)->isRegExp(): bool(false)
V8\StringValue(V8\Value)->isAsyncFunction(): bool(false)
V8\StringValue(V8\Value)->isGeneratorFunction(): bool(false)
V8\StringValue(V8\Value)->isGeneratorObject(): bool(false)
V8\StringValue(V8\Value)->isPromise(): bool(false)
V8\StringValue(V8\Value)->isMap(): bool(false)
V8\StringValue(V8\Value)->isSet(): bool(false)
V8\StringValue(V8\Value)->isMapIterator(): bool(false)
V8\StringValue(V8\Value)->isSetIterator(): bool(false)
V8\StringValue(V8\Value)->isWeakMap(): bool(false)
V8\StringValue(V8\Value)->isWeakSet(): bool(false)
V8\StringValue(V8\Value)->isArrayBuffer(): bool(false)
V8\StringValue(V8\Value)->isArrayBufferView(): bool(false)
V8\StringValue(V8\Value)->isTypedArray(): bool(false)
V8\StringValue(V8\Value)->isUint8Array(): bool(false)
V8\StringValue(V8\Value)->isUint8ClampedArray(): bool(false)
V8\StringValue(V8\Value)->isInt8Array(): bool(false)
V8\StringValue(V8\Value)->isUint16Array(): bool(false)
V8\StringValue(V8\Value)->isInt16Array(): bool(false)
V8\StringValue(V8\Value)->isUint32Array(): bool(false)
V8\StringValue(V8\Value)->isInt32Array(): bool(false)
V8\StringValue(V8\Value)->isFloat32Array(): bool(false)
V8\StringValue(V8\Value)->isFloat64Array(): bool(false)
V8\StringValue(V8\Value)->isDataView(): bool(false)
V8\StringValue(V8\Value)->isSharedArrayBuffer(): bool(false)
V8\StringValue(V8\Value)->isProxy(): bool(false)


exception: '[object Object]'
exception.stack: <undefined>

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
V8\ObjectValue(V8\Value)->isNativeError(): bool(false)
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


exception: 'Error'
exception.stack: Error
    at test.js:5:15

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
