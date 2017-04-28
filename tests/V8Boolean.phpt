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
$helper->method_matches($value, 'GetIsolate', $isolate);
$helper->method_export($value, 'Value');
$helper->space();


$v8_helper->run_checks($value, 'Checkers');

$global_template = new \V8\ObjectTemplate($isolate);
$context = new \V8\Context($isolate, $global_template);


$helper->header('Primitive converters');
$helper->method_export($value, 'BooleanValue', [$context]);
$helper->method_export($value, 'NumberValue', [$context]);
$helper->space();


$helper->header(get_class($value) .'::ToString() converting');
$string = $value->ToString($context);
$helper->dump($string->Value());
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
V8\BooleanValue::GetIsolate() matches expected value
V8\BooleanValue->Value(): bool(true)


Checkers:
---------
V8\BooleanValue(V8\Value)->TypeOf(): V8\StringValue->Value(): string(7) "boolean"

V8\BooleanValue(V8\Value)->IsUndefined(): bool(false)
V8\BooleanValue(V8\Value)->IsNull(): bool(false)
V8\BooleanValue(V8\Value)->IsNullOrUndefined(): bool(false)
V8\BooleanValue(V8\Value)->IsTrue(): bool(true)
V8\BooleanValue(V8\Value)->IsFalse(): bool(false)
V8\BooleanValue(V8\Value)->IsName(): bool(false)
V8\BooleanValue(V8\Value)->IsString(): bool(false)
V8\BooleanValue(V8\Value)->IsSymbol(): bool(false)
V8\BooleanValue(V8\Value)->IsFunction(): bool(false)
V8\BooleanValue(V8\Value)->IsArray(): bool(false)
V8\BooleanValue(V8\Value)->IsObject(): bool(false)
V8\BooleanValue(V8\Value)->IsBoolean(): bool(true)
V8\BooleanValue(V8\Value)->IsNumber(): bool(false)
V8\BooleanValue(V8\Value)->IsInt32(): bool(false)
V8\BooleanValue(V8\Value)->IsUint32(): bool(false)
V8\BooleanValue(V8\Value)->IsDate(): bool(false)
V8\BooleanValue(V8\Value)->IsArgumentsObject(): bool(false)
V8\BooleanValue(V8\Value)->IsBooleanObject(): bool(false)
V8\BooleanValue(V8\Value)->IsNumberObject(): bool(false)
V8\BooleanValue(V8\Value)->IsStringObject(): bool(false)
V8\BooleanValue(V8\Value)->IsSymbolObject(): bool(false)
V8\BooleanValue(V8\Value)->IsNativeError(): bool(false)
V8\BooleanValue(V8\Value)->IsRegExp(): bool(false)
V8\BooleanValue(V8\Value)->IsAsyncFunction(): bool(false)
V8\BooleanValue(V8\Value)->IsGeneratorFunction(): bool(false)
V8\BooleanValue(V8\Value)->IsGeneratorObject(): bool(false)
V8\BooleanValue(V8\Value)->IsPromise(): bool(false)
V8\BooleanValue(V8\Value)->IsMap(): bool(false)
V8\BooleanValue(V8\Value)->IsSet(): bool(false)
V8\BooleanValue(V8\Value)->IsMapIterator(): bool(false)
V8\BooleanValue(V8\Value)->IsSetIterator(): bool(false)
V8\BooleanValue(V8\Value)->IsWeakMap(): bool(false)
V8\BooleanValue(V8\Value)->IsWeakSet(): bool(false)
V8\BooleanValue(V8\Value)->IsArrayBuffer(): bool(false)
V8\BooleanValue(V8\Value)->IsArrayBufferView(): bool(false)
V8\BooleanValue(V8\Value)->IsTypedArray(): bool(false)
V8\BooleanValue(V8\Value)->IsUint8Array(): bool(false)
V8\BooleanValue(V8\Value)->IsUint8ClampedArray(): bool(false)
V8\BooleanValue(V8\Value)->IsInt8Array(): bool(false)
V8\BooleanValue(V8\Value)->IsUint16Array(): bool(false)
V8\BooleanValue(V8\Value)->IsInt16Array(): bool(false)
V8\BooleanValue(V8\Value)->IsUint32Array(): bool(false)
V8\BooleanValue(V8\Value)->IsInt32Array(): bool(false)
V8\BooleanValue(V8\Value)->IsFloat32Array(): bool(false)
V8\BooleanValue(V8\Value)->IsFloat64Array(): bool(false)
V8\BooleanValue(V8\Value)->IsDataView(): bool(false)
V8\BooleanValue(V8\Value)->IsSharedArrayBuffer(): bool(false)
V8\BooleanValue(V8\Value)->IsProxy(): bool(false)


Primitive converters:
---------------------
V8\BooleanValue(V8\Value)->BooleanValue(): bool(true)
V8\BooleanValue(V8\Value)->NumberValue(): float(1)


V8\BooleanValue::ToString() converting:
---------------------------------------
string(4) "true"
