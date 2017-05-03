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
$helper->assert('TypeOf returns StringValue', $value->TypeOf() instanceof \V8\StringValue);
$helper->line();

$helper->header('InstanceOf');
try {
    $value->InstanceOf($context, new \V8\ObjectValue($context));
} catch (\V8\Exceptions\TryCatchException $e) {
    $helper->exception_export($e);
}
$helper->assert('Default Value is not an instance of Function', !$value->InstanceOf($context, new \V8\FunctionObject($context, function(){})));
$helper->line();

$helper->header('Accessors');
$helper->method_matches($value, 'GetIsolate', $isolate);
$helper->method_export($value, 'Value');
$helper->space();

$v8_helper->run_checks($value);

$global_template = new \V8\ObjectTemplate($isolate);
$context = new \V8\Context($isolate, $global_template);


$helper->header('Primitive converters');
$helper->method_export($value, 'BooleanValue', [$context]);
$helper->method_export($value, 'NumberValue', [$context]);
$helper->space();


$string = $value->ToString($context);

$helper->header(get_class($value) .'::ToString() converting');
$helper->dump($string);
$helper->dump($string->Value());
$helper->space();

$v8_helper->run_checks($value, 'Checkers after ToString() converting');

$helper->header(get_class($value) .'::ToObject() converting');
try {
  $object = $value->ToObject($context);
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
V8\UndefinedValue::GetIsolate() matches expected value
V8\UndefinedValue->Value(): NULL


Checks on V8\UndefinedValue:
----------------------------
V8\UndefinedValue(V8\Value)->TypeOf(): V8\StringValue->Value(): string(9) "undefined"

V8\UndefinedValue(V8\Value)->IsUndefined(): bool(true)
V8\UndefinedValue(V8\Value)->IsNull(): bool(false)
V8\UndefinedValue(V8\Value)->IsNullOrUndefined(): bool(true)
V8\UndefinedValue(V8\Value)->IsTrue(): bool(false)
V8\UndefinedValue(V8\Value)->IsFalse(): bool(false)
V8\UndefinedValue(V8\Value)->IsName(): bool(false)
V8\UndefinedValue(V8\Value)->IsString(): bool(false)
V8\UndefinedValue(V8\Value)->IsSymbol(): bool(false)
V8\UndefinedValue(V8\Value)->IsFunction(): bool(false)
V8\UndefinedValue(V8\Value)->IsArray(): bool(false)
V8\UndefinedValue(V8\Value)->IsObject(): bool(false)
V8\UndefinedValue(V8\Value)->IsBoolean(): bool(false)
V8\UndefinedValue(V8\Value)->IsNumber(): bool(false)
V8\UndefinedValue(V8\Value)->IsInt32(): bool(false)
V8\UndefinedValue(V8\Value)->IsUint32(): bool(false)
V8\UndefinedValue(V8\Value)->IsDate(): bool(false)
V8\UndefinedValue(V8\Value)->IsArgumentsObject(): bool(false)
V8\UndefinedValue(V8\Value)->IsBooleanObject(): bool(false)
V8\UndefinedValue(V8\Value)->IsNumberObject(): bool(false)
V8\UndefinedValue(V8\Value)->IsStringObject(): bool(false)
V8\UndefinedValue(V8\Value)->IsSymbolObject(): bool(false)
V8\UndefinedValue(V8\Value)->IsNativeError(): bool(false)
V8\UndefinedValue(V8\Value)->IsRegExp(): bool(false)
V8\UndefinedValue(V8\Value)->IsAsyncFunction(): bool(false)
V8\UndefinedValue(V8\Value)->IsGeneratorFunction(): bool(false)
V8\UndefinedValue(V8\Value)->IsGeneratorObject(): bool(false)
V8\UndefinedValue(V8\Value)->IsPromise(): bool(false)
V8\UndefinedValue(V8\Value)->IsMap(): bool(false)
V8\UndefinedValue(V8\Value)->IsSet(): bool(false)
V8\UndefinedValue(V8\Value)->IsMapIterator(): bool(false)
V8\UndefinedValue(V8\Value)->IsSetIterator(): bool(false)
V8\UndefinedValue(V8\Value)->IsWeakMap(): bool(false)
V8\UndefinedValue(V8\Value)->IsWeakSet(): bool(false)
V8\UndefinedValue(V8\Value)->IsArrayBuffer(): bool(false)
V8\UndefinedValue(V8\Value)->IsArrayBufferView(): bool(false)
V8\UndefinedValue(V8\Value)->IsTypedArray(): bool(false)
V8\UndefinedValue(V8\Value)->IsUint8Array(): bool(false)
V8\UndefinedValue(V8\Value)->IsUint8ClampedArray(): bool(false)
V8\UndefinedValue(V8\Value)->IsInt8Array(): bool(false)
V8\UndefinedValue(V8\Value)->IsUint16Array(): bool(false)
V8\UndefinedValue(V8\Value)->IsInt16Array(): bool(false)
V8\UndefinedValue(V8\Value)->IsUint32Array(): bool(false)
V8\UndefinedValue(V8\Value)->IsInt32Array(): bool(false)
V8\UndefinedValue(V8\Value)->IsFloat32Array(): bool(false)
V8\UndefinedValue(V8\Value)->IsFloat64Array(): bool(false)
V8\UndefinedValue(V8\Value)->IsDataView(): bool(false)
V8\UndefinedValue(V8\Value)->IsSharedArrayBuffer(): bool(false)
V8\UndefinedValue(V8\Value)->IsProxy(): bool(false)


Primitive converters:
---------------------
V8\UndefinedValue(V8\Value)->BooleanValue(): bool(false)
V8\UndefinedValue(V8\Value)->NumberValue(): float(NAN)


V8\UndefinedValue::ToString() converting:
-----------------------------------------
object(V8\StringValue)#89 (1) {
  ["isolate":"V8\Value":private]=>
  object(V8\Isolate)#3 (0) {
  }
}
string(9) "undefined"


Checkers after ToString() converting:
-------------------------------------
V8\UndefinedValue(V8\Value)->TypeOf(): V8\StringValue->Value(): string(9) "undefined"

V8\UndefinedValue(V8\Value)->IsUndefined(): bool(true)
V8\UndefinedValue(V8\Value)->IsNull(): bool(false)
V8\UndefinedValue(V8\Value)->IsNullOrUndefined(): bool(true)
V8\UndefinedValue(V8\Value)->IsTrue(): bool(false)
V8\UndefinedValue(V8\Value)->IsFalse(): bool(false)
V8\UndefinedValue(V8\Value)->IsName(): bool(false)
V8\UndefinedValue(V8\Value)->IsString(): bool(false)
V8\UndefinedValue(V8\Value)->IsSymbol(): bool(false)
V8\UndefinedValue(V8\Value)->IsFunction(): bool(false)
V8\UndefinedValue(V8\Value)->IsArray(): bool(false)
V8\UndefinedValue(V8\Value)->IsObject(): bool(false)
V8\UndefinedValue(V8\Value)->IsBoolean(): bool(false)
V8\UndefinedValue(V8\Value)->IsNumber(): bool(false)
V8\UndefinedValue(V8\Value)->IsInt32(): bool(false)
V8\UndefinedValue(V8\Value)->IsUint32(): bool(false)
V8\UndefinedValue(V8\Value)->IsDate(): bool(false)
V8\UndefinedValue(V8\Value)->IsArgumentsObject(): bool(false)
V8\UndefinedValue(V8\Value)->IsBooleanObject(): bool(false)
V8\UndefinedValue(V8\Value)->IsNumberObject(): bool(false)
V8\UndefinedValue(V8\Value)->IsStringObject(): bool(false)
V8\UndefinedValue(V8\Value)->IsSymbolObject(): bool(false)
V8\UndefinedValue(V8\Value)->IsNativeError(): bool(false)
V8\UndefinedValue(V8\Value)->IsRegExp(): bool(false)
V8\UndefinedValue(V8\Value)->IsAsyncFunction(): bool(false)
V8\UndefinedValue(V8\Value)->IsGeneratorFunction(): bool(false)
V8\UndefinedValue(V8\Value)->IsGeneratorObject(): bool(false)
V8\UndefinedValue(V8\Value)->IsPromise(): bool(false)
V8\UndefinedValue(V8\Value)->IsMap(): bool(false)
V8\UndefinedValue(V8\Value)->IsSet(): bool(false)
V8\UndefinedValue(V8\Value)->IsMapIterator(): bool(false)
V8\UndefinedValue(V8\Value)->IsSetIterator(): bool(false)
V8\UndefinedValue(V8\Value)->IsWeakMap(): bool(false)
V8\UndefinedValue(V8\Value)->IsWeakSet(): bool(false)
V8\UndefinedValue(V8\Value)->IsArrayBuffer(): bool(false)
V8\UndefinedValue(V8\Value)->IsArrayBufferView(): bool(false)
V8\UndefinedValue(V8\Value)->IsTypedArray(): bool(false)
V8\UndefinedValue(V8\Value)->IsUint8Array(): bool(false)
V8\UndefinedValue(V8\Value)->IsUint8ClampedArray(): bool(false)
V8\UndefinedValue(V8\Value)->IsInt8Array(): bool(false)
V8\UndefinedValue(V8\Value)->IsUint16Array(): bool(false)
V8\UndefinedValue(V8\Value)->IsInt16Array(): bool(false)
V8\UndefinedValue(V8\Value)->IsUint32Array(): bool(false)
V8\UndefinedValue(V8\Value)->IsInt32Array(): bool(false)
V8\UndefinedValue(V8\Value)->IsFloat32Array(): bool(false)
V8\UndefinedValue(V8\Value)->IsFloat64Array(): bool(false)
V8\UndefinedValue(V8\Value)->IsDataView(): bool(false)
V8\UndefinedValue(V8\Value)->IsSharedArrayBuffer(): bool(false)
V8\UndefinedValue(V8\Value)->IsProxy(): bool(false)


V8\UndefinedValue::ToObject() converting:
-----------------------------------------
V8\Exceptions\TryCatchException: TypeError: Cannot convert undefined or null to object
