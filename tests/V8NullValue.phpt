--TEST--
v8\NullValue
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

// Bootstraps:

$isolate = new v8\Isolate();
$value = new v8\NullValue($isolate);

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

// Tests:

$helper->header('Object representation');
$helper->dump($value);
$helper->space();

$helper->assert('NullValue extends PrimitiveValue', $value instanceof \v8\PrimitiveValue);
$helper->line();

$helper->header('Accessors');
$helper->method_matches($value, 'GetIsolate', $isolate);
$helper->method_export($value, 'Value');
$helper->space();


$v8_helper->run_checks($value, 'Checkers');

$extensions = [];
$global_template = new \v8\ObjectTemplate($isolate);
$context = new \v8\Context($isolate, $extensions, $global_template);


$helper->header('Primitive converters');
$helper->method_export($value, 'BooleanValue', [$context]);
$helper->method_export($value, 'NumberValue', [$context]);
$helper->space();

$string = $value->ToString($context);

$helper->header(get_class($value) .'::ToString() converting');
$helper->dump($string);
$helper->dump($string->Value());

?>
--EXPECT--
Object representation:
----------------------
object(v8\NullValue)#2 (1) {
  ["isolate":"v8\Value":private]=>
  object(v8\Isolate)#1 (5) {
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


NullValue extends PrimitiveValue: ok

Accessors:
----------
v8\NullValue::GetIsolate() matches expected value
v8\NullValue->Value(): NULL


Checkers:
---------
v8\NullValue(v8\Value)->IsUndefined(): bool(false)
v8\NullValue(v8\Value)->IsNull(): bool(true)
v8\NullValue(v8\Value)->IsTrue(): bool(false)
v8\NullValue(v8\Value)->IsFalse(): bool(false)
v8\NullValue(v8\Value)->IsName(): bool(false)
v8\NullValue(v8\Value)->IsString(): bool(false)
v8\NullValue(v8\Value)->IsSymbol(): bool(false)
v8\NullValue(v8\Value)->IsFunction(): bool(false)
v8\NullValue(v8\Value)->IsArray(): bool(false)
v8\NullValue(v8\Value)->IsObject(): bool(false)
v8\NullValue(v8\Value)->IsBoolean(): bool(false)
v8\NullValue(v8\Value)->IsNumber(): bool(false)
v8\NullValue(v8\Value)->IsInt32(): bool(false)
v8\NullValue(v8\Value)->IsUint32(): bool(false)
v8\NullValue(v8\Value)->IsDate(): bool(false)
v8\NullValue(v8\Value)->IsArgumentsObject(): bool(false)
v8\NullValue(v8\Value)->IsBooleanObject(): bool(false)
v8\NullValue(v8\Value)->IsNumberObject(): bool(false)
v8\NullValue(v8\Value)->IsStringObject(): bool(false)
v8\NullValue(v8\Value)->IsSymbolObject(): bool(false)
v8\NullValue(v8\Value)->IsNativeError(): bool(false)
v8\NullValue(v8\Value)->IsRegExp(): bool(false)


Primitive converters:
---------------------
v8\NullValue(v8\Value)->BooleanValue(): bool(false)
v8\NullValue(v8\Value)->NumberValue(): float(0)


v8\NullValue::ToString() converting:
------------------------------------
object(v8\StringValue)#7 (1) {
  ["isolate":"v8\Value":private]=>
  object(v8\Isolate)#1 (5) {
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
string(4) "null"
