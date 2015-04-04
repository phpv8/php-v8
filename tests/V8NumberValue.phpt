--TEST--
v8\NumberValue
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
$value = new v8\NumberValue($isolate, 123.456);


$helper->header('Object representation');
debug_zval_dump($value);
$helper->space();

$helper->assert('NumberValue extends PrimitiveValue', $value instanceof \v8\PrimitiveValue);
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
$value = new v8\NumberValue($isolate, -123.456);
$helper->method_export($value, 'Value');
$helper->method_export($value, 'BooleanValue', [$context]);
$helper->method_export($value, 'NumberValue', [$context]);
$helper->space();

$helper->header('Test non-standard constructor values');

foreach ([null, true, false, NAN, INF, -INF] as $val) {
    try {
        $value = new v8\NumberValue($isolate, $val);
        $helper->value_export($val);
        $helper->method_export($value, 'Value');
        $helper->method_export($value, 'BooleanValue', [$context]);
        $helper->method_export($value, 'NumberValue', [$context]);
    } catch (Throwable $e) {
        $helper->exception_export($e);
    }
    $helper->space();
}


?>
--EXPECT--
Object representation:
----------------------
object(v8\NumberValue)#4 (1) refcount(2){
  ["isolate":"v8\Value":private]=>
  object(v8\Isolate)#3 (1) refcount(2){
    ["snapshot":"v8\Isolate":private]=>
    NULL
  }
}


NumberValue extends PrimitiveValue: ok

Accessors:
----------
v8\NumberValue::GetIsolate() matches expected value
v8\NumberValue->Value(): float(123.456)


Checkers:
---------
v8\NumberValue(v8\Value)->IsUndefined(): bool(false)
v8\NumberValue(v8\Value)->IsNull(): bool(false)
v8\NumberValue(v8\Value)->IsTrue(): bool(false)
v8\NumberValue(v8\Value)->IsFalse(): bool(false)
v8\NumberValue(v8\Value)->IsName(): bool(false)
v8\NumberValue(v8\Value)->IsString(): bool(false)
v8\NumberValue(v8\Value)->IsSymbol(): bool(false)
v8\NumberValue(v8\Value)->IsFunction(): bool(false)
v8\NumberValue(v8\Value)->IsArray(): bool(false)
v8\NumberValue(v8\Value)->IsObject(): bool(false)
v8\NumberValue(v8\Value)->IsBoolean(): bool(false)
v8\NumberValue(v8\Value)->IsNumber(): bool(true)
v8\NumberValue(v8\Value)->IsInt32(): bool(false)
v8\NumberValue(v8\Value)->IsUint32(): bool(false)
v8\NumberValue(v8\Value)->IsDate(): bool(false)
v8\NumberValue(v8\Value)->IsArgumentsObject(): bool(false)
v8\NumberValue(v8\Value)->IsBooleanObject(): bool(false)
v8\NumberValue(v8\Value)->IsNumberObject(): bool(false)
v8\NumberValue(v8\Value)->IsStringObject(): bool(false)
v8\NumberValue(v8\Value)->IsSymbolObject(): bool(false)
v8\NumberValue(v8\Value)->IsNativeError(): bool(false)
v8\NumberValue(v8\Value)->IsRegExp(): bool(false)


v8\NumberValue::ToString() converting:
--------------------------------------
object(v8\StringValue)#7 (1) refcount(2){
  ["isolate":"v8\Value":private]=>
  object(v8\Isolate)#3 (1) refcount(5){
    ["snapshot":"v8\Isolate":private]=>
    NULL
  }
}
string(7) "123.456" refcount(1)


Primitive converters:
---------------------
v8\NumberValue(v8\Value)->BooleanValue(): bool(true)
v8\NumberValue(v8\Value)->NumberValue(): float(123.456)


Test negative value in constructor:
-----------------------------------
v8\NumberValue->Value(): float(-123.456)
v8\NumberValue(v8\Value)->BooleanValue(): bool(true)
v8\NumberValue(v8\Value)->NumberValue(): float(-123.456)


Test non-standard constructor values:
-------------------------------------
TypeError: Argument 2 passed to v8\NumberValue::__construct() must be of the type float, null given


boolean: true
v8\NumberValue->Value(): float(1)
v8\NumberValue(v8\Value)->BooleanValue(): bool(true)
v8\NumberValue(v8\Value)->NumberValue(): float(1)


boolean: false
v8\NumberValue->Value(): float(0)
v8\NumberValue(v8\Value)->BooleanValue(): bool(false)
v8\NumberValue(v8\Value)->NumberValue(): float(0)


double: NAN
v8\NumberValue->Value(): float(NAN)
v8\NumberValue(v8\Value)->BooleanValue(): bool(false)
v8\NumberValue(v8\Value)->NumberValue(): float(NAN)


double: INF
v8\NumberValue->Value(): float(INF)
v8\NumberValue(v8\Value)->BooleanValue(): bool(true)
v8\NumberValue(v8\Value)->NumberValue(): float(INF)


double: -INF
v8\NumberValue->Value(): float(-INF)
v8\NumberValue(v8\Value)->BooleanValue(): bool(true)
v8\NumberValue(v8\Value)->NumberValue(): float(-INF)
