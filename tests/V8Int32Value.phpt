--TEST--
v8\Int32Value
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

// Bootstraps:

$isolate = new v8\Isolate();
$value = new v8\Int32Value($isolate, 2147483647-1);

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

// Tests:

$helper->header('Object representation');
debug_zval_dump($value);
$helper->space();

$helper->assert('Int32Value extends IntegerValue', $value instanceof \v8\IntegerValue);
$helper->line();

$helper->header('Accessors');
$helper->method_matches($value, 'GetIsolate', $isolate);
$helper->method_export($value, 'Value');
$helper->space();


$v8_helper->run_checks($value, 'Checkers');


$extensions = [];
$global_template = new \v8\ObjectTemplate($isolate);
$context = new \v8\Context($isolate, $extensions, $global_template);


$string = $value->ToString($context);

$helper->header(get_class($value) .'::ToString() converting');
debug_zval_dump($string);
debug_zval_dump($string->Value());
$helper->space();


$helper->header('Primitive converters');
$helper->method_export($value, 'BooleanValue', [$context]);
$helper->method_export($value, 'NumberValue', [$context]);
$helper->space();


$helper->header('Test negative value in constructor');
$value = new v8\Int32Value($isolate, -123.456);
$helper->method_export($value, 'Value');
$helper->method_export($value, 'BooleanValue', [$context]);
$helper->method_export($value, 'NumberValue', [$context]);
$helper->space();

$v8_helper->run_checks($value, 'Checkers for negative');


$helper->header('Int32 is same as Integer - int32, so test for out-of-range (INT32_MIN-INT32_MAX)');

foreach ([PHP_INT_MAX, -PHP_INT_MAX, NAN, INF, -INF] as $val) {
  $helper->value_export($val);
  try {
    $value = new v8\Int32Value($isolate, $val);
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
object(v8\Int32Value)#2 (1) refcount(2){
  ["isolate":"v8\Value":private]=>
  object(v8\Isolate)#1 (1) refcount(2){
    ["snapshot":"v8\Isolate":private]=>
    NULL
  }
}


Int32Value extends IntegerValue: ok

Accessors:
----------
v8\Int32Value::GetIsolate() matches expected value
v8\Int32Value->Value(): int(2147483646)


Checkers:
---------
v8\Int32Value(v8\Value)->IsUndefined(): bool(false)
v8\Int32Value(v8\Value)->IsNull(): bool(false)
v8\Int32Value(v8\Value)->IsTrue(): bool(false)
v8\Int32Value(v8\Value)->IsFalse(): bool(false)
v8\Int32Value(v8\Value)->IsName(): bool(false)
v8\Int32Value(v8\Value)->IsString(): bool(false)
v8\Int32Value(v8\Value)->IsSymbol(): bool(false)
v8\Int32Value(v8\Value)->IsFunction(): bool(false)
v8\Int32Value(v8\Value)->IsArray(): bool(false)
v8\Int32Value(v8\Value)->IsObject(): bool(false)
v8\Int32Value(v8\Value)->IsBoolean(): bool(false)
v8\Int32Value(v8\Value)->IsNumber(): bool(true)
v8\Int32Value(v8\Value)->IsInt32(): bool(true)
v8\Int32Value(v8\Value)->IsUint32(): bool(true)
v8\Int32Value(v8\Value)->IsDate(): bool(false)
v8\Int32Value(v8\Value)->IsArgumentsObject(): bool(false)
v8\Int32Value(v8\Value)->IsBooleanObject(): bool(false)
v8\Int32Value(v8\Value)->IsNumberObject(): bool(false)
v8\Int32Value(v8\Value)->IsStringObject(): bool(false)
v8\Int32Value(v8\Value)->IsSymbolObject(): bool(false)
v8\Int32Value(v8\Value)->IsNativeError(): bool(false)
v8\Int32Value(v8\Value)->IsRegExp(): bool(false)


v8\Int32Value::ToString() converting:
-------------------------------------
object(v8\StringValue)#7 (1) refcount(2){
  ["isolate":"v8\Value":private]=>
  object(v8\Isolate)#1 (1) refcount(5){
    ["snapshot":"v8\Isolate":private]=>
    NULL
  }
}
string(10) "2147483646" refcount(1)


Primitive converters:
---------------------
v8\Int32Value(v8\Value)->BooleanValue(): bool(true)
v8\Int32Value(v8\Value)->NumberValue(): float(2147483646)


Test negative value in constructor:
-----------------------------------
v8\Int32Value->Value(): int(-123)
v8\Int32Value(v8\Value)->BooleanValue(): bool(true)
v8\Int32Value(v8\Value)->NumberValue(): float(-123)


Checkers for negative:
----------------------
v8\Int32Value(v8\Value)->IsUndefined(): bool(false)
v8\Int32Value(v8\Value)->IsNull(): bool(false)
v8\Int32Value(v8\Value)->IsTrue(): bool(false)
v8\Int32Value(v8\Value)->IsFalse(): bool(false)
v8\Int32Value(v8\Value)->IsName(): bool(false)
v8\Int32Value(v8\Value)->IsString(): bool(false)
v8\Int32Value(v8\Value)->IsSymbol(): bool(false)
v8\Int32Value(v8\Value)->IsFunction(): bool(false)
v8\Int32Value(v8\Value)->IsArray(): bool(false)
v8\Int32Value(v8\Value)->IsObject(): bool(false)
v8\Int32Value(v8\Value)->IsBoolean(): bool(false)
v8\Int32Value(v8\Value)->IsNumber(): bool(true)
v8\Int32Value(v8\Value)->IsInt32(): bool(true)
v8\Int32Value(v8\Value)->IsUint32(): bool(false)
v8\Int32Value(v8\Value)->IsDate(): bool(false)
v8\Int32Value(v8\Value)->IsArgumentsObject(): bool(false)
v8\Int32Value(v8\Value)->IsBooleanObject(): bool(false)
v8\Int32Value(v8\Value)->IsNumberObject(): bool(false)
v8\Int32Value(v8\Value)->IsStringObject(): bool(false)
v8\Int32Value(v8\Value)->IsSymbolObject(): bool(false)
v8\Int32Value(v8\Value)->IsNativeError(): bool(false)
v8\Int32Value(v8\Value)->IsRegExp(): bool(false)


Int32 is same as Integer - int32, so test for out-of-range (INT32_MIN-INT32_MAX):
---------------------------------------------------------------------------------
integer: 9223372036854775807
v8\Exceptions\ValueException: Int32 value to set is out of range


integer: -9223372036854775807
v8\Exceptions\ValueException: Int32 value to set is out of range


double: NAN
TypeError: Argument 2 passed to v8\Int32Value::__construct() must be of the type integer, float given


double: INF
TypeError: Argument 2 passed to v8\Int32Value::__construct() must be of the type integer, float given


double: -INF
TypeError: Argument 2 passed to v8\Int32Value::__construct() must be of the type integer, float given
