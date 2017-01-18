--TEST--
V8\NameValue
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
$value = new V8\NameValue($isolate);


$helper->header('Object representation');
$helper->dump($value);
$helper->space();

$helper->assert('NameValue extends PrimitiveValue', $value instanceof \V8\PrimitiveValue);
$helper->line();

$helper->header('Accessors');
$helper->method_matches($value, 'GetIsolate', $isolate);
$helper->space();

$helper->header('Getters');
$helper->assert('GetIdentityHash is integer', gettype($value->GetIdentityHash()), 'integer');
$helper->space();


$v8_helper->run_checks($value);

$extensions = [];
$global_template = new \V8\ObjectTemplate($isolate);
$context = new \V8\Context($isolate, $extensions, $global_template);


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
object(V8\NameValue)#4 (1) {
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


NameValue extends PrimitiveValue: ok

Accessors:
----------
V8\NameValue::GetIsolate() matches expected value


Getters:
--------
GetIdentityHash is integer: ok


Checks on V8\NameValue:
-----------------------
V8\NameValue(V8\Value)->TypeOf(): V8\StringValue->Value(): string(9) "undefined"

V8\NameValue(V8\Value)->IsUndefined(): bool(true)
V8\NameValue(V8\Value)->IsNull(): bool(false)
V8\NameValue(V8\Value)->IsNullOrUndefined(): bool(true)
V8\NameValue(V8\Value)->IsTrue(): bool(false)
V8\NameValue(V8\Value)->IsFalse(): bool(false)
V8\NameValue(V8\Value)->IsName(): bool(false)
V8\NameValue(V8\Value)->IsString(): bool(false)
V8\NameValue(V8\Value)->IsSymbol(): bool(false)
V8\NameValue(V8\Value)->IsFunction(): bool(false)
V8\NameValue(V8\Value)->IsArray(): bool(false)
V8\NameValue(V8\Value)->IsObject(): bool(false)
V8\NameValue(V8\Value)->IsBoolean(): bool(false)
V8\NameValue(V8\Value)->IsNumber(): bool(false)
V8\NameValue(V8\Value)->IsInt32(): bool(false)
V8\NameValue(V8\Value)->IsUint32(): bool(false)
V8\NameValue(V8\Value)->IsDate(): bool(false)
V8\NameValue(V8\Value)->IsArgumentsObject(): bool(false)
V8\NameValue(V8\Value)->IsBooleanObject(): bool(false)
V8\NameValue(V8\Value)->IsNumberObject(): bool(false)
V8\NameValue(V8\Value)->IsStringObject(): bool(false)
V8\NameValue(V8\Value)->IsSymbolObject(): bool(false)
V8\NameValue(V8\Value)->IsNativeError(): bool(false)
V8\NameValue(V8\Value)->IsRegExp(): bool(false)


Primitive converters:
---------------------
V8\NameValue(V8\Value)->BooleanValue(): bool(false)
V8\NameValue(V8\Value)->NumberValue(): float(NAN)


V8\NameValue::ToString() converting:
------------------------------------
object(V8\StringValue)#53 (1) {
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
string(9) "undefined"


Checkers after ToString() converting:
-------------------------------------
V8\NameValue(V8\Value)->TypeOf(): V8\StringValue->Value(): string(9) "undefined"

V8\NameValue(V8\Value)->IsUndefined(): bool(true)
V8\NameValue(V8\Value)->IsNull(): bool(false)
V8\NameValue(V8\Value)->IsNullOrUndefined(): bool(true)
V8\NameValue(V8\Value)->IsTrue(): bool(false)
V8\NameValue(V8\Value)->IsFalse(): bool(false)
V8\NameValue(V8\Value)->IsName(): bool(false)
V8\NameValue(V8\Value)->IsString(): bool(false)
V8\NameValue(V8\Value)->IsSymbol(): bool(false)
V8\NameValue(V8\Value)->IsFunction(): bool(false)
V8\NameValue(V8\Value)->IsArray(): bool(false)
V8\NameValue(V8\Value)->IsObject(): bool(false)
V8\NameValue(V8\Value)->IsBoolean(): bool(false)
V8\NameValue(V8\Value)->IsNumber(): bool(false)
V8\NameValue(V8\Value)->IsInt32(): bool(false)
V8\NameValue(V8\Value)->IsUint32(): bool(false)
V8\NameValue(V8\Value)->IsDate(): bool(false)
V8\NameValue(V8\Value)->IsArgumentsObject(): bool(false)
V8\NameValue(V8\Value)->IsBooleanObject(): bool(false)
V8\NameValue(V8\Value)->IsNumberObject(): bool(false)
V8\NameValue(V8\Value)->IsStringObject(): bool(false)
V8\NameValue(V8\Value)->IsSymbolObject(): bool(false)
V8\NameValue(V8\Value)->IsNativeError(): bool(false)
V8\NameValue(V8\Value)->IsRegExp(): bool(false)


V8\NameValue::ToObject() converting:
------------------------------------
V8\Exceptions\TryCatchException: TypeError: Cannot convert undefined or null to object
