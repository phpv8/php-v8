--TEST--
v8\StringValue
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

$default = new v8\StringValue($isolate);
$helper->header('Default constructor');
debug_zval_dump($default);
$helper->method_export($default, 'Value');
$helper->space();


$value = new v8\StringValue($isolate, 'test string');

$helper->header('Object representation');
debug_zval_dump($value);
$helper->space();

$helper->assert('StringValue extends NameValue', $value instanceof \v8\NameValue);
$helper->line();

$helper->header('Accessors');
$helper->method_matches($value, 'GetIsolate', $isolate);
$helper->method_export($value, 'Value');
$helper->space();

$v8_helper->run_checks($value, 'Checkers');

$helper->header('Getters');
$helper->method_export($value, 'GetIdentityHash');
$helper->method_export($value, 'Length');
$helper->method_export($value, 'Utf8Length');
$helper->method_export($value, 'IsOneByte');
$helper->method_export($value, 'ContainsOnlyOneByte');
$helper->space();

$extensions = [];
$global_template = new \v8\ObjectTemplate($isolate);
$context = new \v8\Context($isolate, $extensions, $global_template);


$helper->header('Primitive converters');
$helper->method_export($value, 'BooleanValue', [$context]);
$helper->method_export($value, 'NumberValue', [$context]);
$helper->space();


$value = new v8\StringValue($isolate, '');

$helper->header('Test empty string constructor');
$helper->method_export($value, 'Value');
$helper->method_export($value, 'BooleanValue', [$context]);
$helper->method_export($value, 'NumberValue', [$context]);
$helper->space();

$value = new v8\StringValue($isolate);

$helper->header('Test default constructor');
$helper->method_export($value, 'Value');
$helper->method_export($value, 'BooleanValue', [$context]);
$helper->method_export($value, 'NumberValue', [$context]);
$helper->space();


$helper->header('Test encodings');

foreach (['Hello, world!', 'Привет, мир!', 'こんにちは世界'] as $text ) {
  $value = new v8\StringValue($isolate, $text);

  $helper->method_export($value, 'Value');
  $helper->method_export($value, 'Length');
  $helper->method_export($value, 'Utf8Length');
  $helper->method_export($value, 'IsOneByte');
  $helper->method_export($value, 'ContainsOnlyOneByte');

  $helper->function_export('strlen', [$value->Value()]);
  $helper->function_export('mb_strlen', [$value->Value()]);
  $helper->space();
}


?>
--EXPECTF--
Default constructor:
--------------------
object(v8\StringValue)#4 (1) refcount(2){
  ["isolate":"v8\Value":private]=>
  object(v8\Isolate)#3 (1) refcount(2){
    ["snapshot":"v8\Isolate":private]=>
    NULL
  }
}
v8\StringValue->Value(): string(0) "" refcount(5)


Object representation:
----------------------
object(v8\StringValue)#5 (1) refcount(2){
  ["isolate":"v8\Value":private]=>
  object(v8\Isolate)#3 (1) refcount(3){
    ["snapshot":"v8\Isolate":private]=>
    NULL
  }
}


StringValue extends NameValue: ok

Accessors:
----------
v8\StringValue::GetIsolate() matches expected value
v8\StringValue->Value(): string(11) "test string" refcount(5)


Checkers:
---------
v8\StringValue->IsOneByte(): bool(true)
v8\StringValue(v8\Value)->IsUndefined(): bool(false)
v8\StringValue(v8\Value)->IsNull(): bool(false)
v8\StringValue(v8\Value)->IsTrue(): bool(false)
v8\StringValue(v8\Value)->IsFalse(): bool(false)
v8\StringValue(v8\Value)->IsName(): bool(true)
v8\StringValue(v8\Value)->IsString(): bool(true)
v8\StringValue(v8\Value)->IsSymbol(): bool(false)
v8\StringValue(v8\Value)->IsFunction(): bool(false)
v8\StringValue(v8\Value)->IsArray(): bool(false)
v8\StringValue(v8\Value)->IsObject(): bool(false)
v8\StringValue(v8\Value)->IsBoolean(): bool(false)
v8\StringValue(v8\Value)->IsNumber(): bool(false)
v8\StringValue(v8\Value)->IsInt32(): bool(false)
v8\StringValue(v8\Value)->IsUint32(): bool(false)
v8\StringValue(v8\Value)->IsDate(): bool(false)
v8\StringValue(v8\Value)->IsArgumentsObject(): bool(false)
v8\StringValue(v8\Value)->IsBooleanObject(): bool(false)
v8\StringValue(v8\Value)->IsNumberObject(): bool(false)
v8\StringValue(v8\Value)->IsStringObject(): bool(false)
v8\StringValue(v8\Value)->IsSymbolObject(): bool(false)
v8\StringValue(v8\Value)->IsNativeError(): bool(false)
v8\StringValue(v8\Value)->IsRegExp(): bool(false)


Getters:
--------
v8\StringValue(v8\NameValue)->GetIdentityHash(): int(1034255942)
v8\StringValue->Length(): int(11)
v8\StringValue->Utf8Length(): int(11)
v8\StringValue->IsOneByte(): bool(true)
v8\StringValue->ContainsOnlyOneByte(): bool(true)


Primitive converters:
---------------------
v8\StringValue(v8\Value)->BooleanValue(): bool(true)
v8\StringValue(v8\Value)->NumberValue(): float(NAN)


Test empty string constructor:
------------------------------
v8\StringValue->Value(): string(0) "" refcount(5)
v8\StringValue(v8\Value)->BooleanValue(): bool(false)
v8\StringValue(v8\Value)->NumberValue(): float(0)


Test default constructor:
-------------------------
v8\StringValue->Value(): string(0) "" refcount(5)
v8\StringValue(v8\Value)->BooleanValue(): bool(false)
v8\StringValue(v8\Value)->NumberValue(): float(0)


Test encodings:
---------------
v8\StringValue->Value(): string(13) "Hello, world!" refcount(5)
v8\StringValue->Length(): int(13)
v8\StringValue->Utf8Length(): int(13)
v8\StringValue->IsOneByte(): bool(true)
v8\StringValue->ContainsOnlyOneByte(): bool(true)
strlen(): 13
mb_strlen(): 13


v8\StringValue->Value(): string(21) "Привет, мир!" refcount(5)
v8\StringValue->Length(): int(12)
v8\StringValue->Utf8Length(): int(21)
v8\StringValue->IsOneByte(): bool(false)
v8\StringValue->ContainsOnlyOneByte(): bool(false)
strlen(): 21
mb_strlen(): 12


v8\StringValue->Value(): string(21) "こんにちは世界" refcount(5)
v8\StringValue->Length(): int(7)
v8\StringValue->Utf8Length(): int(21)
v8\StringValue->IsOneByte(): bool(false)
v8\StringValue->ContainsOnlyOneByte(): bool(false)
strlen(): 21
mb_strlen(): 7
