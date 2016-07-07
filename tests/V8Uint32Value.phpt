--TEST--
v8\Uint32Value
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

// Tests:


$isolate = new v8\Isolate();
$value = new v8\Uint32Value($isolate, 2147483647+1);

$helper->header('Object representation');
$helper->dump($value);
$helper->space();

$helper->assert('Uint32Value extends IntegerValue', $value instanceof \v8\IntegerValue);
$helper->line();

$helper->header('Accessors');
$helper->method_matches($value, 'GetIsolate', $isolate);
$helper->method_export($value, 'Value');
$helper->space();


$v8_helper->run_checks($value, 'Checkers for negative');

$extensions = [];
$global_template = new \v8\ObjectTemplate($isolate);
$context = new \v8\Context($isolate, $extensions, $global_template);


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
    $value = new v8\Uint32Value($isolate, $val);
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
object(v8\Uint32Value)#4 (1) {
  ["isolate":"v8\Value":private]=>
  object(v8\Isolate)#3 (5) {
    ["snapshot":"v8\Isolate":private]=>
    NULL
    ["time_limit":"v8\Isolate":private]=>
    float(0)
    ["time_limit_hit":"v8\Isolate":private]=>
    bool(false)
    ["memory_limit":"v8\Isolate":private]=>
    int(0)
    ["memory_limit_hit":"v8\Isolate":private]=>
    bool(false)
  }
}


Uint32Value extends IntegerValue: ok

Accessors:
----------
v8\Uint32Value::GetIsolate() matches expected value
v8\Uint32Value->Value(): int(2147483648)


Checkers for negative:
----------------------
v8\Uint32Value(v8\Value)->IsUndefined(): bool(false)
v8\Uint32Value(v8\Value)->IsNull(): bool(false)
v8\Uint32Value(v8\Value)->IsTrue(): bool(false)
v8\Uint32Value(v8\Value)->IsFalse(): bool(false)
v8\Uint32Value(v8\Value)->IsName(): bool(false)
v8\Uint32Value(v8\Value)->IsString(): bool(false)
v8\Uint32Value(v8\Value)->IsSymbol(): bool(false)
v8\Uint32Value(v8\Value)->IsFunction(): bool(false)
v8\Uint32Value(v8\Value)->IsArray(): bool(false)
v8\Uint32Value(v8\Value)->IsObject(): bool(false)
v8\Uint32Value(v8\Value)->IsBoolean(): bool(false)
v8\Uint32Value(v8\Value)->IsNumber(): bool(true)
v8\Uint32Value(v8\Value)->IsInt32(): bool(false)
v8\Uint32Value(v8\Value)->IsUint32(): bool(true)
v8\Uint32Value(v8\Value)->IsDate(): bool(false)
v8\Uint32Value(v8\Value)->IsArgumentsObject(): bool(false)
v8\Uint32Value(v8\Value)->IsBooleanObject(): bool(false)
v8\Uint32Value(v8\Value)->IsNumberObject(): bool(false)
v8\Uint32Value(v8\Value)->IsStringObject(): bool(false)
v8\Uint32Value(v8\Value)->IsSymbolObject(): bool(false)
v8\Uint32Value(v8\Value)->IsNativeError(): bool(false)
v8\Uint32Value(v8\Value)->IsRegExp(): bool(false)


v8\Uint32Value::ToString() converting:
--------------------------------------
object(v8\StringValue)#7 (1) {
  ["isolate":"v8\Value":private]=>
  object(v8\Isolate)#3 (5) {
    ["snapshot":"v8\Isolate":private]=>
    NULL
    ["time_limit":"v8\Isolate":private]=>
    float(0)
    ["time_limit_hit":"v8\Isolate":private]=>
    bool(false)
    ["memory_limit":"v8\Isolate":private]=>
    int(0)
    ["memory_limit_hit":"v8\Isolate":private]=>
    bool(false)
  }
}
string(10) "2147483648"


Primitive converters:
---------------------
v8\Uint32Value(v8\Value)->BooleanValue(): bool(true)
v8\Uint32Value(v8\Value)->NumberValue(): float(2147483648)


Uint32 is unsingned int32 value, so test for out-of-range (0-UINT32_MAX):
-------------------------------------------------------------------------
integer: -1
v8\Exceptions\ValueException: Uint32 value to set is out of range


integer: 9223372036854775807
v8\Exceptions\ValueException: Uint32 value to set is out of range


integer: -9223372036854775807
v8\Exceptions\ValueException: Uint32 value to set is out of range


double: NAN
TypeError: Argument 2 passed to v8\Uint32Value::__construct() must be of the type integer, float given


double: INF
TypeError: Argument 2 passed to v8\Uint32Value::__construct() must be of the type integer, float given


double: -INF
TypeError: Argument 2 passed to v8\Uint32Value::__construct() must be of the type integer, float given
