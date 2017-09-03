--TEST--
V8\NullValue
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--ENV--
HOME=/tmp/we-need-home-env-var-set-to-load-valgrindrc
--FILE--
<?php

// Bootstraps:

$isolate = new V8\Isolate();
$value = new V8\NullValue($isolate);

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

// Tests:

$helper->header('Object representation');
$helper->dump($value);
$helper->space();

$helper->assert('NullValue extends PrimitiveValue', $value instanceof \V8\PrimitiveValue);
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

$string = $value->toString($context);

$helper->header(get_class($value) .'::toString() converting');
$helper->dump($string);
$helper->dump($string->value());

?>
--EXPECT--
Object representation:
----------------------
object(V8\NullValue)#2 (1) {
  ["isolate":"V8\Value":private]=>
  object(V8\Isolate)#1 (0) {
  }
}


NullValue extends PrimitiveValue: ok

Accessors:
----------
V8\NullValue::getIsolate() matches expected value
V8\NullValue->value(): NULL


Checkers:
---------
V8\NullValue(V8\Value)->typeOf(): V8\StringValue->value(): string(6) "object"

V8\NullValue(V8\Value)->isUndefined(): bool(false)
V8\NullValue(V8\Value)->isNull(): bool(true)
V8\NullValue(V8\Value)->isNullOrUndefined(): bool(true)
V8\NullValue(V8\Value)->isTrue(): bool(false)
V8\NullValue(V8\Value)->isFalse(): bool(false)
V8\NullValue(V8\Value)->isName(): bool(false)
V8\NullValue(V8\Value)->isString(): bool(false)
V8\NullValue(V8\Value)->isSymbol(): bool(false)
V8\NullValue(V8\Value)->isFunction(): bool(false)
V8\NullValue(V8\Value)->isArray(): bool(false)
V8\NullValue(V8\Value)->isObject(): bool(false)
V8\NullValue(V8\Value)->isBoolean(): bool(false)
V8\NullValue(V8\Value)->isNumber(): bool(false)
V8\NullValue(V8\Value)->isInt32(): bool(false)
V8\NullValue(V8\Value)->isUint32(): bool(false)
V8\NullValue(V8\Value)->isDate(): bool(false)
V8\NullValue(V8\Value)->isArgumentsObject(): bool(false)
V8\NullValue(V8\Value)->isBooleanObject(): bool(false)
V8\NullValue(V8\Value)->isNumberObject(): bool(false)
V8\NullValue(V8\Value)->isStringObject(): bool(false)
V8\NullValue(V8\Value)->isSymbolObject(): bool(false)
V8\NullValue(V8\Value)->isNativeError(): bool(false)
V8\NullValue(V8\Value)->isRegExp(): bool(false)
V8\NullValue(V8\Value)->isAsyncFunction(): bool(false)
V8\NullValue(V8\Value)->isGeneratorFunction(): bool(false)
V8\NullValue(V8\Value)->isGeneratorObject(): bool(false)
V8\NullValue(V8\Value)->isPromise(): bool(false)
V8\NullValue(V8\Value)->isMap(): bool(false)
V8\NullValue(V8\Value)->isSet(): bool(false)
V8\NullValue(V8\Value)->isMapIterator(): bool(false)
V8\NullValue(V8\Value)->isSetIterator(): bool(false)
V8\NullValue(V8\Value)->isWeakMap(): bool(false)
V8\NullValue(V8\Value)->isWeakSet(): bool(false)
V8\NullValue(V8\Value)->isArrayBuffer(): bool(false)
V8\NullValue(V8\Value)->isArrayBufferView(): bool(false)
V8\NullValue(V8\Value)->isTypedArray(): bool(false)
V8\NullValue(V8\Value)->isUint8Array(): bool(false)
V8\NullValue(V8\Value)->isUint8ClampedArray(): bool(false)
V8\NullValue(V8\Value)->isInt8Array(): bool(false)
V8\NullValue(V8\Value)->isUint16Array(): bool(false)
V8\NullValue(V8\Value)->isInt16Array(): bool(false)
V8\NullValue(V8\Value)->isUint32Array(): bool(false)
V8\NullValue(V8\Value)->isInt32Array(): bool(false)
V8\NullValue(V8\Value)->isFloat32Array(): bool(false)
V8\NullValue(V8\Value)->isFloat64Array(): bool(false)
V8\NullValue(V8\Value)->isDataView(): bool(false)
V8\NullValue(V8\Value)->isSharedArrayBuffer(): bool(false)
V8\NullValue(V8\Value)->isProxy(): bool(false)


Primitive converters:
---------------------
V8\NullValue(V8\Value)->booleanValue(): bool(false)
V8\NullValue(V8\Value)->numberValue(): float(0)


V8\NullValue::toString() converting:
------------------------------------
object(V8\StringValue)#79 (1) {
  ["isolate":"V8\Value":private]=>
  object(V8\Isolate)#1 (0) {
  }
}
string(4) "null"
