--TEST--
V8\IntegerValue
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

// Bootstraps:

$isolate = new V8\Isolate();
$value = new V8\IntegerValue($isolate, 123.456);

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

// Tests:

$helper->header('Object representation');
$helper->dump($value);
$helper->space();

$helper->assert('IntegerValue extends NumberValue', $value instanceof \V8\NumberValue);
$helper->line();

$helper->header('Accessors');
$helper->method_matches($value, 'getIsolate', $isolate);
$helper->method_export($value, 'value');
$helper->space();


$v8_helper->run_checks($value, 'Checkers');


$global_template = new \V8\ObjectTemplate($isolate);
$context = new \V8\Context($isolate, $global_template);


$string = $value->toString($context);

$helper->header(get_class($value) .'::toString() converting');
$helper->dump($string);
$helper->dump($string->value());
$helper->space();


$helper->header('Primitive converters');
$helper->method_export($value, 'booleanValue', [$context]);
$helper->method_export($value, 'numberValue', [$context]);
$helper->space();


$helper->header('Test negative value in constructor');
$value = new V8\IntegerValue($isolate, -123.456);
$helper->method_export($value, 'value');
$helper->method_export($value, 'booleanValue', [$context]);
$helper->method_export($value, 'numberValue', [$context]);
$helper->space();

$v8_helper->run_checks($value, 'Checkers for negative');

$helper->header('Integer is int32, so test for out-of-range (INT32_MIN-INT32_MAX)');


foreach ([PHP_INT_MAX, -PHP_INT_MAX, NAN, INF, -INF] as $val) {
  $helper->value_export($val);
  try {
    $value = new V8\IntegerValue($isolate, $val);
    $helper->method_export($value, 'value');
  } catch (Throwable $e) {
    $helper->exception_export($e);
  }
  $helper->space();
}


?>
--EXPECT--
Object representation:
----------------------
object(V8\IntegerValue)#2 (1) {
  ["isolate":"V8\Value":private]=>
  object(V8\Isolate)#1 (0) {
  }
}


IntegerValue extends NumberValue: ok

Accessors:
----------
V8\IntegerValue::getIsolate() matches expected value
V8\IntegerValue->value(): int(123)


Checkers:
---------
V8\IntegerValue(V8\Value)->typeOf(): V8\StringValue->value(): string(6) "number"

V8\IntegerValue(V8\Value)->isUndefined(): bool(false)
V8\IntegerValue(V8\Value)->isNull(): bool(false)
V8\IntegerValue(V8\Value)->isNullOrUndefined(): bool(false)
V8\IntegerValue(V8\Value)->isTrue(): bool(false)
V8\IntegerValue(V8\Value)->isFalse(): bool(false)
V8\IntegerValue(V8\Value)->isName(): bool(false)
V8\IntegerValue(V8\Value)->isString(): bool(false)
V8\IntegerValue(V8\Value)->isSymbol(): bool(false)
V8\IntegerValue(V8\Value)->isFunction(): bool(false)
V8\IntegerValue(V8\Value)->isArray(): bool(false)
V8\IntegerValue(V8\Value)->isObject(): bool(false)
V8\IntegerValue(V8\Value)->isBoolean(): bool(false)
V8\IntegerValue(V8\Value)->isNumber(): bool(true)
V8\IntegerValue(V8\Value)->isInt32(): bool(true)
V8\IntegerValue(V8\Value)->isUint32(): bool(true)
V8\IntegerValue(V8\Value)->isDate(): bool(false)
V8\IntegerValue(V8\Value)->isArgumentsObject(): bool(false)
V8\IntegerValue(V8\Value)->isBooleanObject(): bool(false)
V8\IntegerValue(V8\Value)->isNumberObject(): bool(false)
V8\IntegerValue(V8\Value)->isStringObject(): bool(false)
V8\IntegerValue(V8\Value)->isSymbolObject(): bool(false)
V8\IntegerValue(V8\Value)->isNativeError(): bool(false)
V8\IntegerValue(V8\Value)->isRegExp(): bool(false)
V8\IntegerValue(V8\Value)->isAsyncFunction(): bool(false)
V8\IntegerValue(V8\Value)->isGeneratorFunction(): bool(false)
V8\IntegerValue(V8\Value)->isGeneratorObject(): bool(false)
V8\IntegerValue(V8\Value)->isPromise(): bool(false)
V8\IntegerValue(V8\Value)->isMap(): bool(false)
V8\IntegerValue(V8\Value)->isSet(): bool(false)
V8\IntegerValue(V8\Value)->isMapIterator(): bool(false)
V8\IntegerValue(V8\Value)->isSetIterator(): bool(false)
V8\IntegerValue(V8\Value)->isWeakMap(): bool(false)
V8\IntegerValue(V8\Value)->isWeakSet(): bool(false)
V8\IntegerValue(V8\Value)->isArrayBuffer(): bool(false)
V8\IntegerValue(V8\Value)->isArrayBufferView(): bool(false)
V8\IntegerValue(V8\Value)->isTypedArray(): bool(false)
V8\IntegerValue(V8\Value)->isUint8Array(): bool(false)
V8\IntegerValue(V8\Value)->isUint8ClampedArray(): bool(false)
V8\IntegerValue(V8\Value)->isInt8Array(): bool(false)
V8\IntegerValue(V8\Value)->isUint16Array(): bool(false)
V8\IntegerValue(V8\Value)->isInt16Array(): bool(false)
V8\IntegerValue(V8\Value)->isUint32Array(): bool(false)
V8\IntegerValue(V8\Value)->isInt32Array(): bool(false)
V8\IntegerValue(V8\Value)->isFloat32Array(): bool(false)
V8\IntegerValue(V8\Value)->isFloat64Array(): bool(false)
V8\IntegerValue(V8\Value)->isDataView(): bool(false)
V8\IntegerValue(V8\Value)->isSharedArrayBuffer(): bool(false)
V8\IntegerValue(V8\Value)->isProxy(): bool(false)


V8\IntegerValue::toString() converting:
---------------------------------------
object(V8\StringValue)#79 (1) {
  ["isolate":"V8\Value":private]=>
  object(V8\Isolate)#1 (0) {
  }
}
string(3) "123"


Primitive converters:
---------------------
V8\IntegerValue(V8\Value)->booleanValue(): bool(true)
V8\IntegerValue(V8\Value)->numberValue(): float(123)


Test negative value in constructor:
-----------------------------------
V8\IntegerValue->value(): int(-123)
V8\IntegerValue(V8\Value)->booleanValue(): bool(true)
V8\IntegerValue(V8\Value)->numberValue(): float(-123)


Checkers for negative:
----------------------
V8\IntegerValue(V8\Value)->typeOf(): V8\StringValue->value(): string(6) "number"

V8\IntegerValue(V8\Value)->isUndefined(): bool(false)
V8\IntegerValue(V8\Value)->isNull(): bool(false)
V8\IntegerValue(V8\Value)->isNullOrUndefined(): bool(false)
V8\IntegerValue(V8\Value)->isTrue(): bool(false)
V8\IntegerValue(V8\Value)->isFalse(): bool(false)
V8\IntegerValue(V8\Value)->isName(): bool(false)
V8\IntegerValue(V8\Value)->isString(): bool(false)
V8\IntegerValue(V8\Value)->isSymbol(): bool(false)
V8\IntegerValue(V8\Value)->isFunction(): bool(false)
V8\IntegerValue(V8\Value)->isArray(): bool(false)
V8\IntegerValue(V8\Value)->isObject(): bool(false)
V8\IntegerValue(V8\Value)->isBoolean(): bool(false)
V8\IntegerValue(V8\Value)->isNumber(): bool(true)
V8\IntegerValue(V8\Value)->isInt32(): bool(true)
V8\IntegerValue(V8\Value)->isUint32(): bool(false)
V8\IntegerValue(V8\Value)->isDate(): bool(false)
V8\IntegerValue(V8\Value)->isArgumentsObject(): bool(false)
V8\IntegerValue(V8\Value)->isBooleanObject(): bool(false)
V8\IntegerValue(V8\Value)->isNumberObject(): bool(false)
V8\IntegerValue(V8\Value)->isStringObject(): bool(false)
V8\IntegerValue(V8\Value)->isSymbolObject(): bool(false)
V8\IntegerValue(V8\Value)->isNativeError(): bool(false)
V8\IntegerValue(V8\Value)->isRegExp(): bool(false)
V8\IntegerValue(V8\Value)->isAsyncFunction(): bool(false)
V8\IntegerValue(V8\Value)->isGeneratorFunction(): bool(false)
V8\IntegerValue(V8\Value)->isGeneratorObject(): bool(false)
V8\IntegerValue(V8\Value)->isPromise(): bool(false)
V8\IntegerValue(V8\Value)->isMap(): bool(false)
V8\IntegerValue(V8\Value)->isSet(): bool(false)
V8\IntegerValue(V8\Value)->isMapIterator(): bool(false)
V8\IntegerValue(V8\Value)->isSetIterator(): bool(false)
V8\IntegerValue(V8\Value)->isWeakMap(): bool(false)
V8\IntegerValue(V8\Value)->isWeakSet(): bool(false)
V8\IntegerValue(V8\Value)->isArrayBuffer(): bool(false)
V8\IntegerValue(V8\Value)->isArrayBufferView(): bool(false)
V8\IntegerValue(V8\Value)->isTypedArray(): bool(false)
V8\IntegerValue(V8\Value)->isUint8Array(): bool(false)
V8\IntegerValue(V8\Value)->isUint8ClampedArray(): bool(false)
V8\IntegerValue(V8\Value)->isInt8Array(): bool(false)
V8\IntegerValue(V8\Value)->isUint16Array(): bool(false)
V8\IntegerValue(V8\Value)->isInt16Array(): bool(false)
V8\IntegerValue(V8\Value)->isUint32Array(): bool(false)
V8\IntegerValue(V8\Value)->isInt32Array(): bool(false)
V8\IntegerValue(V8\Value)->isFloat32Array(): bool(false)
V8\IntegerValue(V8\Value)->isFloat64Array(): bool(false)
V8\IntegerValue(V8\Value)->isDataView(): bool(false)
V8\IntegerValue(V8\Value)->isSharedArrayBuffer(): bool(false)
V8\IntegerValue(V8\Value)->isProxy(): bool(false)


Integer is int32, so test for out-of-range (INT32_MIN-INT32_MAX):
-----------------------------------------------------------------
integer: 9223372036854775807
V8\Exceptions\ValueException: Integer value to set is out of range


integer: -9223372036854775807
V8\Exceptions\ValueException: Integer value to set is out of range


double: NAN
TypeError: Argument 2 passed to V8\IntegerValue::__construct() must be of the type integer, float given


double: INF
TypeError: Argument 2 passed to V8\IntegerValue::__construct() must be of the type integer, float given


double: -INF
TypeError: Argument 2 passed to V8\IntegerValue::__construct() must be of the type integer, float given
