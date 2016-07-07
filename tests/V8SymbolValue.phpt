--TEST--
v8\SymbolValue
--SKIPIF--
<?php if (!extension_loaded("v8")) { print "skip"; } ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

// Tests:


$isolate = new v8\Isolate();

$value = new v8\SymbolValue($isolate);
$helper->header('Default constructor');
$helper->line();

$helper->header('Object representation');
$helper->dump($value);
$helper->space();

$helper->assert('SymbolValue extends NameValue', $value instanceof \v8\NameValue);
$helper->line();

$helper->header('Accessors');
$helper->method_matches($value, 'GetIsolate', $isolate);
$helper->method_export($value, 'Name');
$helper->method_export($value, 'GetIdentityHash');
$helper->space();

$v8_helper->run_checks($value, 'Checkers');


$value = new v8\SymbolValue($isolate, null);
$helper->header('Null constructor');
$helper->line();

$helper->header('Object representation');
$helper->dump($value);
$helper->space();

$helper->assert('SymbolValue extends NameValue', $value instanceof \v8\NameValue);
$helper->line();

$helper->header('Accessors');
$helper->method_matches($value, 'GetIsolate', $isolate);
$helper->method_export($value, 'Name');
$helper->method_export($value, 'GetIdentityHash');
$helper->space();

$v8_helper->run_checks($value, 'Checkers');

$value = new v8\SymbolValue($isolate, new \v8\StringValue($isolate, ''));
$helper->header('Empty StringValue constructor');
$helper->line();

$helper->header('Object representation');
$helper->dump($value);
$helper->space();

$helper->assert('SymbolValue extends NameValue', $value instanceof \v8\NameValue);
$helper->line();

$helper->header('Accessors');
$helper->method_matches($value, 'GetIsolate', $isolate);
$helper->method_export($value, 'Name');
$helper->method_export($value, 'GetIdentityHash');
$helper->space();

$v8_helper->run_checks($value, 'Checkers');

$helper->header('Symbol name');
$helper->dump($value->Name()->Value());
$helper->line();


$value = new v8\SymbolValue($isolate, new \v8\StringValue($isolate, 'test'));
$helper->header('Non-empty StringValue constructor');
$helper->line();

$helper->header('Object representation');
$helper->dump($value);
$helper->space();

$helper->assert('SymbolValue extends NameValue', $value instanceof \v8\NameValue);
$helper->line();

$helper->header('Accessors');
$helper->method_matches($value, 'GetIsolate', $isolate);
$helper->method_export($value, 'Name');
$helper->method_export($value, 'GetIdentityHash');
$helper->space();

$v8_helper->run_checks($value, 'Checkers');

$helper->header('Symbol name');
$helper->dump($value->Name()->Value());
$helper->line();

$v8_helper->run_checks($value->Name(), 'Checkers on name');


$source = 'Symbol("foo")';
$file_name = 'test.js';
$context = new v8\Context($isolate);

$script = new v8\Script($context, new \v8\StringValue($isolate, $source), new \v8\ScriptOrigin($file_name));
$res = $script->Run();

$v8_helper->run_checks($res, 'Checkers on Symbol value from script');


function test_For(\v8\Context $context, PhpV8Testsuite $helper)
{
    $value = v8\SymbolValue::For($context, new \v8\StringValue($context->GetIsolate(), 'test'));
    $helper->assert('Symbol For(string) returned', $value instanceof \v8\SymbolValue);
    $helper->pretty_dump('Symbol For(string) name', $value->Name()->Value());
    $helper->line();
}

function getFunctionForTesting(\v8\Context $context, PhpV8Testsuite $helper, callable $fnc, array $extra_args = []) {
    return new \v8\FunctionObject($context, function (\v8\FunctionCallbackInfo $args) use ($helper, $fnc) {
        $fnc($args->GetContext(), $helper);
    });
}

try {
    test_For($context, $helper);
} catch (Exception $e) {
    $helper->exception_export($e);
}

$context->GlobalObject()->Set($context, new \v8\StringValue($isolate, 'test_For'), getFunctionForTesting($context, $helper, 'test_For'));
$v8_helper->CompileRun($context, 'test_For()');


$helper->assert('Isolate not in context', !$isolate->InContext());
$value = v8\SymbolValue::ForApi($context, new \v8\StringValue($isolate, 'test'));
$helper->assert('Symbol ForApi(string) returned', $value instanceof \v8\SymbolValue);
$helper->pretty_dump('Symbol ForApi(string) name', $value->Name()->Value());
$helper->line();

$helper->assert('Isolate not in context', !$isolate->InContext());
$value = v8\SymbolValue::GetIterator($isolate);
$helper->assert('Symbol GetIterator() returned', $value instanceof \v8\SymbolValue);
$helper->pretty_dump('Symbol GetIterator() name', $value->Name()->Value());
$helper->line();

$helper->assert('Isolate not in context', !$isolate->InContext());
$value = v8\SymbolValue::GetUnscopables($isolate);
$helper->assert('Symbol GetUnscopables() returned', $value instanceof \v8\SymbolValue);
$helper->pretty_dump('Symbol GetUnscopables() name', $value->Name()->Value());
$helper->line();

$helper->assert('Isolate not in context', !$isolate->InContext());
$value = v8\SymbolValue::GetToStringTag($isolate);
$helper->assert('Symbol GetToStringTag() returned', $value instanceof \v8\SymbolValue);
$helper->pretty_dump('Symbol GetToStringTag() name', $value->Name()->Value());
$helper->line();

$helper->assert('Isolate not in context', !$isolate->InContext());
$value = v8\SymbolValue::GetIsConcatSpreadable($isolate);
$helper->assert('Symbol GetIsConcatSpreadable() returned', $value instanceof \v8\SymbolValue);
$helper->pretty_dump('Symbol GetIsConcatSpreadable() name', $value->Name()->Value());
$helper->line();

?>
--EXPECTF--
Default constructor:
--------------------

Object representation:
----------------------
object(v8\SymbolValue)#4 (1) {
  ["isolate":"v8\Value":private]=>
  object(v8\Isolate)#3 (1) {
    ["snapshot":"v8\Isolate":private]=>
    NULL
  }
}


SymbolValue extends NameValue: ok

Accessors:
----------
v8\SymbolValue::GetIsolate() matches expected value
v8\SymbolValue->Name():
    object(v8\Value)#58 (1) {
      ["isolate":"v8\Value":private]=>
      object(v8\Isolate)#3 (1) {
        ["snapshot":"v8\Isolate":private]=>
        NULL
      }
    }
v8\SymbolValue(v8\NameValue)->GetIdentityHash(): int(%d)


Checkers:
---------
v8\SymbolValue(v8\Value)->IsUndefined(): bool(false)
v8\SymbolValue(v8\Value)->IsNull(): bool(false)
v8\SymbolValue(v8\Value)->IsTrue(): bool(false)
v8\SymbolValue(v8\Value)->IsFalse(): bool(false)
v8\SymbolValue(v8\Value)->IsName(): bool(true)
v8\SymbolValue(v8\Value)->IsString(): bool(false)
v8\SymbolValue(v8\Value)->IsSymbol(): bool(true)
v8\SymbolValue(v8\Value)->IsFunction(): bool(false)
v8\SymbolValue(v8\Value)->IsArray(): bool(false)
v8\SymbolValue(v8\Value)->IsObject(): bool(false)
v8\SymbolValue(v8\Value)->IsBoolean(): bool(false)
v8\SymbolValue(v8\Value)->IsNumber(): bool(false)
v8\SymbolValue(v8\Value)->IsInt32(): bool(false)
v8\SymbolValue(v8\Value)->IsUint32(): bool(false)
v8\SymbolValue(v8\Value)->IsDate(): bool(false)
v8\SymbolValue(v8\Value)->IsArgumentsObject(): bool(false)
v8\SymbolValue(v8\Value)->IsBooleanObject(): bool(false)
v8\SymbolValue(v8\Value)->IsNumberObject(): bool(false)
v8\SymbolValue(v8\Value)->IsStringObject(): bool(false)
v8\SymbolValue(v8\Value)->IsSymbolObject(): bool(false)
v8\SymbolValue(v8\Value)->IsNativeError(): bool(false)
v8\SymbolValue(v8\Value)->IsRegExp(): bool(false)


Null constructor:
-----------------

Object representation:
----------------------
object(v8\SymbolValue)#5 (1) {
  ["isolate":"v8\Value":private]=>
  object(v8\Isolate)#3 (1) {
    ["snapshot":"v8\Isolate":private]=>
    NULL
  }
}


SymbolValue extends NameValue: ok

Accessors:
----------
v8\SymbolValue::GetIsolate() matches expected value
v8\SymbolValue->Name():
    object(v8\Value)#8 (1) {
      ["isolate":"v8\Value":private]=>
      object(v8\Isolate)#3 (1) {
        ["snapshot":"v8\Isolate":private]=>
        NULL
      }
    }
v8\SymbolValue(v8\NameValue)->GetIdentityHash(): int(%d)


Checkers:
---------
v8\SymbolValue(v8\Value)->IsUndefined(): bool(false)
v8\SymbolValue(v8\Value)->IsNull(): bool(false)
v8\SymbolValue(v8\Value)->IsTrue(): bool(false)
v8\SymbolValue(v8\Value)->IsFalse(): bool(false)
v8\SymbolValue(v8\Value)->IsName(): bool(true)
v8\SymbolValue(v8\Value)->IsString(): bool(false)
v8\SymbolValue(v8\Value)->IsSymbol(): bool(true)
v8\SymbolValue(v8\Value)->IsFunction(): bool(false)
v8\SymbolValue(v8\Value)->IsArray(): bool(false)
v8\SymbolValue(v8\Value)->IsObject(): bool(false)
v8\SymbolValue(v8\Value)->IsBoolean(): bool(false)
v8\SymbolValue(v8\Value)->IsNumber(): bool(false)
v8\SymbolValue(v8\Value)->IsInt32(): bool(false)
v8\SymbolValue(v8\Value)->IsUint32(): bool(false)
v8\SymbolValue(v8\Value)->IsDate(): bool(false)
v8\SymbolValue(v8\Value)->IsArgumentsObject(): bool(false)
v8\SymbolValue(v8\Value)->IsBooleanObject(): bool(false)
v8\SymbolValue(v8\Value)->IsNumberObject(): bool(false)
v8\SymbolValue(v8\Value)->IsStringObject(): bool(false)
v8\SymbolValue(v8\Value)->IsSymbolObject(): bool(false)
v8\SymbolValue(v8\Value)->IsNativeError(): bool(false)
v8\SymbolValue(v8\Value)->IsRegExp(): bool(false)


Empty StringValue constructor:
------------------------------

Object representation:
----------------------
object(v8\SymbolValue)#4 (1) {
  ["isolate":"v8\Value":private]=>
  object(v8\Isolate)#3 (1) {
    ["snapshot":"v8\Isolate":private]=>
    NULL
  }
}


SymbolValue extends NameValue: ok

Accessors:
----------
v8\SymbolValue::GetIsolate() matches expected value
v8\SymbolValue->Name():
    object(v8\StringValue)#58 (1) {
      ["isolate":"v8\Value":private]=>
      object(v8\Isolate)#3 (1) {
        ["snapshot":"v8\Isolate":private]=>
        NULL
      }
    }
v8\SymbolValue(v8\NameValue)->GetIdentityHash(): int(%d)


Checkers:
---------
v8\SymbolValue(v8\Value)->IsUndefined(): bool(false)
v8\SymbolValue(v8\Value)->IsNull(): bool(false)
v8\SymbolValue(v8\Value)->IsTrue(): bool(false)
v8\SymbolValue(v8\Value)->IsFalse(): bool(false)
v8\SymbolValue(v8\Value)->IsName(): bool(true)
v8\SymbolValue(v8\Value)->IsString(): bool(false)
v8\SymbolValue(v8\Value)->IsSymbol(): bool(true)
v8\SymbolValue(v8\Value)->IsFunction(): bool(false)
v8\SymbolValue(v8\Value)->IsArray(): bool(false)
v8\SymbolValue(v8\Value)->IsObject(): bool(false)
v8\SymbolValue(v8\Value)->IsBoolean(): bool(false)
v8\SymbolValue(v8\Value)->IsNumber(): bool(false)
v8\SymbolValue(v8\Value)->IsInt32(): bool(false)
v8\SymbolValue(v8\Value)->IsUint32(): bool(false)
v8\SymbolValue(v8\Value)->IsDate(): bool(false)
v8\SymbolValue(v8\Value)->IsArgumentsObject(): bool(false)
v8\SymbolValue(v8\Value)->IsBooleanObject(): bool(false)
v8\SymbolValue(v8\Value)->IsNumberObject(): bool(false)
v8\SymbolValue(v8\Value)->IsStringObject(): bool(false)
v8\SymbolValue(v8\Value)->IsSymbolObject(): bool(false)
v8\SymbolValue(v8\Value)->IsNativeError(): bool(false)
v8\SymbolValue(v8\Value)->IsRegExp(): bool(false)


Symbol name:
------------
string(0) ""

Non-empty StringValue constructor:
----------------------------------

Object representation:
----------------------
object(v8\SymbolValue)#5 (1) {
  ["isolate":"v8\Value":private]=>
  object(v8\Isolate)#3 (1) {
    ["snapshot":"v8\Isolate":private]=>
    NULL
  }
}


SymbolValue extends NameValue: ok

Accessors:
----------
v8\SymbolValue::GetIsolate() matches expected value
v8\SymbolValue->Name():
    object(v8\StringValue)#8 (1) {
      ["isolate":"v8\Value":private]=>
      object(v8\Isolate)#3 (1) {
        ["snapshot":"v8\Isolate":private]=>
        NULL
      }
    }
v8\SymbolValue(v8\NameValue)->GetIdentityHash(): int(%d)


Checkers:
---------
v8\SymbolValue(v8\Value)->IsUndefined(): bool(false)
v8\SymbolValue(v8\Value)->IsNull(): bool(false)
v8\SymbolValue(v8\Value)->IsTrue(): bool(false)
v8\SymbolValue(v8\Value)->IsFalse(): bool(false)
v8\SymbolValue(v8\Value)->IsName(): bool(true)
v8\SymbolValue(v8\Value)->IsString(): bool(false)
v8\SymbolValue(v8\Value)->IsSymbol(): bool(true)
v8\SymbolValue(v8\Value)->IsFunction(): bool(false)
v8\SymbolValue(v8\Value)->IsArray(): bool(false)
v8\SymbolValue(v8\Value)->IsObject(): bool(false)
v8\SymbolValue(v8\Value)->IsBoolean(): bool(false)
v8\SymbolValue(v8\Value)->IsNumber(): bool(false)
v8\SymbolValue(v8\Value)->IsInt32(): bool(false)
v8\SymbolValue(v8\Value)->IsUint32(): bool(false)
v8\SymbolValue(v8\Value)->IsDate(): bool(false)
v8\SymbolValue(v8\Value)->IsArgumentsObject(): bool(false)
v8\SymbolValue(v8\Value)->IsBooleanObject(): bool(false)
v8\SymbolValue(v8\Value)->IsNumberObject(): bool(false)
v8\SymbolValue(v8\Value)->IsStringObject(): bool(false)
v8\SymbolValue(v8\Value)->IsSymbolObject(): bool(false)
v8\SymbolValue(v8\Value)->IsNativeError(): bool(false)
v8\SymbolValue(v8\Value)->IsRegExp(): bool(false)


Symbol name:
------------
string(4) "test"

Checkers on name:
-----------------
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


Checkers on Symbol value from script:
-------------------------------------
v8\SymbolValue(v8\Value)->IsUndefined(): bool(false)
v8\SymbolValue(v8\Value)->IsNull(): bool(false)
v8\SymbolValue(v8\Value)->IsTrue(): bool(false)
v8\SymbolValue(v8\Value)->IsFalse(): bool(false)
v8\SymbolValue(v8\Value)->IsName(): bool(true)
v8\SymbolValue(v8\Value)->IsString(): bool(false)
v8\SymbolValue(v8\Value)->IsSymbol(): bool(true)
v8\SymbolValue(v8\Value)->IsFunction(): bool(false)
v8\SymbolValue(v8\Value)->IsArray(): bool(false)
v8\SymbolValue(v8\Value)->IsObject(): bool(false)
v8\SymbolValue(v8\Value)->IsBoolean(): bool(false)
v8\SymbolValue(v8\Value)->IsNumber(): bool(false)
v8\SymbolValue(v8\Value)->IsInt32(): bool(false)
v8\SymbolValue(v8\Value)->IsUint32(): bool(false)
v8\SymbolValue(v8\Value)->IsDate(): bool(false)
v8\SymbolValue(v8\Value)->IsArgumentsObject(): bool(false)
v8\SymbolValue(v8\Value)->IsBooleanObject(): bool(false)
v8\SymbolValue(v8\Value)->IsNumberObject(): bool(false)
v8\SymbolValue(v8\Value)->IsStringObject(): bool(false)
v8\SymbolValue(v8\Value)->IsSymbolObject(): bool(false)
v8\SymbolValue(v8\Value)->IsNativeError(): bool(false)
v8\SymbolValue(v8\Value)->IsRegExp(): bool(false)


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
Symbol GetToStringTag() returned: ok
Symbol GetToStringTag() name: string(18) "Symbol.toStringTag"

Isolate not in context: ok
Symbol GetIsConcatSpreadable() returned: ok
Symbol GetIsConcatSpreadable() name: string(25) "Symbol.isConcatSpreadable"
