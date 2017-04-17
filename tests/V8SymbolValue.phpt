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
$helper->line();

$helper->header('Accessors');
$helper->method_matches($value, 'GetIsolate', $isolate);
$helper->method_export($value, 'Name');
$helper->assert('GetIdentityHash is integer', gettype($value->GetIdentityHash()), 'integer');
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
$helper->method_matches($value, 'GetIsolate', $isolate);
$helper->method_export($value, 'Name');
$helper->assert('GetIdentityHash is integer', gettype($value->GetIdentityHash()), 'integer');
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
$helper->method_matches($value, 'GetIsolate', $isolate);
$helper->method_export($value, 'Name');
$helper->assert('GetIdentityHash is integer', gettype($value->GetIdentityHash()), 'integer');
$helper->space();

$v8_helper->run_checks($value, 'Checkers');

$helper->header('Symbol name');
$helper->dump($value->Name()->Value());
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
$helper->method_matches($value, 'GetIsolate', $isolate);
$helper->method_export($value, 'Name');
$helper->assert('GetIdentityHash is integer', gettype($value->GetIdentityHash()), 'integer');
$helper->space();

$v8_helper->run_checks($value, 'Checkers');

$helper->header('Symbol name');
$helper->dump($value->Name()->Value());
$helper->line();

$v8_helper->run_checks($value->Name(), 'Checkers on name');


$source = 'Symbol("foo")';
$file_name = 'test.js';
$context = new V8\Context($isolate);

$script = new V8\Script($context, new \V8\StringValue($isolate, $source), new \V8\ScriptOrigin($file_name));
$res = $script->Run($context);

$v8_helper->run_checks($res, 'Checkers on Symbol value from script');


function test_For(\V8\Context $context, PhpV8Testsuite $helper)
{
    $value = V8\SymbolValue::For($context, new \V8\StringValue($context->GetIsolate(), 'test'));
    $helper->assert('Symbol For(string) returned', $value instanceof \V8\SymbolValue);
    $helper->pretty_dump('Symbol For(string) name', $value->Name()->Value());
    $helper->line();
}

function getFunctionForTesting(\V8\Context $context, PhpV8Testsuite $helper, callable $fnc, array $extra_args = []) {
    return new \V8\FunctionObject($context, function (\V8\FunctionCallbackInfo $args) use ($helper, $fnc) {
        $fnc($args->GetContext(), $helper);
    });
}

try {
    test_For($context, $helper);
} catch (Exception $e) {
    $helper->exception_export($e);
}

$context->GlobalObject()->Set($context, new \V8\StringValue($isolate, 'test_For'), getFunctionForTesting($context, $helper, 'test_For'));
$v8_helper->CompileRun($context, 'test_For()');


$helper->assert('Isolate not in context', !$isolate->InContext());
$value = V8\SymbolValue::ForApi($context, new \V8\StringValue($isolate, 'test'));
$helper->assert('Symbol ForApi(string) returned', $value instanceof \V8\SymbolValue);
$helper->pretty_dump('Symbol ForApi(string) name', $value->Name()->Value());
$helper->line();

$helper->assert('Isolate not in context', !$isolate->InContext());
$value = V8\SymbolValue::GetIterator($isolate);
$helper->assert('Symbol GetIterator() returned', $value instanceof \V8\SymbolValue);
$helper->pretty_dump('Symbol GetIterator() name', $value->Name()->Value());
$helper->line();

$helper->assert('Isolate not in context', !$isolate->InContext());
$value = V8\SymbolValue::GetUnscopables($isolate);
$helper->assert('Symbol GetUnscopables() returned', $value instanceof \V8\SymbolValue);
$helper->pretty_dump('Symbol GetUnscopables() name', $value->Name()->Value());
$helper->line();

$helper->assert('Isolate not in context', !$isolate->InContext());
$value = V8\SymbolValue::GetToPrimitive($isolate);
$helper->assert('Symbol GetToPrimitive() returned', $value instanceof \V8\SymbolValue);
$helper->pretty_dump('Symbol GetToPrimitive() name', $value->Name()->Value());
$helper->line();

$helper->assert('Isolate not in context', !$isolate->InContext());
$value = V8\SymbolValue::GetToStringTag($isolate);
$helper->assert('Symbol GetToStringTag() returned', $value instanceof \V8\SymbolValue);
$helper->pretty_dump('Symbol GetToStringTag() name', $value->Name()->Value());
$helper->line();

$helper->assert('Isolate not in context', !$isolate->InContext());
$value = V8\SymbolValue::GetIsConcatSpreadable($isolate);
$helper->assert('Symbol GetIsConcatSpreadable() returned', $value instanceof \V8\SymbolValue);
$helper->pretty_dump('Symbol GetIsConcatSpreadable() name', $value->Name()->Value());
$helper->line();

?>
--EXPECT--
Default constructor:
--------------------

Object representation:
----------------------
object(V8\SymbolValue)#4 (1) {
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


SymbolValue extends NameValue: ok

Accessors:
----------
V8\SymbolValue::GetIsolate() matches expected value
V8\SymbolValue->Name():
    object(V8\Value)#87 (1) {
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
GetIdentityHash is integer: ok


Checkers:
---------
V8\SymbolValue(V8\Value)->TypeOf(): V8\StringValue->Value(): string(6) "symbol"

V8\SymbolValue(V8\Value)->IsUndefined(): bool(false)
V8\SymbolValue(V8\Value)->IsNull(): bool(false)
V8\SymbolValue(V8\Value)->IsNullOrUndefined(): bool(false)
V8\SymbolValue(V8\Value)->IsTrue(): bool(false)
V8\SymbolValue(V8\Value)->IsFalse(): bool(false)
V8\SymbolValue(V8\Value)->IsName(): bool(true)
V8\SymbolValue(V8\Value)->IsString(): bool(false)
V8\SymbolValue(V8\Value)->IsSymbol(): bool(true)
V8\SymbolValue(V8\Value)->IsFunction(): bool(false)
V8\SymbolValue(V8\Value)->IsArray(): bool(false)
V8\SymbolValue(V8\Value)->IsObject(): bool(false)
V8\SymbolValue(V8\Value)->IsBoolean(): bool(false)
V8\SymbolValue(V8\Value)->IsNumber(): bool(false)
V8\SymbolValue(V8\Value)->IsInt32(): bool(false)
V8\SymbolValue(V8\Value)->IsUint32(): bool(false)
V8\SymbolValue(V8\Value)->IsDate(): bool(false)
V8\SymbolValue(V8\Value)->IsArgumentsObject(): bool(false)
V8\SymbolValue(V8\Value)->IsBooleanObject(): bool(false)
V8\SymbolValue(V8\Value)->IsNumberObject(): bool(false)
V8\SymbolValue(V8\Value)->IsStringObject(): bool(false)
V8\SymbolValue(V8\Value)->IsSymbolObject(): bool(false)
V8\SymbolValue(V8\Value)->IsNativeError(): bool(false)
V8\SymbolValue(V8\Value)->IsRegExp(): bool(false)
V8\SymbolValue(V8\Value)->IsAsyncFunction(): bool(false)
V8\SymbolValue(V8\Value)->IsGeneratorFunction(): bool(false)
V8\SymbolValue(V8\Value)->IsGeneratorObject(): bool(false)
V8\SymbolValue(V8\Value)->IsPromise(): bool(false)
V8\SymbolValue(V8\Value)->IsMap(): bool(false)
V8\SymbolValue(V8\Value)->IsSet(): bool(false)
V8\SymbolValue(V8\Value)->IsMapIterator(): bool(false)
V8\SymbolValue(V8\Value)->IsSetIterator(): bool(false)
V8\SymbolValue(V8\Value)->IsWeakMap(): bool(false)
V8\SymbolValue(V8\Value)->IsWeakSet(): bool(false)
V8\SymbolValue(V8\Value)->IsArrayBuffer(): bool(false)
V8\SymbolValue(V8\Value)->IsArrayBufferView(): bool(false)
V8\SymbolValue(V8\Value)->IsTypedArray(): bool(false)
V8\SymbolValue(V8\Value)->IsUint8Array(): bool(false)
V8\SymbolValue(V8\Value)->IsUint8ClampedArray(): bool(false)
V8\SymbolValue(V8\Value)->IsInt8Array(): bool(false)
V8\SymbolValue(V8\Value)->IsUint16Array(): bool(false)
V8\SymbolValue(V8\Value)->IsInt16Array(): bool(false)
V8\SymbolValue(V8\Value)->IsUint32Array(): bool(false)
V8\SymbolValue(V8\Value)->IsInt32Array(): bool(false)
V8\SymbolValue(V8\Value)->IsFloat32Array(): bool(false)
V8\SymbolValue(V8\Value)->IsFloat64Array(): bool(false)
V8\SymbolValue(V8\Value)->IsDataView(): bool(false)
V8\SymbolValue(V8\Value)->IsSharedArrayBuffer(): bool(false)
V8\SymbolValue(V8\Value)->IsProxy(): bool(false)


Null constructor:
-----------------

Object representation:
----------------------
object(V8\SymbolValue)#87 (1) {
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


SymbolValue extends NameValue: ok

Accessors:
----------
V8\SymbolValue::GetIsolate() matches expected value
V8\SymbolValue->Name():
    object(V8\Value)#7 (1) {
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
GetIdentityHash is integer: ok


Checkers:
---------
V8\SymbolValue(V8\Value)->TypeOf(): V8\StringValue->Value(): string(6) "symbol"

V8\SymbolValue(V8\Value)->IsUndefined(): bool(false)
V8\SymbolValue(V8\Value)->IsNull(): bool(false)
V8\SymbolValue(V8\Value)->IsNullOrUndefined(): bool(false)
V8\SymbolValue(V8\Value)->IsTrue(): bool(false)
V8\SymbolValue(V8\Value)->IsFalse(): bool(false)
V8\SymbolValue(V8\Value)->IsName(): bool(true)
V8\SymbolValue(V8\Value)->IsString(): bool(false)
V8\SymbolValue(V8\Value)->IsSymbol(): bool(true)
V8\SymbolValue(V8\Value)->IsFunction(): bool(false)
V8\SymbolValue(V8\Value)->IsArray(): bool(false)
V8\SymbolValue(V8\Value)->IsObject(): bool(false)
V8\SymbolValue(V8\Value)->IsBoolean(): bool(false)
V8\SymbolValue(V8\Value)->IsNumber(): bool(false)
V8\SymbolValue(V8\Value)->IsInt32(): bool(false)
V8\SymbolValue(V8\Value)->IsUint32(): bool(false)
V8\SymbolValue(V8\Value)->IsDate(): bool(false)
V8\SymbolValue(V8\Value)->IsArgumentsObject(): bool(false)
V8\SymbolValue(V8\Value)->IsBooleanObject(): bool(false)
V8\SymbolValue(V8\Value)->IsNumberObject(): bool(false)
V8\SymbolValue(V8\Value)->IsStringObject(): bool(false)
V8\SymbolValue(V8\Value)->IsSymbolObject(): bool(false)
V8\SymbolValue(V8\Value)->IsNativeError(): bool(false)
V8\SymbolValue(V8\Value)->IsRegExp(): bool(false)
V8\SymbolValue(V8\Value)->IsAsyncFunction(): bool(false)
V8\SymbolValue(V8\Value)->IsGeneratorFunction(): bool(false)
V8\SymbolValue(V8\Value)->IsGeneratorObject(): bool(false)
V8\SymbolValue(V8\Value)->IsPromise(): bool(false)
V8\SymbolValue(V8\Value)->IsMap(): bool(false)
V8\SymbolValue(V8\Value)->IsSet(): bool(false)
V8\SymbolValue(V8\Value)->IsMapIterator(): bool(false)
V8\SymbolValue(V8\Value)->IsSetIterator(): bool(false)
V8\SymbolValue(V8\Value)->IsWeakMap(): bool(false)
V8\SymbolValue(V8\Value)->IsWeakSet(): bool(false)
V8\SymbolValue(V8\Value)->IsArrayBuffer(): bool(false)
V8\SymbolValue(V8\Value)->IsArrayBufferView(): bool(false)
V8\SymbolValue(V8\Value)->IsTypedArray(): bool(false)
V8\SymbolValue(V8\Value)->IsUint8Array(): bool(false)
V8\SymbolValue(V8\Value)->IsUint8ClampedArray(): bool(false)
V8\SymbolValue(V8\Value)->IsInt8Array(): bool(false)
V8\SymbolValue(V8\Value)->IsUint16Array(): bool(false)
V8\SymbolValue(V8\Value)->IsInt16Array(): bool(false)
V8\SymbolValue(V8\Value)->IsUint32Array(): bool(false)
V8\SymbolValue(V8\Value)->IsInt32Array(): bool(false)
V8\SymbolValue(V8\Value)->IsFloat32Array(): bool(false)
V8\SymbolValue(V8\Value)->IsFloat64Array(): bool(false)
V8\SymbolValue(V8\Value)->IsDataView(): bool(false)
V8\SymbolValue(V8\Value)->IsSharedArrayBuffer(): bool(false)
V8\SymbolValue(V8\Value)->IsProxy(): bool(false)


Empty StringValue constructor:
------------------------------

Object representation:
----------------------
object(V8\SymbolValue)#7 (1) {
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


SymbolValue extends NameValue: ok

Accessors:
----------
V8\SymbolValue::GetIsolate() matches expected value
V8\SymbolValue->Name():
    object(V8\StringValue)#8 (1) {
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
GetIdentityHash is integer: ok


Checkers:
---------
V8\SymbolValue(V8\Value)->TypeOf(): V8\StringValue->Value(): string(6) "symbol"

V8\SymbolValue(V8\Value)->IsUndefined(): bool(false)
V8\SymbolValue(V8\Value)->IsNull(): bool(false)
V8\SymbolValue(V8\Value)->IsNullOrUndefined(): bool(false)
V8\SymbolValue(V8\Value)->IsTrue(): bool(false)
V8\SymbolValue(V8\Value)->IsFalse(): bool(false)
V8\SymbolValue(V8\Value)->IsName(): bool(true)
V8\SymbolValue(V8\Value)->IsString(): bool(false)
V8\SymbolValue(V8\Value)->IsSymbol(): bool(true)
V8\SymbolValue(V8\Value)->IsFunction(): bool(false)
V8\SymbolValue(V8\Value)->IsArray(): bool(false)
V8\SymbolValue(V8\Value)->IsObject(): bool(false)
V8\SymbolValue(V8\Value)->IsBoolean(): bool(false)
V8\SymbolValue(V8\Value)->IsNumber(): bool(false)
V8\SymbolValue(V8\Value)->IsInt32(): bool(false)
V8\SymbolValue(V8\Value)->IsUint32(): bool(false)
V8\SymbolValue(V8\Value)->IsDate(): bool(false)
V8\SymbolValue(V8\Value)->IsArgumentsObject(): bool(false)
V8\SymbolValue(V8\Value)->IsBooleanObject(): bool(false)
V8\SymbolValue(V8\Value)->IsNumberObject(): bool(false)
V8\SymbolValue(V8\Value)->IsStringObject(): bool(false)
V8\SymbolValue(V8\Value)->IsSymbolObject(): bool(false)
V8\SymbolValue(V8\Value)->IsNativeError(): bool(false)
V8\SymbolValue(V8\Value)->IsRegExp(): bool(false)
V8\SymbolValue(V8\Value)->IsAsyncFunction(): bool(false)
V8\SymbolValue(V8\Value)->IsGeneratorFunction(): bool(false)
V8\SymbolValue(V8\Value)->IsGeneratorObject(): bool(false)
V8\SymbolValue(V8\Value)->IsPromise(): bool(false)
V8\SymbolValue(V8\Value)->IsMap(): bool(false)
V8\SymbolValue(V8\Value)->IsSet(): bool(false)
V8\SymbolValue(V8\Value)->IsMapIterator(): bool(false)
V8\SymbolValue(V8\Value)->IsSetIterator(): bool(false)
V8\SymbolValue(V8\Value)->IsWeakMap(): bool(false)
V8\SymbolValue(V8\Value)->IsWeakSet(): bool(false)
V8\SymbolValue(V8\Value)->IsArrayBuffer(): bool(false)
V8\SymbolValue(V8\Value)->IsArrayBufferView(): bool(false)
V8\SymbolValue(V8\Value)->IsTypedArray(): bool(false)
V8\SymbolValue(V8\Value)->IsUint8Array(): bool(false)
V8\SymbolValue(V8\Value)->IsUint8ClampedArray(): bool(false)
V8\SymbolValue(V8\Value)->IsInt8Array(): bool(false)
V8\SymbolValue(V8\Value)->IsUint16Array(): bool(false)
V8\SymbolValue(V8\Value)->IsInt16Array(): bool(false)
V8\SymbolValue(V8\Value)->IsUint32Array(): bool(false)
V8\SymbolValue(V8\Value)->IsInt32Array(): bool(false)
V8\SymbolValue(V8\Value)->IsFloat32Array(): bool(false)
V8\SymbolValue(V8\Value)->IsFloat64Array(): bool(false)
V8\SymbolValue(V8\Value)->IsDataView(): bool(false)
V8\SymbolValue(V8\Value)->IsSharedArrayBuffer(): bool(false)
V8\SymbolValue(V8\Value)->IsProxy(): bool(false)


Symbol name:
------------
string(0) ""

Non-empty StringValue constructor:
----------------------------------

Object representation:
----------------------
object(V8\SymbolValue)#8 (1) {
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


SymbolValue extends NameValue: ok

Accessors:
----------
V8\SymbolValue::GetIsolate() matches expected value
V8\SymbolValue->Name():
    object(V8\StringValue)#88 (1) {
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
GetIdentityHash is integer: ok


Checkers:
---------
V8\SymbolValue(V8\Value)->TypeOf(): V8\StringValue->Value(): string(6) "symbol"

V8\SymbolValue(V8\Value)->IsUndefined(): bool(false)
V8\SymbolValue(V8\Value)->IsNull(): bool(false)
V8\SymbolValue(V8\Value)->IsNullOrUndefined(): bool(false)
V8\SymbolValue(V8\Value)->IsTrue(): bool(false)
V8\SymbolValue(V8\Value)->IsFalse(): bool(false)
V8\SymbolValue(V8\Value)->IsName(): bool(true)
V8\SymbolValue(V8\Value)->IsString(): bool(false)
V8\SymbolValue(V8\Value)->IsSymbol(): bool(true)
V8\SymbolValue(V8\Value)->IsFunction(): bool(false)
V8\SymbolValue(V8\Value)->IsArray(): bool(false)
V8\SymbolValue(V8\Value)->IsObject(): bool(false)
V8\SymbolValue(V8\Value)->IsBoolean(): bool(false)
V8\SymbolValue(V8\Value)->IsNumber(): bool(false)
V8\SymbolValue(V8\Value)->IsInt32(): bool(false)
V8\SymbolValue(V8\Value)->IsUint32(): bool(false)
V8\SymbolValue(V8\Value)->IsDate(): bool(false)
V8\SymbolValue(V8\Value)->IsArgumentsObject(): bool(false)
V8\SymbolValue(V8\Value)->IsBooleanObject(): bool(false)
V8\SymbolValue(V8\Value)->IsNumberObject(): bool(false)
V8\SymbolValue(V8\Value)->IsStringObject(): bool(false)
V8\SymbolValue(V8\Value)->IsSymbolObject(): bool(false)
V8\SymbolValue(V8\Value)->IsNativeError(): bool(false)
V8\SymbolValue(V8\Value)->IsRegExp(): bool(false)
V8\SymbolValue(V8\Value)->IsAsyncFunction(): bool(false)
V8\SymbolValue(V8\Value)->IsGeneratorFunction(): bool(false)
V8\SymbolValue(V8\Value)->IsGeneratorObject(): bool(false)
V8\SymbolValue(V8\Value)->IsPromise(): bool(false)
V8\SymbolValue(V8\Value)->IsMap(): bool(false)
V8\SymbolValue(V8\Value)->IsSet(): bool(false)
V8\SymbolValue(V8\Value)->IsMapIterator(): bool(false)
V8\SymbolValue(V8\Value)->IsSetIterator(): bool(false)
V8\SymbolValue(V8\Value)->IsWeakMap(): bool(false)
V8\SymbolValue(V8\Value)->IsWeakSet(): bool(false)
V8\SymbolValue(V8\Value)->IsArrayBuffer(): bool(false)
V8\SymbolValue(V8\Value)->IsArrayBufferView(): bool(false)
V8\SymbolValue(V8\Value)->IsTypedArray(): bool(false)
V8\SymbolValue(V8\Value)->IsUint8Array(): bool(false)
V8\SymbolValue(V8\Value)->IsUint8ClampedArray(): bool(false)
V8\SymbolValue(V8\Value)->IsInt8Array(): bool(false)
V8\SymbolValue(V8\Value)->IsUint16Array(): bool(false)
V8\SymbolValue(V8\Value)->IsInt16Array(): bool(false)
V8\SymbolValue(V8\Value)->IsUint32Array(): bool(false)
V8\SymbolValue(V8\Value)->IsInt32Array(): bool(false)
V8\SymbolValue(V8\Value)->IsFloat32Array(): bool(false)
V8\SymbolValue(V8\Value)->IsFloat64Array(): bool(false)
V8\SymbolValue(V8\Value)->IsDataView(): bool(false)
V8\SymbolValue(V8\Value)->IsSharedArrayBuffer(): bool(false)
V8\SymbolValue(V8\Value)->IsProxy(): bool(false)


Symbol name:
------------
string(4) "test"

Checkers on name:
-----------------
V8\StringValue(V8\Value)->TypeOf(): V8\StringValue->Value(): string(6) "string"

V8\StringValue->IsOneByte(): bool(true)
V8\StringValue(V8\Value)->IsUndefined(): bool(false)
V8\StringValue(V8\Value)->IsNull(): bool(false)
V8\StringValue(V8\Value)->IsNullOrUndefined(): bool(false)
V8\StringValue(V8\Value)->IsTrue(): bool(false)
V8\StringValue(V8\Value)->IsFalse(): bool(false)
V8\StringValue(V8\Value)->IsName(): bool(true)
V8\StringValue(V8\Value)->IsString(): bool(true)
V8\StringValue(V8\Value)->IsSymbol(): bool(false)
V8\StringValue(V8\Value)->IsFunction(): bool(false)
V8\StringValue(V8\Value)->IsArray(): bool(false)
V8\StringValue(V8\Value)->IsObject(): bool(false)
V8\StringValue(V8\Value)->IsBoolean(): bool(false)
V8\StringValue(V8\Value)->IsNumber(): bool(false)
V8\StringValue(V8\Value)->IsInt32(): bool(false)
V8\StringValue(V8\Value)->IsUint32(): bool(false)
V8\StringValue(V8\Value)->IsDate(): bool(false)
V8\StringValue(V8\Value)->IsArgumentsObject(): bool(false)
V8\StringValue(V8\Value)->IsBooleanObject(): bool(false)
V8\StringValue(V8\Value)->IsNumberObject(): bool(false)
V8\StringValue(V8\Value)->IsStringObject(): bool(false)
V8\StringValue(V8\Value)->IsSymbolObject(): bool(false)
V8\StringValue(V8\Value)->IsNativeError(): bool(false)
V8\StringValue(V8\Value)->IsRegExp(): bool(false)
V8\StringValue(V8\Value)->IsAsyncFunction(): bool(false)
V8\StringValue(V8\Value)->IsGeneratorFunction(): bool(false)
V8\StringValue(V8\Value)->IsGeneratorObject(): bool(false)
V8\StringValue(V8\Value)->IsPromise(): bool(false)
V8\StringValue(V8\Value)->IsMap(): bool(false)
V8\StringValue(V8\Value)->IsSet(): bool(false)
V8\StringValue(V8\Value)->IsMapIterator(): bool(false)
V8\StringValue(V8\Value)->IsSetIterator(): bool(false)
V8\StringValue(V8\Value)->IsWeakMap(): bool(false)
V8\StringValue(V8\Value)->IsWeakSet(): bool(false)
V8\StringValue(V8\Value)->IsArrayBuffer(): bool(false)
V8\StringValue(V8\Value)->IsArrayBufferView(): bool(false)
V8\StringValue(V8\Value)->IsTypedArray(): bool(false)
V8\StringValue(V8\Value)->IsUint8Array(): bool(false)
V8\StringValue(V8\Value)->IsUint8ClampedArray(): bool(false)
V8\StringValue(V8\Value)->IsInt8Array(): bool(false)
V8\StringValue(V8\Value)->IsUint16Array(): bool(false)
V8\StringValue(V8\Value)->IsInt16Array(): bool(false)
V8\StringValue(V8\Value)->IsUint32Array(): bool(false)
V8\StringValue(V8\Value)->IsInt32Array(): bool(false)
V8\StringValue(V8\Value)->IsFloat32Array(): bool(false)
V8\StringValue(V8\Value)->IsFloat64Array(): bool(false)
V8\StringValue(V8\Value)->IsDataView(): bool(false)
V8\StringValue(V8\Value)->IsSharedArrayBuffer(): bool(false)
V8\StringValue(V8\Value)->IsProxy(): bool(false)


Checkers on Symbol value from script:
-------------------------------------
V8\SymbolValue(V8\Value)->TypeOf(): V8\StringValue->Value(): string(6) "symbol"

V8\SymbolValue(V8\Value)->IsUndefined(): bool(false)
V8\SymbolValue(V8\Value)->IsNull(): bool(false)
V8\SymbolValue(V8\Value)->IsNullOrUndefined(): bool(false)
V8\SymbolValue(V8\Value)->IsTrue(): bool(false)
V8\SymbolValue(V8\Value)->IsFalse(): bool(false)
V8\SymbolValue(V8\Value)->IsName(): bool(true)
V8\SymbolValue(V8\Value)->IsString(): bool(false)
V8\SymbolValue(V8\Value)->IsSymbol(): bool(true)
V8\SymbolValue(V8\Value)->IsFunction(): bool(false)
V8\SymbolValue(V8\Value)->IsArray(): bool(false)
V8\SymbolValue(V8\Value)->IsObject(): bool(false)
V8\SymbolValue(V8\Value)->IsBoolean(): bool(false)
V8\SymbolValue(V8\Value)->IsNumber(): bool(false)
V8\SymbolValue(V8\Value)->IsInt32(): bool(false)
V8\SymbolValue(V8\Value)->IsUint32(): bool(false)
V8\SymbolValue(V8\Value)->IsDate(): bool(false)
V8\SymbolValue(V8\Value)->IsArgumentsObject(): bool(false)
V8\SymbolValue(V8\Value)->IsBooleanObject(): bool(false)
V8\SymbolValue(V8\Value)->IsNumberObject(): bool(false)
V8\SymbolValue(V8\Value)->IsStringObject(): bool(false)
V8\SymbolValue(V8\Value)->IsSymbolObject(): bool(false)
V8\SymbolValue(V8\Value)->IsNativeError(): bool(false)
V8\SymbolValue(V8\Value)->IsRegExp(): bool(false)
V8\SymbolValue(V8\Value)->IsAsyncFunction(): bool(false)
V8\SymbolValue(V8\Value)->IsGeneratorFunction(): bool(false)
V8\SymbolValue(V8\Value)->IsGeneratorObject(): bool(false)
V8\SymbolValue(V8\Value)->IsPromise(): bool(false)
V8\SymbolValue(V8\Value)->IsMap(): bool(false)
V8\SymbolValue(V8\Value)->IsSet(): bool(false)
V8\SymbolValue(V8\Value)->IsMapIterator(): bool(false)
V8\SymbolValue(V8\Value)->IsSetIterator(): bool(false)
V8\SymbolValue(V8\Value)->IsWeakMap(): bool(false)
V8\SymbolValue(V8\Value)->IsWeakSet(): bool(false)
V8\SymbolValue(V8\Value)->IsArrayBuffer(): bool(false)
V8\SymbolValue(V8\Value)->IsArrayBufferView(): bool(false)
V8\SymbolValue(V8\Value)->IsTypedArray(): bool(false)
V8\SymbolValue(V8\Value)->IsUint8Array(): bool(false)
V8\SymbolValue(V8\Value)->IsUint8ClampedArray(): bool(false)
V8\SymbolValue(V8\Value)->IsInt8Array(): bool(false)
V8\SymbolValue(V8\Value)->IsUint16Array(): bool(false)
V8\SymbolValue(V8\Value)->IsInt16Array(): bool(false)
V8\SymbolValue(V8\Value)->IsUint32Array(): bool(false)
V8\SymbolValue(V8\Value)->IsInt32Array(): bool(false)
V8\SymbolValue(V8\Value)->IsFloat32Array(): bool(false)
V8\SymbolValue(V8\Value)->IsFloat64Array(): bool(false)
V8\SymbolValue(V8\Value)->IsDataView(): bool(false)
V8\SymbolValue(V8\Value)->IsSharedArrayBuffer(): bool(false)
V8\SymbolValue(V8\Value)->IsProxy(): bool(false)


Symbol For(string) returned: ok
Symbol For(string) name: string(4) "test"

Symbol For(string) returned: ok
Symbol For(string) name: string(4) "test"

Isolate not in context: ok
Symbol ForApi(string) returned: ok
Symbol ForApi(string) name: string(4) "test"

Isolate not in context: ok
Symbol GetIterator() returned: ok
Symbol GetIterator() name: string(15) "Symbol.iterator"

Isolate not in context: ok
Symbol GetUnscopables() returned: ok
Symbol GetUnscopables() name: string(18) "Symbol.unscopables"

Isolate not in context: ok
Symbol GetToPrimitive() returned: ok
Symbol GetToPrimitive() name: string(18) "Symbol.toPrimitive"

Isolate not in context: ok
Symbol GetToStringTag() returned: ok
Symbol GetToStringTag() name: string(18) "Symbol.toStringTag"

Isolate not in context: ok
Symbol GetIsConcatSpreadable() returned: ok
Symbol GetIsConcatSpreadable() name: string(25) "Symbol.isConcatSpreadable"
