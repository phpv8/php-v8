--TEST--
V8\SymbolValue
--SKIPIF--
<?php if (!extension_loaded("v8")) { print "skip"; } ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

// Tests:


$isolate = new V8\Isolate();

$value = new V8\SymbolValue($isolate);
$helper->header('Default constructor');
$helper->line();

$helper->header('Object representation');
$helper->dump($value);
$helper->space();

$helper->assert('SymbolValue extends NameValue', $value instanceof \V8\NameValue);
$helper->assert('SymbolValue extends Value', $value instanceof \V8\Value);
$helper->line();

$helper->header('Accessors');
$helper->method_matches($value, 'getIsolate', $isolate);
$helper->method_export($value, 'value');
$helper->assert('Name() is undefined', $value->name() instanceof \V8\UndefinedValue);
$helper->assert('GetIdentityHash is integer', gettype($value->getIdentityHash()), 'integer');
$helper->space();

$v8_helper->run_checks($value, 'Checkers');


$value = new V8\SymbolValue($isolate, null);
$helper->header('Null constructor');
$helper->line();

$helper->header('Object representation');
$helper->dump($value);
$helper->space();

$helper->assert('SymbolValue extends NameValue', $value instanceof \V8\NameValue);
$helper->line();

$helper->header('Accessors');
$helper->method_matches($value, 'getIsolate', $isolate);
$helper->method_export($value, 'value');
$helper->assert('Name() is undefined', $value->name() instanceof \V8\UndefinedValue);
$helper->assert('GetIdentityHash is integer', gettype($value->getIdentityHash()), 'integer');
$helper->space();

$v8_helper->run_checks($value, 'Checkers');

$value = new V8\SymbolValue($isolate, new \V8\StringValue($isolate, ''));
$helper->header('Empty StringValue constructor');
$helper->line();

$helper->header('Object representation');
$helper->dump($value);
$helper->space();

$helper->assert('SymbolValue extends NameValue', $value instanceof \V8\NameValue);
$helper->line();

$helper->header('Accessors');
$helper->method_matches($value, 'getIsolate', $isolate);
$helper->method_export($value, 'value');
$helper->assert('Name() is String', $value->name() instanceof \V8\StringValue);
$helper->assert('GetIdentityHash is integer', gettype($value->getIdentityHash()), 'integer');
$helper->space();

$v8_helper->run_checks($value, 'Checkers');

$helper->header('Symbol name');
$helper->dump($value->name()->value());
$helper->line();


$value = new V8\SymbolValue($isolate, new \V8\StringValue($isolate, 'test'));
$helper->header('Non-empty StringValue constructor');
$helper->line();

$helper->header('Object representation');
$helper->dump($value);
$helper->space();

$helper->assert('SymbolValue extends NameValue', $value instanceof \V8\NameValue);
$helper->line();

$helper->header('Accessors');
$helper->method_matches($value, 'getIsolate', $isolate);
$helper->method_export($value, 'value');
$helper->assert('Name() is String', $value->name() instanceof \V8\StringValue);
$helper->assert('GetIdentityHash is integer', gettype($value->getIdentityHash()), 'integer');
$helper->space();

$v8_helper->run_checks($value, 'Checkers');

$helper->header('Symbol name');
$helper->dump($value->name()->value());
$helper->line();

$v8_helper->run_checks($value->name(), 'Checkers on name');


$source = 'Symbol("foo")';
$file_name = 'test.js';
$context = new V8\Context($isolate);

$res = $v8_helper->CompileRun($context, $source);

$v8_helper->run_checks($res, 'Checkers on Symbol value from script');


function test_For(\V8\Context $context, PhpV8Testsuite $helper)
{
    $value = V8\SymbolValue::for($context, new \V8\StringValue($context->getIsolate(), 'test'));
    $helper->assert('Symbol For(string) returned', $value instanceof \V8\SymbolValue);
    $helper->pretty_dump('Symbol For(string) name', $value->name()->value());
    $helper->line();
}

function getFunctionForTesting(\V8\Context $context, PhpV8Testsuite $helper, callable $fnc, array $extra_args = []) {
    return new \V8\FunctionObject($context, function (\V8\FunctionCallbackInfo $args) use ($helper, $fnc) {
        $fnc($args->getContext(), $helper);
    });
}

try {
    test_For($context, $helper);
} catch (Exception $e) {
    $helper->exception_export($e);
}

$context->globalObject()->set($context, new \V8\StringValue($isolate, 'test_For'), getFunctionForTesting($context, $helper, 'test_For'));
$v8_helper->CompileRun($context, 'test_For()');


$helper->assert('Isolate not in context', !$isolate->inContext());
$value = V8\SymbolValue::forApi($context, new \V8\StringValue($isolate, 'test'));
$helper->assert('Symbol ForApi(string) returned', $value instanceof \V8\SymbolValue);
$helper->pretty_dump('Symbol ForApi(string) name', $value->name()->value());
$helper->line();

$static_getters = [
    'GetHasInstance',
    'GetIsConcatSpreadable',
    'GetIterator',
    'GetMatch',
    'GetReplace',
    'GetSearch',
    'GetSplit',
    'GetToPrimitive',
    'GetToStringTag',
    'GetUnscopables',
];

foreach ($static_getters as $static_getter) {
    $helper->assert('Isolate not in context', !$isolate->inContext());
    $value = V8\SymbolValue::$static_getter($isolate);
    $helper->assert("Symbol {$static_getter}() returned", $value instanceof \V8\SymbolValue);
    $helper->pretty_dump("Symbol {$static_getter}() name", $value->Name()->value());
    $helper->line();
}

?>
--EXPECT--
Default constructor:
--------------------

Object representation:
----------------------
object(V8\SymbolValue)#4 (1) {
  ["isolate":"V8\Value":private]=>
  object(V8\Isolate)#3 (0) {
  }
}


SymbolValue extends NameValue: ok
SymbolValue extends Value: ok

Accessors:
----------
V8\SymbolValue::getIsolate() matches expected value
V8\SymbolValue->value(): string(0) ""
Name() is undefined: ok
GetIdentityHash is integer: ok


Checkers:
---------
V8\SymbolValue(V8\Value)->typeOf(): V8\StringValue->value(): string(6) "symbol"

V8\SymbolValue(V8\Value)->isUndefined(): bool(false)
V8\SymbolValue(V8\Value)->isNull(): bool(false)
V8\SymbolValue(V8\Value)->isNullOrUndefined(): bool(false)
V8\SymbolValue(V8\Value)->isTrue(): bool(false)
V8\SymbolValue(V8\Value)->isFalse(): bool(false)
V8\SymbolValue(V8\Value)->isName(): bool(true)
V8\SymbolValue(V8\Value)->isString(): bool(false)
V8\SymbolValue(V8\Value)->isSymbol(): bool(true)
V8\SymbolValue(V8\Value)->isFunction(): bool(false)
V8\SymbolValue(V8\Value)->isArray(): bool(false)
V8\SymbolValue(V8\Value)->isObject(): bool(false)
V8\SymbolValue(V8\Value)->isBoolean(): bool(false)
V8\SymbolValue(V8\Value)->isNumber(): bool(false)
V8\SymbolValue(V8\Value)->isInt32(): bool(false)
V8\SymbolValue(V8\Value)->isUint32(): bool(false)
V8\SymbolValue(V8\Value)->isDate(): bool(false)
V8\SymbolValue(V8\Value)->isArgumentsObject(): bool(false)
V8\SymbolValue(V8\Value)->isBooleanObject(): bool(false)
V8\SymbolValue(V8\Value)->isNumberObject(): bool(false)
V8\SymbolValue(V8\Value)->isStringObject(): bool(false)
V8\SymbolValue(V8\Value)->isSymbolObject(): bool(false)
V8\SymbolValue(V8\Value)->isNativeError(): bool(false)
V8\SymbolValue(V8\Value)->isRegExp(): bool(false)
V8\SymbolValue(V8\Value)->isAsyncFunction(): bool(false)
V8\SymbolValue(V8\Value)->isGeneratorFunction(): bool(false)
V8\SymbolValue(V8\Value)->isGeneratorObject(): bool(false)
V8\SymbolValue(V8\Value)->isPromise(): bool(false)
V8\SymbolValue(V8\Value)->isMap(): bool(false)
V8\SymbolValue(V8\Value)->isSet(): bool(false)
V8\SymbolValue(V8\Value)->isMapIterator(): bool(false)
V8\SymbolValue(V8\Value)->isSetIterator(): bool(false)
V8\SymbolValue(V8\Value)->isWeakMap(): bool(false)
V8\SymbolValue(V8\Value)->isWeakSet(): bool(false)
V8\SymbolValue(V8\Value)->isArrayBuffer(): bool(false)
V8\SymbolValue(V8\Value)->isArrayBufferView(): bool(false)
V8\SymbolValue(V8\Value)->isTypedArray(): bool(false)
V8\SymbolValue(V8\Value)->isUint8Array(): bool(false)
V8\SymbolValue(V8\Value)->isUint8ClampedArray(): bool(false)
V8\SymbolValue(V8\Value)->isInt8Array(): bool(false)
V8\SymbolValue(V8\Value)->isUint16Array(): bool(false)
V8\SymbolValue(V8\Value)->isInt16Array(): bool(false)
V8\SymbolValue(V8\Value)->isUint32Array(): bool(false)
V8\SymbolValue(V8\Value)->isInt32Array(): bool(false)
V8\SymbolValue(V8\Value)->isFloat32Array(): bool(false)
V8\SymbolValue(V8\Value)->isFloat64Array(): bool(false)
V8\SymbolValue(V8\Value)->isDataView(): bool(false)
V8\SymbolValue(V8\Value)->isSharedArrayBuffer(): bool(false)
V8\SymbolValue(V8\Value)->isProxy(): bool(false)


Null constructor:
-----------------

Object representation:
----------------------
object(V8\SymbolValue)#92 (1) {
  ["isolate":"V8\Value":private]=>
  object(V8\Isolate)#3 (0) {
  }
}


SymbolValue extends NameValue: ok

Accessors:
----------
V8\SymbolValue::getIsolate() matches expected value
V8\SymbolValue->value(): string(0) ""
Name() is undefined: ok
GetIdentityHash is integer: ok


Checkers:
---------
V8\SymbolValue(V8\Value)->typeOf(): V8\StringValue->value(): string(6) "symbol"

V8\SymbolValue(V8\Value)->isUndefined(): bool(false)
V8\SymbolValue(V8\Value)->isNull(): bool(false)
V8\SymbolValue(V8\Value)->isNullOrUndefined(): bool(false)
V8\SymbolValue(V8\Value)->isTrue(): bool(false)
V8\SymbolValue(V8\Value)->isFalse(): bool(false)
V8\SymbolValue(V8\Value)->isName(): bool(true)
V8\SymbolValue(V8\Value)->isString(): bool(false)
V8\SymbolValue(V8\Value)->isSymbol(): bool(true)
V8\SymbolValue(V8\Value)->isFunction(): bool(false)
V8\SymbolValue(V8\Value)->isArray(): bool(false)
V8\SymbolValue(V8\Value)->isObject(): bool(false)
V8\SymbolValue(V8\Value)->isBoolean(): bool(false)
V8\SymbolValue(V8\Value)->isNumber(): bool(false)
V8\SymbolValue(V8\Value)->isInt32(): bool(false)
V8\SymbolValue(V8\Value)->isUint32(): bool(false)
V8\SymbolValue(V8\Value)->isDate(): bool(false)
V8\SymbolValue(V8\Value)->isArgumentsObject(): bool(false)
V8\SymbolValue(V8\Value)->isBooleanObject(): bool(false)
V8\SymbolValue(V8\Value)->isNumberObject(): bool(false)
V8\SymbolValue(V8\Value)->isStringObject(): bool(false)
V8\SymbolValue(V8\Value)->isSymbolObject(): bool(false)
V8\SymbolValue(V8\Value)->isNativeError(): bool(false)
V8\SymbolValue(V8\Value)->isRegExp(): bool(false)
V8\SymbolValue(V8\Value)->isAsyncFunction(): bool(false)
V8\SymbolValue(V8\Value)->isGeneratorFunction(): bool(false)
V8\SymbolValue(V8\Value)->isGeneratorObject(): bool(false)
V8\SymbolValue(V8\Value)->isPromise(): bool(false)
V8\SymbolValue(V8\Value)->isMap(): bool(false)
V8\SymbolValue(V8\Value)->isSet(): bool(false)
V8\SymbolValue(V8\Value)->isMapIterator(): bool(false)
V8\SymbolValue(V8\Value)->isSetIterator(): bool(false)
V8\SymbolValue(V8\Value)->isWeakMap(): bool(false)
V8\SymbolValue(V8\Value)->isWeakSet(): bool(false)
V8\SymbolValue(V8\Value)->isArrayBuffer(): bool(false)
V8\SymbolValue(V8\Value)->isArrayBufferView(): bool(false)
V8\SymbolValue(V8\Value)->isTypedArray(): bool(false)
V8\SymbolValue(V8\Value)->isUint8Array(): bool(false)
V8\SymbolValue(V8\Value)->isUint8ClampedArray(): bool(false)
V8\SymbolValue(V8\Value)->isInt8Array(): bool(false)
V8\SymbolValue(V8\Value)->isUint16Array(): bool(false)
V8\SymbolValue(V8\Value)->isInt16Array(): bool(false)
V8\SymbolValue(V8\Value)->isUint32Array(): bool(false)
V8\SymbolValue(V8\Value)->isInt32Array(): bool(false)
V8\SymbolValue(V8\Value)->isFloat32Array(): bool(false)
V8\SymbolValue(V8\Value)->isFloat64Array(): bool(false)
V8\SymbolValue(V8\Value)->isDataView(): bool(false)
V8\SymbolValue(V8\Value)->isSharedArrayBuffer(): bool(false)
V8\SymbolValue(V8\Value)->isProxy(): bool(false)


Empty StringValue constructor:
------------------------------

Object representation:
----------------------
object(V8\SymbolValue)#5 (1) {
  ["isolate":"V8\Value":private]=>
  object(V8\Isolate)#3 (0) {
  }
}


SymbolValue extends NameValue: ok

Accessors:
----------
V8\SymbolValue::getIsolate() matches expected value
V8\SymbolValue->value(): string(0) ""
Name() is String: ok
GetIdentityHash is integer: ok


Checkers:
---------
V8\SymbolValue(V8\Value)->typeOf(): V8\StringValue->value(): string(6) "symbol"

V8\SymbolValue(V8\Value)->isUndefined(): bool(false)
V8\SymbolValue(V8\Value)->isNull(): bool(false)
V8\SymbolValue(V8\Value)->isNullOrUndefined(): bool(false)
V8\SymbolValue(V8\Value)->isTrue(): bool(false)
V8\SymbolValue(V8\Value)->isFalse(): bool(false)
V8\SymbolValue(V8\Value)->isName(): bool(true)
V8\SymbolValue(V8\Value)->isString(): bool(false)
V8\SymbolValue(V8\Value)->isSymbol(): bool(true)
V8\SymbolValue(V8\Value)->isFunction(): bool(false)
V8\SymbolValue(V8\Value)->isArray(): bool(false)
V8\SymbolValue(V8\Value)->isObject(): bool(false)
V8\SymbolValue(V8\Value)->isBoolean(): bool(false)
V8\SymbolValue(V8\Value)->isNumber(): bool(false)
V8\SymbolValue(V8\Value)->isInt32(): bool(false)
V8\SymbolValue(V8\Value)->isUint32(): bool(false)
V8\SymbolValue(V8\Value)->isDate(): bool(false)
V8\SymbolValue(V8\Value)->isArgumentsObject(): bool(false)
V8\SymbolValue(V8\Value)->isBooleanObject(): bool(false)
V8\SymbolValue(V8\Value)->isNumberObject(): bool(false)
V8\SymbolValue(V8\Value)->isStringObject(): bool(false)
V8\SymbolValue(V8\Value)->isSymbolObject(): bool(false)
V8\SymbolValue(V8\Value)->isNativeError(): bool(false)
V8\SymbolValue(V8\Value)->isRegExp(): bool(false)
V8\SymbolValue(V8\Value)->isAsyncFunction(): bool(false)
V8\SymbolValue(V8\Value)->isGeneratorFunction(): bool(false)
V8\SymbolValue(V8\Value)->isGeneratorObject(): bool(false)
V8\SymbolValue(V8\Value)->isPromise(): bool(false)
V8\SymbolValue(V8\Value)->isMap(): bool(false)
V8\SymbolValue(V8\Value)->isSet(): bool(false)
V8\SymbolValue(V8\Value)->isMapIterator(): bool(false)
V8\SymbolValue(V8\Value)->isSetIterator(): bool(false)
V8\SymbolValue(V8\Value)->isWeakMap(): bool(false)
V8\SymbolValue(V8\Value)->isWeakSet(): bool(false)
V8\SymbolValue(V8\Value)->isArrayBuffer(): bool(false)
V8\SymbolValue(V8\Value)->isArrayBufferView(): bool(false)
V8\SymbolValue(V8\Value)->isTypedArray(): bool(false)
V8\SymbolValue(V8\Value)->isUint8Array(): bool(false)
V8\SymbolValue(V8\Value)->isUint8ClampedArray(): bool(false)
V8\SymbolValue(V8\Value)->isInt8Array(): bool(false)
V8\SymbolValue(V8\Value)->isUint16Array(): bool(false)
V8\SymbolValue(V8\Value)->isInt16Array(): bool(false)
V8\SymbolValue(V8\Value)->isUint32Array(): bool(false)
V8\SymbolValue(V8\Value)->isInt32Array(): bool(false)
V8\SymbolValue(V8\Value)->isFloat32Array(): bool(false)
V8\SymbolValue(V8\Value)->isFloat64Array(): bool(false)
V8\SymbolValue(V8\Value)->isDataView(): bool(false)
V8\SymbolValue(V8\Value)->isSharedArrayBuffer(): bool(false)
V8\SymbolValue(V8\Value)->isProxy(): bool(false)


Symbol name:
------------
string(0) ""

Non-empty StringValue constructor:
----------------------------------

Object representation:
----------------------
object(V8\SymbolValue)#4 (1) {
  ["isolate":"V8\Value":private]=>
  object(V8\Isolate)#3 (0) {
  }
}


SymbolValue extends NameValue: ok

Accessors:
----------
V8\SymbolValue::getIsolate() matches expected value
V8\SymbolValue->value(): string(4) "test"
Name() is String: ok
GetIdentityHash is integer: ok


Checkers:
---------
V8\SymbolValue(V8\Value)->typeOf(): V8\StringValue->value(): string(6) "symbol"

V8\SymbolValue(V8\Value)->isUndefined(): bool(false)
V8\SymbolValue(V8\Value)->isNull(): bool(false)
V8\SymbolValue(V8\Value)->isNullOrUndefined(): bool(false)
V8\SymbolValue(V8\Value)->isTrue(): bool(false)
V8\SymbolValue(V8\Value)->isFalse(): bool(false)
V8\SymbolValue(V8\Value)->isName(): bool(true)
V8\SymbolValue(V8\Value)->isString(): bool(false)
V8\SymbolValue(V8\Value)->isSymbol(): bool(true)
V8\SymbolValue(V8\Value)->isFunction(): bool(false)
V8\SymbolValue(V8\Value)->isArray(): bool(false)
V8\SymbolValue(V8\Value)->isObject(): bool(false)
V8\SymbolValue(V8\Value)->isBoolean(): bool(false)
V8\SymbolValue(V8\Value)->isNumber(): bool(false)
V8\SymbolValue(V8\Value)->isInt32(): bool(false)
V8\SymbolValue(V8\Value)->isUint32(): bool(false)
V8\SymbolValue(V8\Value)->isDate(): bool(false)
V8\SymbolValue(V8\Value)->isArgumentsObject(): bool(false)
V8\SymbolValue(V8\Value)->isBooleanObject(): bool(false)
V8\SymbolValue(V8\Value)->isNumberObject(): bool(false)
V8\SymbolValue(V8\Value)->isStringObject(): bool(false)
V8\SymbolValue(V8\Value)->isSymbolObject(): bool(false)
V8\SymbolValue(V8\Value)->isNativeError(): bool(false)
V8\SymbolValue(V8\Value)->isRegExp(): bool(false)
V8\SymbolValue(V8\Value)->isAsyncFunction(): bool(false)
V8\SymbolValue(V8\Value)->isGeneratorFunction(): bool(false)
V8\SymbolValue(V8\Value)->isGeneratorObject(): bool(false)
V8\SymbolValue(V8\Value)->isPromise(): bool(false)
V8\SymbolValue(V8\Value)->isMap(): bool(false)
V8\SymbolValue(V8\Value)->isSet(): bool(false)
V8\SymbolValue(V8\Value)->isMapIterator(): bool(false)
V8\SymbolValue(V8\Value)->isSetIterator(): bool(false)
V8\SymbolValue(V8\Value)->isWeakMap(): bool(false)
V8\SymbolValue(V8\Value)->isWeakSet(): bool(false)
V8\SymbolValue(V8\Value)->isArrayBuffer(): bool(false)
V8\SymbolValue(V8\Value)->isArrayBufferView(): bool(false)
V8\SymbolValue(V8\Value)->isTypedArray(): bool(false)
V8\SymbolValue(V8\Value)->isUint8Array(): bool(false)
V8\SymbolValue(V8\Value)->isUint8ClampedArray(): bool(false)
V8\SymbolValue(V8\Value)->isInt8Array(): bool(false)
V8\SymbolValue(V8\Value)->isUint16Array(): bool(false)
V8\SymbolValue(V8\Value)->isInt16Array(): bool(false)
V8\SymbolValue(V8\Value)->isUint32Array(): bool(false)
V8\SymbolValue(V8\Value)->isInt32Array(): bool(false)
V8\SymbolValue(V8\Value)->isFloat32Array(): bool(false)
V8\SymbolValue(V8\Value)->isFloat64Array(): bool(false)
V8\SymbolValue(V8\Value)->isDataView(): bool(false)
V8\SymbolValue(V8\Value)->isSharedArrayBuffer(): bool(false)
V8\SymbolValue(V8\Value)->isProxy(): bool(false)


Symbol name:
------------
string(4) "test"

Checkers on name:
-----------------
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


Checkers on Symbol value from script:
-------------------------------------
V8\SymbolValue(V8\Value)->typeOf(): V8\StringValue->value(): string(6) "symbol"

V8\SymbolValue(V8\Value)->isUndefined(): bool(false)
V8\SymbolValue(V8\Value)->isNull(): bool(false)
V8\SymbolValue(V8\Value)->isNullOrUndefined(): bool(false)
V8\SymbolValue(V8\Value)->isTrue(): bool(false)
V8\SymbolValue(V8\Value)->isFalse(): bool(false)
V8\SymbolValue(V8\Value)->isName(): bool(true)
V8\SymbolValue(V8\Value)->isString(): bool(false)
V8\SymbolValue(V8\Value)->isSymbol(): bool(true)
V8\SymbolValue(V8\Value)->isFunction(): bool(false)
V8\SymbolValue(V8\Value)->isArray(): bool(false)
V8\SymbolValue(V8\Value)->isObject(): bool(false)
V8\SymbolValue(V8\Value)->isBoolean(): bool(false)
V8\SymbolValue(V8\Value)->isNumber(): bool(false)
V8\SymbolValue(V8\Value)->isInt32(): bool(false)
V8\SymbolValue(V8\Value)->isUint32(): bool(false)
V8\SymbolValue(V8\Value)->isDate(): bool(false)
V8\SymbolValue(V8\Value)->isArgumentsObject(): bool(false)
V8\SymbolValue(V8\Value)->isBooleanObject(): bool(false)
V8\SymbolValue(V8\Value)->isNumberObject(): bool(false)
V8\SymbolValue(V8\Value)->isStringObject(): bool(false)
V8\SymbolValue(V8\Value)->isSymbolObject(): bool(false)
V8\SymbolValue(V8\Value)->isNativeError(): bool(false)
V8\SymbolValue(V8\Value)->isRegExp(): bool(false)
V8\SymbolValue(V8\Value)->isAsyncFunction(): bool(false)
V8\SymbolValue(V8\Value)->isGeneratorFunction(): bool(false)
V8\SymbolValue(V8\Value)->isGeneratorObject(): bool(false)
V8\SymbolValue(V8\Value)->isPromise(): bool(false)
V8\SymbolValue(V8\Value)->isMap(): bool(false)
V8\SymbolValue(V8\Value)->isSet(): bool(false)
V8\SymbolValue(V8\Value)->isMapIterator(): bool(false)
V8\SymbolValue(V8\Value)->isSetIterator(): bool(false)
V8\SymbolValue(V8\Value)->isWeakMap(): bool(false)
V8\SymbolValue(V8\Value)->isWeakSet(): bool(false)
V8\SymbolValue(V8\Value)->isArrayBuffer(): bool(false)
V8\SymbolValue(V8\Value)->isArrayBufferView(): bool(false)
V8\SymbolValue(V8\Value)->isTypedArray(): bool(false)
V8\SymbolValue(V8\Value)->isUint8Array(): bool(false)
V8\SymbolValue(V8\Value)->isUint8ClampedArray(): bool(false)
V8\SymbolValue(V8\Value)->isInt8Array(): bool(false)
V8\SymbolValue(V8\Value)->isUint16Array(): bool(false)
V8\SymbolValue(V8\Value)->isInt16Array(): bool(false)
V8\SymbolValue(V8\Value)->isUint32Array(): bool(false)
V8\SymbolValue(V8\Value)->isInt32Array(): bool(false)
V8\SymbolValue(V8\Value)->isFloat32Array(): bool(false)
V8\SymbolValue(V8\Value)->isFloat64Array(): bool(false)
V8\SymbolValue(V8\Value)->isDataView(): bool(false)
V8\SymbolValue(V8\Value)->isSharedArrayBuffer(): bool(false)
V8\SymbolValue(V8\Value)->isProxy(): bool(false)


Symbol For(string) returned: ok
Symbol For(string) name: string(4) "test"

Symbol For(string) returned: ok
Symbol For(string) name: string(4) "test"

Isolate not in context: ok
Symbol ForApi(string) returned: ok
Symbol ForApi(string) name: string(4) "test"

Isolate not in context: ok
Symbol GetHasInstance() returned: ok
Symbol GetHasInstance() name: string(18) "Symbol.hasInstance"

Isolate not in context: ok
Symbol GetIsConcatSpreadable() returned: ok
Symbol GetIsConcatSpreadable() name: string(25) "Symbol.isConcatSpreadable"

Isolate not in context: ok
Symbol GetIterator() returned: ok
Symbol GetIterator() name: string(15) "Symbol.iterator"

Isolate not in context: ok
Symbol GetMatch() returned: ok
Symbol GetMatch() name: string(12) "Symbol.match"

Isolate not in context: ok
Symbol GetReplace() returned: ok
Symbol GetReplace() name: string(14) "Symbol.replace"

Isolate not in context: ok
Symbol GetSearch() returned: ok
Symbol GetSearch() name: string(13) "Symbol.search"

Isolate not in context: ok
Symbol GetSplit() returned: ok
Symbol GetSplit() name: string(12) "Symbol.split"

Isolate not in context: ok
Symbol GetToPrimitive() returned: ok
Symbol GetToPrimitive() name: string(18) "Symbol.toPrimitive"

Isolate not in context: ok
Symbol GetToStringTag() returned: ok
Symbol GetToStringTag() name: string(18) "Symbol.toStringTag"

Isolate not in context: ok
Symbol GetUnscopables() returned: ok
Symbol GetUnscopables() name: string(18) "Symbol.unscopables"
