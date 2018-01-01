--TEST--
V8\NumberObject
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

// Tests:

$isolate = new \V8\Isolate();
$context = new V8\Context($isolate);
$v8_helper->injectConsoleLog($context);

$value = new V8\NumberObject($context, 42.12);

$helper->header('Object representation');
$helper->dump($value);
$helper->space();

$helper->assert('NumberObject extends ObjectValue', $value instanceof \V8\ObjectValue);
$helper->assert('NumberObject is instanceof Number', $value->instanceOf($context, $context->globalObject()->get($context, new \V8\StringValue($isolate, 'Number'))));
$helper->line();

$helper->header('Getters');
$helper->method_export($value, 'valueOf');
$helper->space();

$v8_helper->run_checks($value, 'Checkers');

$context->globalObject()->set($context, new \V8\StringValue($isolate, 'val'), $value);

$source    = '
console.log("val: ", val);
console.log("typeof val: ", typeof val);

val';
$file_name = 'test.js';

$script = new V8\Script($context, new \V8\StringValue($isolate, $source), new \V8\ScriptOrigin($file_name));
$res = $script->run($context);
$helper->space();

$helper->header('Returned value should be the same');
$helper->value_matches_with_no_output($res, $value);
$helper->space();

$source    = 'new Number(11.22);';
$file_name = 'test.js';

$script = new V8\Script($context, new \V8\StringValue($isolate, $source), new \V8\ScriptOrigin($file_name));
$res = $script->run($context);

$v8_helper->run_checks($res, 'Checkers on boxed from script');


?>
--EXPECT--
Object representation:
----------------------
object(V8\NumberObject)#6 (2) {
  ["isolate":"V8\Value":private]=>
  object(V8\Isolate)#3 (0) {
  }
  ["context":"V8\ObjectValue":private]=>
  object(V8\Context)#4 (1) {
    ["isolate":"V8\Context":private]=>
    object(V8\Isolate)#3 (0) {
    }
  }
}


NumberObject extends ObjectValue: ok
NumberObject is instanceof Number: ok

Getters:
--------
V8\NumberObject->valueOf(): float(42.12)


Checkers:
---------
V8\NumberObject(V8\Value)->typeOf(): V8\StringValue->value(): string(6) "object"

V8\NumberObject(V8\ObjectValue)->isCallable(): bool(false)
V8\NumberObject(V8\ObjectValue)->isConstructor(): bool(false)
V8\NumberObject(V8\Value)->isUndefined(): bool(false)
V8\NumberObject(V8\Value)->isNull(): bool(false)
V8\NumberObject(V8\Value)->isNullOrUndefined(): bool(false)
V8\NumberObject(V8\Value)->isTrue(): bool(false)
V8\NumberObject(V8\Value)->isFalse(): bool(false)
V8\NumberObject(V8\Value)->isName(): bool(false)
V8\NumberObject(V8\Value)->isString(): bool(false)
V8\NumberObject(V8\Value)->isSymbol(): bool(false)
V8\NumberObject(V8\Value)->isFunction(): bool(false)
V8\NumberObject(V8\Value)->isArray(): bool(false)
V8\NumberObject(V8\Value)->isObject(): bool(true)
V8\NumberObject(V8\Value)->isBoolean(): bool(false)
V8\NumberObject(V8\Value)->isNumber(): bool(false)
V8\NumberObject(V8\Value)->isInt32(): bool(false)
V8\NumberObject(V8\Value)->isUint32(): bool(false)
V8\NumberObject(V8\Value)->isDate(): bool(false)
V8\NumberObject(V8\Value)->isArgumentsObject(): bool(false)
V8\NumberObject(V8\Value)->isBooleanObject(): bool(false)
V8\NumberObject(V8\Value)->isNumberObject(): bool(true)
V8\NumberObject(V8\Value)->isStringObject(): bool(false)
V8\NumberObject(V8\Value)->isSymbolObject(): bool(false)
V8\NumberObject(V8\Value)->isNativeError(): bool(false)
V8\NumberObject(V8\Value)->isRegExp(): bool(false)
V8\NumberObject(V8\Value)->isAsyncFunction(): bool(false)
V8\NumberObject(V8\Value)->isGeneratorFunction(): bool(false)
V8\NumberObject(V8\Value)->isGeneratorObject(): bool(false)
V8\NumberObject(V8\Value)->isPromise(): bool(false)
V8\NumberObject(V8\Value)->isMap(): bool(false)
V8\NumberObject(V8\Value)->isSet(): bool(false)
V8\NumberObject(V8\Value)->isMapIterator(): bool(false)
V8\NumberObject(V8\Value)->isSetIterator(): bool(false)
V8\NumberObject(V8\Value)->isWeakMap(): bool(false)
V8\NumberObject(V8\Value)->isWeakSet(): bool(false)
V8\NumberObject(V8\Value)->isArrayBuffer(): bool(false)
V8\NumberObject(V8\Value)->isArrayBufferView(): bool(false)
V8\NumberObject(V8\Value)->isTypedArray(): bool(false)
V8\NumberObject(V8\Value)->isUint8Array(): bool(false)
V8\NumberObject(V8\Value)->isUint8ClampedArray(): bool(false)
V8\NumberObject(V8\Value)->isInt8Array(): bool(false)
V8\NumberObject(V8\Value)->isUint16Array(): bool(false)
V8\NumberObject(V8\Value)->isInt16Array(): bool(false)
V8\NumberObject(V8\Value)->isUint32Array(): bool(false)
V8\NumberObject(V8\Value)->isInt32Array(): bool(false)
V8\NumberObject(V8\Value)->isFloat32Array(): bool(false)
V8\NumberObject(V8\Value)->isFloat64Array(): bool(false)
V8\NumberObject(V8\Value)->isDataView(): bool(false)
V8\NumberObject(V8\Value)->isSharedArrayBuffer(): bool(false)
V8\NumberObject(V8\Value)->isProxy(): bool(false)


val: 42.12
typeof val: object


Returned value should be the same:
----------------------------------
Expected value is identical to actual value


Checkers on boxed from script:
------------------------------
V8\NumberObject(V8\Value)->typeOf(): V8\StringValue->value(): string(6) "object"

V8\NumberObject(V8\ObjectValue)->isCallable(): bool(false)
V8\NumberObject(V8\ObjectValue)->isConstructor(): bool(false)
V8\NumberObject(V8\Value)->isUndefined(): bool(false)
V8\NumberObject(V8\Value)->isNull(): bool(false)
V8\NumberObject(V8\Value)->isNullOrUndefined(): bool(false)
V8\NumberObject(V8\Value)->isTrue(): bool(false)
V8\NumberObject(V8\Value)->isFalse(): bool(false)
V8\NumberObject(V8\Value)->isName(): bool(false)
V8\NumberObject(V8\Value)->isString(): bool(false)
V8\NumberObject(V8\Value)->isSymbol(): bool(false)
V8\NumberObject(V8\Value)->isFunction(): bool(false)
V8\NumberObject(V8\Value)->isArray(): bool(false)
V8\NumberObject(V8\Value)->isObject(): bool(true)
V8\NumberObject(V8\Value)->isBoolean(): bool(false)
V8\NumberObject(V8\Value)->isNumber(): bool(false)
V8\NumberObject(V8\Value)->isInt32(): bool(false)
V8\NumberObject(V8\Value)->isUint32(): bool(false)
V8\NumberObject(V8\Value)->isDate(): bool(false)
V8\NumberObject(V8\Value)->isArgumentsObject(): bool(false)
V8\NumberObject(V8\Value)->isBooleanObject(): bool(false)
V8\NumberObject(V8\Value)->isNumberObject(): bool(true)
V8\NumberObject(V8\Value)->isStringObject(): bool(false)
V8\NumberObject(V8\Value)->isSymbolObject(): bool(false)
V8\NumberObject(V8\Value)->isNativeError(): bool(false)
V8\NumberObject(V8\Value)->isRegExp(): bool(false)
V8\NumberObject(V8\Value)->isAsyncFunction(): bool(false)
V8\NumberObject(V8\Value)->isGeneratorFunction(): bool(false)
V8\NumberObject(V8\Value)->isGeneratorObject(): bool(false)
V8\NumberObject(V8\Value)->isPromise(): bool(false)
V8\NumberObject(V8\Value)->isMap(): bool(false)
V8\NumberObject(V8\Value)->isSet(): bool(false)
V8\NumberObject(V8\Value)->isMapIterator(): bool(false)
V8\NumberObject(V8\Value)->isSetIterator(): bool(false)
V8\NumberObject(V8\Value)->isWeakMap(): bool(false)
V8\NumberObject(V8\Value)->isWeakSet(): bool(false)
V8\NumberObject(V8\Value)->isArrayBuffer(): bool(false)
V8\NumberObject(V8\Value)->isArrayBufferView(): bool(false)
V8\NumberObject(V8\Value)->isTypedArray(): bool(false)
V8\NumberObject(V8\Value)->isUint8Array(): bool(false)
V8\NumberObject(V8\Value)->isUint8ClampedArray(): bool(false)
V8\NumberObject(V8\Value)->isInt8Array(): bool(false)
V8\NumberObject(V8\Value)->isUint16Array(): bool(false)
V8\NumberObject(V8\Value)->isInt16Array(): bool(false)
V8\NumberObject(V8\Value)->isUint32Array(): bool(false)
V8\NumberObject(V8\Value)->isInt32Array(): bool(false)
V8\NumberObject(V8\Value)->isFloat32Array(): bool(false)
V8\NumberObject(V8\Value)->isFloat64Array(): bool(false)
V8\NumberObject(V8\Value)->isDataView(): bool(false)
V8\NumberObject(V8\Value)->isSharedArrayBuffer(): bool(false)
V8\NumberObject(V8\Value)->isProxy(): bool(false)
