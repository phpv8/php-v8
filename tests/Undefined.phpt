--TEST--
V8\Undefined
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

// Tests:


$isolate = new V8\Isolate();
$context = new V8\Context($isolate);
$value = new V8\UndefinedValue($isolate);


$helper->header('Object representation');
$helper->dump($value);
$helper->space();

$helper->assert('UndefinedValue extends PrimitiveValue', $value instanceof \V8\PrimitiveValue);
$helper->assert('UndefinedValue extends Value', $value instanceof \V8\Value);
$helper->assert('TypeOf returns StringValue', $value->typeOf() instanceof \V8\StringValue);
$helper->line();

$helper->header('InstanceOf');
try {
    $value->instanceOf($context, new \V8\ObjectValue($context));
} catch (\V8\Exceptions\TryCatchException $e) {
    $helper->exception_export($e);
}
$helper->assert('Default Value is not an instance of Function', !$value->instanceOf($context, new \V8\FunctionObject($context, function(){})));
$helper->line();

$helper->header('Accessors');
$helper->method_matches($value, 'getIsolate', $isolate);
$helper->method_export($value, 'value');
$helper->space();

$v8_helper->run_checks($value);

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
$helper->space();

$v8_helper->run_checks($value, 'Checkers after ToString() converting');

$helper->header(get_class($value) .'::toObject() converting');
try {
  $object = $value->toObject($context);
} catch (Exception $e) {
  $helper->exception_export($e);
}
$helper->space();


?>
--EXPECT--
Object representation:
----------------------
object(V8\UndefinedValue)#5 (1) {
  ["isolate":"V8\Value":private]=>
  object(V8\Isolate)#3 (0) {
  }
}


UndefinedValue extends PrimitiveValue: ok
UndefinedValue extends Value: ok
TypeOf returns StringValue: ok

InstanceOf:
-----------
V8\Exceptions\TryCatchException: TypeError: Right-hand side of 'instanceof' is not callable
Default Value is not an instance of Function: ok

Accessors:
----------
V8\UndefinedValue::getIsolate() matches expected value
V8\UndefinedValue->value(): NULL


Checks on V8\UndefinedValue:
----------------------------
V8\UndefinedValue(V8\Value)->typeOf(): V8\StringValue->value(): string(9) "undefined"

V8\UndefinedValue(V8\Value)->isUndefined(): bool(true)
V8\UndefinedValue(V8\Value)->isNull(): bool(false)
V8\UndefinedValue(V8\Value)->isNullOrUndefined(): bool(true)
V8\UndefinedValue(V8\Value)->isTrue(): bool(false)
V8\UndefinedValue(V8\Value)->isFalse(): bool(false)
V8\UndefinedValue(V8\Value)->isName(): bool(false)
V8\UndefinedValue(V8\Value)->isString(): bool(false)
V8\UndefinedValue(V8\Value)->isSymbol(): bool(false)
V8\UndefinedValue(V8\Value)->isFunction(): bool(false)
V8\UndefinedValue(V8\Value)->isArray(): bool(false)
V8\UndefinedValue(V8\Value)->isObject(): bool(false)
V8\UndefinedValue(V8\Value)->isBoolean(): bool(false)
V8\UndefinedValue(V8\Value)->isNumber(): bool(false)
V8\UndefinedValue(V8\Value)->isInt32(): bool(false)
V8\UndefinedValue(V8\Value)->isUint32(): bool(false)
V8\UndefinedValue(V8\Value)->isDate(): bool(false)
V8\UndefinedValue(V8\Value)->isArgumentsObject(): bool(false)
V8\UndefinedValue(V8\Value)->isBooleanObject(): bool(false)
V8\UndefinedValue(V8\Value)->isNumberObject(): bool(false)
V8\UndefinedValue(V8\Value)->isStringObject(): bool(false)
V8\UndefinedValue(V8\Value)->isSymbolObject(): bool(false)
V8\UndefinedValue(V8\Value)->isNativeError(): bool(false)
V8\UndefinedValue(V8\Value)->isRegExp(): bool(false)
V8\UndefinedValue(V8\Value)->isAsyncFunction(): bool(false)
V8\UndefinedValue(V8\Value)->isGeneratorFunction(): bool(false)
V8\UndefinedValue(V8\Value)->isGeneratorObject(): bool(false)
V8\UndefinedValue(V8\Value)->isPromise(): bool(false)
V8\UndefinedValue(V8\Value)->isMap(): bool(false)
V8\UndefinedValue(V8\Value)->isSet(): bool(false)
V8\UndefinedValue(V8\Value)->isMapIterator(): bool(false)
V8\UndefinedValue(V8\Value)->isSetIterator(): bool(false)
V8\UndefinedValue(V8\Value)->isWeakMap(): bool(false)
V8\UndefinedValue(V8\Value)->isWeakSet(): bool(false)
V8\UndefinedValue(V8\Value)->isArrayBuffer(): bool(false)
V8\UndefinedValue(V8\Value)->isArrayBufferView(): bool(false)
V8\UndefinedValue(V8\Value)->isTypedArray(): bool(false)
V8\UndefinedValue(V8\Value)->isUint8Array(): bool(false)
V8\UndefinedValue(V8\Value)->isUint8ClampedArray(): bool(false)
V8\UndefinedValue(V8\Value)->isInt8Array(): bool(false)
V8\UndefinedValue(V8\Value)->isUint16Array(): bool(false)
V8\UndefinedValue(V8\Value)->isInt16Array(): bool(false)
V8\UndefinedValue(V8\Value)->isUint32Array(): bool(false)
V8\UndefinedValue(V8\Value)->isInt32Array(): bool(false)
V8\UndefinedValue(V8\Value)->isFloat32Array(): bool(false)
V8\UndefinedValue(V8\Value)->isFloat64Array(): bool(false)
V8\UndefinedValue(V8\Value)->isBigInt64Array(): bool(false)
V8\UndefinedValue(V8\Value)->isBigUint64Array(): bool(false)
V8\UndefinedValue(V8\Value)->isDataView(): bool(false)
V8\UndefinedValue(V8\Value)->isSharedArrayBuffer(): bool(false)
V8\UndefinedValue(V8\Value)->isProxy(): bool(false)


Primitive converters:
---------------------
V8\UndefinedValue(V8\Value)->booleanValue(): bool(false)
V8\UndefinedValue(V8\Value)->numberValue(): float(NAN)


V8\UndefinedValue::toString() converting:
-----------------------------------------
object(V8\StringValue)#91 (1) {
  ["isolate":"V8\Value":private]=>
  object(V8\Isolate)#3 (0) {
  }
}
string(9) "undefined"


Checkers after ToString() converting:
-------------------------------------
V8\UndefinedValue(V8\Value)->typeOf(): V8\StringValue->value(): string(9) "undefined"

V8\UndefinedValue(V8\Value)->isUndefined(): bool(true)
V8\UndefinedValue(V8\Value)->isNull(): bool(false)
V8\UndefinedValue(V8\Value)->isNullOrUndefined(): bool(true)
V8\UndefinedValue(V8\Value)->isTrue(): bool(false)
V8\UndefinedValue(V8\Value)->isFalse(): bool(false)
V8\UndefinedValue(V8\Value)->isName(): bool(false)
V8\UndefinedValue(V8\Value)->isString(): bool(false)
V8\UndefinedValue(V8\Value)->isSymbol(): bool(false)
V8\UndefinedValue(V8\Value)->isFunction(): bool(false)
V8\UndefinedValue(V8\Value)->isArray(): bool(false)
V8\UndefinedValue(V8\Value)->isObject(): bool(false)
V8\UndefinedValue(V8\Value)->isBoolean(): bool(false)
V8\UndefinedValue(V8\Value)->isNumber(): bool(false)
V8\UndefinedValue(V8\Value)->isInt32(): bool(false)
V8\UndefinedValue(V8\Value)->isUint32(): bool(false)
V8\UndefinedValue(V8\Value)->isDate(): bool(false)
V8\UndefinedValue(V8\Value)->isArgumentsObject(): bool(false)
V8\UndefinedValue(V8\Value)->isBooleanObject(): bool(false)
V8\UndefinedValue(V8\Value)->isNumberObject(): bool(false)
V8\UndefinedValue(V8\Value)->isStringObject(): bool(false)
V8\UndefinedValue(V8\Value)->isSymbolObject(): bool(false)
V8\UndefinedValue(V8\Value)->isNativeError(): bool(false)
V8\UndefinedValue(V8\Value)->isRegExp(): bool(false)
V8\UndefinedValue(V8\Value)->isAsyncFunction(): bool(false)
V8\UndefinedValue(V8\Value)->isGeneratorFunction(): bool(false)
V8\UndefinedValue(V8\Value)->isGeneratorObject(): bool(false)
V8\UndefinedValue(V8\Value)->isPromise(): bool(false)
V8\UndefinedValue(V8\Value)->isMap(): bool(false)
V8\UndefinedValue(V8\Value)->isSet(): bool(false)
V8\UndefinedValue(V8\Value)->isMapIterator(): bool(false)
V8\UndefinedValue(V8\Value)->isSetIterator(): bool(false)
V8\UndefinedValue(V8\Value)->isWeakMap(): bool(false)
V8\UndefinedValue(V8\Value)->isWeakSet(): bool(false)
V8\UndefinedValue(V8\Value)->isArrayBuffer(): bool(false)
V8\UndefinedValue(V8\Value)->isArrayBufferView(): bool(false)
V8\UndefinedValue(V8\Value)->isTypedArray(): bool(false)
V8\UndefinedValue(V8\Value)->isUint8Array(): bool(false)
V8\UndefinedValue(V8\Value)->isUint8ClampedArray(): bool(false)
V8\UndefinedValue(V8\Value)->isInt8Array(): bool(false)
V8\UndefinedValue(V8\Value)->isUint16Array(): bool(false)
V8\UndefinedValue(V8\Value)->isInt16Array(): bool(false)
V8\UndefinedValue(V8\Value)->isUint32Array(): bool(false)
V8\UndefinedValue(V8\Value)->isInt32Array(): bool(false)
V8\UndefinedValue(V8\Value)->isFloat32Array(): bool(false)
V8\UndefinedValue(V8\Value)->isFloat64Array(): bool(false)
V8\UndefinedValue(V8\Value)->isBigInt64Array(): bool(false)
V8\UndefinedValue(V8\Value)->isBigUint64Array(): bool(false)
V8\UndefinedValue(V8\Value)->isDataView(): bool(false)
V8\UndefinedValue(V8\Value)->isSharedArrayBuffer(): bool(false)
V8\UndefinedValue(V8\Value)->isProxy(): bool(false)


V8\UndefinedValue::toObject() converting:
-----------------------------------------
V8\Exceptions\TryCatchException: TypeError: Cannot convert undefined or null to object
