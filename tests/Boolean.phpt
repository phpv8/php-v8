--TEST--
V8\BooleanValue
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

// Bootstraps:

$isolate = new V8\Isolate();
$value = new V8\BooleanValue($isolate, true);

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

// Tests:

$helper->header('Object representation');
$helper->dump($value);
$helper->space();

$helper->assert('BooleanValue extends PrimitiveValue', $value instanceof \V8\PrimitiveValue);
$helper->line();

$helper->header('Accessors');
$helper->method_matches($value, 'getIsolate', $isolate);
$helper->method_export($value, 'value');
$helper->space();


$v8_helper->run_checks($value, 'Checkers');

$global_template = new \V8\ObjectTemplate($isolate);
$context = new \V8\Context($isolate, $global_template);


$helper->header('Primitive converters');
$helper->method_export($value, 'booleanValue', [$context]);
$helper->method_export($value, 'numberValue', [$context]);
$helper->space();


$helper->header(get_class($value) .'::toString() converting');
$string = $value->toString($context);
$helper->dump($string->value());
$helper->space();


?>
--EXPECT--
Object representation:
----------------------
object(V8\BooleanValue)#2 (1) {
  ["isolate":"V8\Value":private]=>
  object(V8\Isolate)#1 (0) {
  }
}


BooleanValue extends PrimitiveValue: ok

Accessors:
----------
V8\BooleanValue::getIsolate() matches expected value
V8\BooleanValue->value(): bool(true)


Checkers:
---------
V8\BooleanValue(V8\Value)->typeOf(): V8\StringValue->value(): string(7) "boolean"

V8\BooleanValue(V8\Value)->isUndefined(): bool(false)
V8\BooleanValue(V8\Value)->isNull(): bool(false)
V8\BooleanValue(V8\Value)->isNullOrUndefined(): bool(false)
V8\BooleanValue(V8\Value)->isTrue(): bool(true)
V8\BooleanValue(V8\Value)->isFalse(): bool(false)
V8\BooleanValue(V8\Value)->isName(): bool(false)
V8\BooleanValue(V8\Value)->isString(): bool(false)
V8\BooleanValue(V8\Value)->isSymbol(): bool(false)
V8\BooleanValue(V8\Value)->isFunction(): bool(false)
V8\BooleanValue(V8\Value)->isArray(): bool(false)
V8\BooleanValue(V8\Value)->isObject(): bool(false)
V8\BooleanValue(V8\Value)->isBoolean(): bool(true)
V8\BooleanValue(V8\Value)->isNumber(): bool(false)
V8\BooleanValue(V8\Value)->isInt32(): bool(false)
V8\BooleanValue(V8\Value)->isUint32(): bool(false)
V8\BooleanValue(V8\Value)->isDate(): bool(false)
V8\BooleanValue(V8\Value)->isArgumentsObject(): bool(false)
V8\BooleanValue(V8\Value)->isBooleanObject(): bool(false)
V8\BooleanValue(V8\Value)->isNumberObject(): bool(false)
V8\BooleanValue(V8\Value)->isStringObject(): bool(false)
V8\BooleanValue(V8\Value)->isSymbolObject(): bool(false)
V8\BooleanValue(V8\Value)->isNativeError(): bool(false)
V8\BooleanValue(V8\Value)->isRegExp(): bool(false)
V8\BooleanValue(V8\Value)->isAsyncFunction(): bool(false)
V8\BooleanValue(V8\Value)->isGeneratorFunction(): bool(false)
V8\BooleanValue(V8\Value)->isGeneratorObject(): bool(false)
V8\BooleanValue(V8\Value)->isPromise(): bool(false)
V8\BooleanValue(V8\Value)->isMap(): bool(false)
V8\BooleanValue(V8\Value)->isSet(): bool(false)
V8\BooleanValue(V8\Value)->isMapIterator(): bool(false)
V8\BooleanValue(V8\Value)->isSetIterator(): bool(false)
V8\BooleanValue(V8\Value)->isWeakMap(): bool(false)
V8\BooleanValue(V8\Value)->isWeakSet(): bool(false)
V8\BooleanValue(V8\Value)->isArrayBuffer(): bool(false)
V8\BooleanValue(V8\Value)->isArrayBufferView(): bool(false)
V8\BooleanValue(V8\Value)->isTypedArray(): bool(false)
V8\BooleanValue(V8\Value)->isUint8Array(): bool(false)
V8\BooleanValue(V8\Value)->isUint8ClampedArray(): bool(false)
V8\BooleanValue(V8\Value)->isInt8Array(): bool(false)
V8\BooleanValue(V8\Value)->isUint16Array(): bool(false)
V8\BooleanValue(V8\Value)->isInt16Array(): bool(false)
V8\BooleanValue(V8\Value)->isUint32Array(): bool(false)
V8\BooleanValue(V8\Value)->isInt32Array(): bool(false)
V8\BooleanValue(V8\Value)->isFloat32Array(): bool(false)
V8\BooleanValue(V8\Value)->isFloat64Array(): bool(false)
V8\BooleanValue(V8\Value)->isBigInt64Array(): bool(false)
V8\BooleanValue(V8\Value)->isBigUint64Array(): bool(false)
V8\BooleanValue(V8\Value)->isDataView(): bool(false)
V8\BooleanValue(V8\Value)->isSharedArrayBuffer(): bool(false)
V8\BooleanValue(V8\Value)->isProxy(): bool(false)


Primitive converters:
---------------------
V8\BooleanValue(V8\Value)->booleanValue(): bool(true)
V8\BooleanValue(V8\Value)->numberValue(): float(1)


V8\BooleanValue::toString() converting:
---------------------------------------
string(4) "true"
