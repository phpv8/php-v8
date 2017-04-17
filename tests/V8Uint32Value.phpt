--TEST--
V8\Uint32Value
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
$value = new V8\Uint32Value($isolate, 2147483647+1);

$helper->header('Object representation');
$helper->dump($value);
$helper->space();

$helper->assert('Uint32Value extends IntegerValue', $value instanceof \V8\IntegerValue);
$helper->line();

$helper->header('Accessors');
$helper->method_matches($value, 'GetIsolate', $isolate);
$helper->method_export($value, 'Value');
$helper->space();


$v8_helper->run_checks($value, 'Checkers for negative');

$global_template = new \V8\ObjectTemplate($isolate);
$context = new \V8\Context($isolate, $global_template);


$string = $value->ToString($context);

$helper->header(get_class($value) .'::ToString() converting');
$helper->dump($string);
$helper->dump($string->Value());
$helper->space();



$helper->header('Primitive converters');
$helper->method_export($value, 'BooleanValue', [$context]);
$helper->method_export($value, 'NumberValue', [$context]);
$helper->space();


$helper->header('Uint32 is unsingned int32 value, so test for out-of-range (0-UINT32_MAX)');


foreach ([-1, PHP_INT_MAX, -PHP_INT_MAX, NAN, INF, -INF] as $val) {
  $helper->value_export($val);
  try {
    $value = new V8\Uint32Value($isolate, $val);
    $helper->method_export($value, 'Value');
  } catch (Throwable $e) {
    $helper->exception_export($e);
  }
  $helper->space();
}


?>
--EXPECT--
Object representation:
----------------------
object(V8\Uint32Value)#4 (1) {
  ["isolate":"V8\Value":private]=>
  object(V8\Isolate)#3 (5) {
    ["snapshot":"V8\Isolate":private]=>
    NULL
    ["time_limit":"V8\Isolate":private]=>
    float(0)
    ["time_limit_hit":"V8\Isolate":private]=>
    bool(false)
    ["memory_limit":"V8\Isolate":private]=>
    int(0)
    ["memory_limit_hit":"V8\Isolate":private]=>
    bool(false)
  }
}


Uint32Value extends IntegerValue: ok

Accessors:
----------
V8\Uint32Value::GetIsolate() matches expected value
V8\Uint32Value->Value(): int(2147483648)


Checkers for negative:
----------------------
V8\Uint32Value(V8\Value)->TypeOf(): V8\StringValue->Value(): string(6) "number"

V8\Uint32Value(V8\Value)->IsUndefined(): bool(false)
V8\Uint32Value(V8\Value)->IsNull(): bool(false)
V8\Uint32Value(V8\Value)->IsNullOrUndefined(): bool(false)
V8\Uint32Value(V8\Value)->IsTrue(): bool(false)
V8\Uint32Value(V8\Value)->IsFalse(): bool(false)
V8\Uint32Value(V8\Value)->IsName(): bool(false)
V8\Uint32Value(V8\Value)->IsString(): bool(false)
V8\Uint32Value(V8\Value)->IsSymbol(): bool(false)
V8\Uint32Value(V8\Value)->IsFunction(): bool(false)
V8\Uint32Value(V8\Value)->IsArray(): bool(false)
V8\Uint32Value(V8\Value)->IsObject(): bool(false)
V8\Uint32Value(V8\Value)->IsBoolean(): bool(false)
V8\Uint32Value(V8\Value)->IsNumber(): bool(true)
V8\Uint32Value(V8\Value)->IsInt32(): bool(false)
V8\Uint32Value(V8\Value)->IsUint32(): bool(true)
V8\Uint32Value(V8\Value)->IsDate(): bool(false)
V8\Uint32Value(V8\Value)->IsArgumentsObject(): bool(false)
V8\Uint32Value(V8\Value)->IsBooleanObject(): bool(false)
V8\Uint32Value(V8\Value)->IsNumberObject(): bool(false)
V8\Uint32Value(V8\Value)->IsStringObject(): bool(false)
V8\Uint32Value(V8\Value)->IsSymbolObject(): bool(false)
V8\Uint32Value(V8\Value)->IsNativeError(): bool(false)
V8\Uint32Value(V8\Value)->IsRegExp(): bool(false)
V8\Uint32Value(V8\Value)->IsAsyncFunction(): bool(false)
V8\Uint32Value(V8\Value)->IsGeneratorFunction(): bool(false)
V8\Uint32Value(V8\Value)->IsGeneratorObject(): bool(false)
V8\Uint32Value(V8\Value)->IsPromise(): bool(false)
V8\Uint32Value(V8\Value)->IsMap(): bool(false)
V8\Uint32Value(V8\Value)->IsSet(): bool(false)
V8\Uint32Value(V8\Value)->IsMapIterator(): bool(false)
V8\Uint32Value(V8\Value)->IsSetIterator(): bool(false)
V8\Uint32Value(V8\Value)->IsWeakMap(): bool(false)
V8\Uint32Value(V8\Value)->IsWeakSet(): bool(false)
V8\Uint32Value(V8\Value)->IsArrayBuffer(): bool(false)
V8\Uint32Value(V8\Value)->IsArrayBufferView(): bool(false)
V8\Uint32Value(V8\Value)->IsTypedArray(): bool(false)
V8\Uint32Value(V8\Value)->IsUint8Array(): bool(false)
V8\Uint32Value(V8\Value)->IsUint8ClampedArray(): bool(false)
V8\Uint32Value(V8\Value)->IsInt8Array(): bool(false)
V8\Uint32Value(V8\Value)->IsUint16Array(): bool(false)
V8\Uint32Value(V8\Value)->IsInt16Array(): bool(false)
V8\Uint32Value(V8\Value)->IsUint32Array(): bool(false)
V8\Uint32Value(V8\Value)->IsInt32Array(): bool(false)
V8\Uint32Value(V8\Value)->IsFloat32Array(): bool(false)
V8\Uint32Value(V8\Value)->IsFloat64Array(): bool(false)
V8\Uint32Value(V8\Value)->IsDataView(): bool(false)
V8\Uint32Value(V8\Value)->IsSharedArrayBuffer(): bool(false)
V8\Uint32Value(V8\Value)->IsProxy(): bool(false)


V8\Uint32Value::ToString() converting:
--------------------------------------
object(V8\StringValue)#79 (1) {
  ["isolate":"V8\Value":private]=>
  object(V8\Isolate)#3 (5) {
    ["snapshot":"V8\Isolate":private]=>
    NULL
    ["time_limit":"V8\Isolate":private]=>
    float(0)
    ["time_limit_hit":"V8\Isolate":private]=>
    bool(false)
    ["memory_limit":"V8\Isolate":private]=>
    int(0)
    ["memory_limit_hit":"V8\Isolate":private]=>
    bool(false)
  }
}
string(10) "2147483648"


Primitive converters:
---------------------
V8\Uint32Value(V8\Value)->BooleanValue(): bool(true)
V8\Uint32Value(V8\Value)->NumberValue(): float(2147483648)


Uint32 is unsingned int32 value, so test for out-of-range (0-UINT32_MAX):
-------------------------------------------------------------------------
integer: -1
V8\Exceptions\ValueException: Uint32 value to set is out of range


integer: 9223372036854775807
V8\Exceptions\ValueException: Uint32 value to set is out of range


integer: -9223372036854775807
V8\Exceptions\ValueException: Uint32 value to set is out of range


double: NAN
TypeError: Argument 2 passed to V8\Uint32Value::__construct() must be of the type integer, float given


double: INF
TypeError: Argument 2 passed to V8\Uint32Value::__construct() must be of the type integer, float given


double: -INF
TypeError: Argument 2 passed to V8\Uint32Value::__construct() must be of the type integer, float given
