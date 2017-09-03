--TEST--
V8\StringValue
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--ENV--
HOME=/tmp/we-need-home-env-var-set-to-load-valgrindrc
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

// Tests:

$isolate = new V8\Isolate();

$default = new V8\StringValue($isolate);
$helper->header('Default constructor');
$helper->dump($default);
$helper->method_export($default, 'value');
$helper->space();


$value = new V8\StringValue($isolate, 'test string');

$helper->header('Object representation');
$helper->dump($value);
$helper->space();

$helper->assert('StringValue extends NameValue', $value instanceof \V8\NameValue);
$helper->assert('StringValue extends Value', $value instanceof \V8\Value);
$helper->line();

$helper->header('Class constants');
$helper->dump_object_constants($value);
$helper->space();

$helper->header('Accessors');
$helper->method_matches($value, 'getIsolate', $isolate);
$helper->method_export($value, 'value');
$helper->space();

$v8_helper->run_checks($value, 'Checkers');

$helper->header('Getters');
$helper->assert('GetIdentityHash is integer', gettype($value->getIdentityHash()), 'integer');
$helper->method_export($value, 'length');
$helper->method_export($value, 'utf8Length');
$helper->method_export($value, 'isOneByte');
$helper->method_export($value, 'containsOnlyOneByte');
$helper->space();

$global_template = new \V8\ObjectTemplate($isolate);
$context = new \V8\Context($isolate, $global_template);


$helper->header('Primitive converters');
$helper->method_export($value, 'booleanValue', [$context]);
$helper->method_export($value, 'numberValue', [$context]);
$helper->space();


$value = new V8\StringValue($isolate, '');

$helper->header('Test empty string constructor');
$helper->method_export($value, 'value');
$helper->method_export($value, 'booleanValue', [$context]);
$helper->method_export($value, 'numberValue', [$context]);
$helper->space();

$value = new V8\StringValue($isolate);

$helper->header('Test default constructor');
$helper->method_export($value, 'value');
$helper->method_export($value, 'booleanValue', [$context]);
$helper->method_export($value, 'numberValue', [$context]);
$helper->space();


$helper->header('Test encodings');

foreach (['Hello, world!', 'Привет, мир!', 'こんにちは世界'] as $text ) {
  $value = new V8\StringValue($isolate, $text);

  $helper->method_export($value, 'value');
  $helper->method_export($value, 'length');
  $helper->method_export($value, 'utf8Length');
  $helper->method_export($value, 'isOneByte');
  $helper->method_export($value, 'containsOnlyOneByte');

  $helper->function_export('strlen', [$value->value()]);
  $helper->space();
}


?>
--EXPECT--
Default constructor:
--------------------
object(V8\StringValue)#4 (1) {
  ["isolate":"V8\Value":private]=>
  object(V8\Isolate)#3 (0) {
  }
}
V8\StringValue->value(): string(0) ""


Object representation:
----------------------
object(V8\StringValue)#5 (1) {
  ["isolate":"V8\Value":private]=>
  object(V8\Isolate)#3 (0) {
  }
}


StringValue extends NameValue: ok
StringValue extends Value: ok

Class constants:
----------------
V8\StringValue::MAX_LENGTH = 1073741799


Accessors:
----------
V8\StringValue::getIsolate() matches expected value
V8\StringValue->value(): string(11) "test string"


Checkers:
---------
V8\StringValue(V8\Value)->typeOf(): V8\StringValue->value(): string(6) "string"

V8\StringValue->isOneByte(): bool(true)
V8\StringValue(V8\Value)->isUndefined(): bool(false)
V8\StringValue(V8\Value)->isNull(): bool(false)
V8\StringValue(V8\Value)->isNullOrUndefined(): bool(false)
V8\StringValue(V8\Value)->isTrue(): bool(false)
V8\StringValue(V8\Value)->isFalse(): bool(false)
V8\StringValue(V8\Value)->isName(): bool(true)
V8\StringValue(V8\Value)->isString(): bool(true)
V8\StringValue(V8\Value)->isSymbol(): bool(false)
V8\StringValue(V8\Value)->isFunction(): bool(false)
V8\StringValue(V8\Value)->isArray(): bool(false)
V8\StringValue(V8\Value)->isObject(): bool(false)
V8\StringValue(V8\Value)->isBoolean(): bool(false)
V8\StringValue(V8\Value)->isNumber(): bool(false)
V8\StringValue(V8\Value)->isInt32(): bool(false)
V8\StringValue(V8\Value)->isUint32(): bool(false)
V8\StringValue(V8\Value)->isDate(): bool(false)
V8\StringValue(V8\Value)->isArgumentsObject(): bool(false)
V8\StringValue(V8\Value)->isBooleanObject(): bool(false)
V8\StringValue(V8\Value)->isNumberObject(): bool(false)
V8\StringValue(V8\Value)->isStringObject(): bool(false)
V8\StringValue(V8\Value)->isSymbolObject(): bool(false)
V8\StringValue(V8\Value)->isNativeError(): bool(false)
V8\StringValue(V8\Value)->isRegExp(): bool(false)
V8\StringValue(V8\Value)->isAsyncFunction(): bool(false)
V8\StringValue(V8\Value)->isGeneratorFunction(): bool(false)
V8\StringValue(V8\Value)->isGeneratorObject(): bool(false)
V8\StringValue(V8\Value)->isPromise(): bool(false)
V8\StringValue(V8\Value)->isMap(): bool(false)
V8\StringValue(V8\Value)->isSet(): bool(false)
V8\StringValue(V8\Value)->isMapIterator(): bool(false)
V8\StringValue(V8\Value)->isSetIterator(): bool(false)
V8\StringValue(V8\Value)->isWeakMap(): bool(false)
V8\StringValue(V8\Value)->isWeakSet(): bool(false)
V8\StringValue(V8\Value)->isArrayBuffer(): bool(false)
V8\StringValue(V8\Value)->isArrayBufferView(): bool(false)
V8\StringValue(V8\Value)->isTypedArray(): bool(false)
V8\StringValue(V8\Value)->isUint8Array(): bool(false)
V8\StringValue(V8\Value)->isUint8ClampedArray(): bool(false)
V8\StringValue(V8\Value)->isInt8Array(): bool(false)
V8\StringValue(V8\Value)->isUint16Array(): bool(false)
V8\StringValue(V8\Value)->isInt16Array(): bool(false)
V8\StringValue(V8\Value)->isUint32Array(): bool(false)
V8\StringValue(V8\Value)->isInt32Array(): bool(false)
V8\StringValue(V8\Value)->isFloat32Array(): bool(false)
V8\StringValue(V8\Value)->isFloat64Array(): bool(false)
V8\StringValue(V8\Value)->isDataView(): bool(false)
V8\StringValue(V8\Value)->isSharedArrayBuffer(): bool(false)
V8\StringValue(V8\Value)->isProxy(): bool(false)


Getters:
--------
GetIdentityHash is integer: ok
V8\StringValue->length(): int(11)
V8\StringValue->utf8Length(): int(11)
V8\StringValue->isOneByte(): bool(true)
V8\StringValue->containsOnlyOneByte(): bool(true)


Primitive converters:
---------------------
V8\StringValue(V8\Value)->booleanValue(): bool(true)
V8\StringValue(V8\Value)->numberValue(): float(NAN)


Test empty string constructor:
------------------------------
V8\StringValue->value(): string(0) ""
V8\StringValue(V8\Value)->booleanValue(): bool(false)
V8\StringValue(V8\Value)->numberValue(): float(0)


Test default constructor:
-------------------------
V8\StringValue->value(): string(0) ""
V8\StringValue(V8\Value)->booleanValue(): bool(false)
V8\StringValue(V8\Value)->numberValue(): float(0)


Test encodings:
---------------
V8\StringValue->value(): string(13) "Hello, world!"
V8\StringValue->length(): int(13)
V8\StringValue->utf8Length(): int(13)
V8\StringValue->isOneByte(): bool(true)
V8\StringValue->containsOnlyOneByte(): bool(true)
strlen(): 13


V8\StringValue->value(): string(21) "Привет, мир!"
V8\StringValue->length(): int(12)
V8\StringValue->utf8Length(): int(21)
V8\StringValue->isOneByte(): bool(false)
V8\StringValue->containsOnlyOneByte(): bool(false)
strlen(): 21


V8\StringValue->value(): string(21) "こんにちは世界"
V8\StringValue->length(): int(7)
V8\StringValue->utf8Length(): int(21)
V8\StringValue->isOneByte(): bool(false)
V8\StringValue->containsOnlyOneByte(): bool(false)
strlen(): 21
