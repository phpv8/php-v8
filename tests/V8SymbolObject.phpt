--TEST--
V8\SymbolObject
--SKIPIF--
<?php if (!extension_loaded("v8")) {
    print "skip";
} ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

// Tests:


$isolate = new \V8\Isolate();
$context = new V8\Context($isolate);
$v8_helper->injectConsoleLog($context);


$value = new V8\SymbolObject($context, new \V8\SymbolValue($isolate, new \V8\StringValue($isolate, 'test')));

$helper->header('Object representation');
$helper->dump($value);
$helper->space();

$helper->assert('SymbolObject extends ObjectValue', $value instanceof \V8\ObjectValue);
$helper->assert('SymbolObject is instanceof Symbol', $value->InstanceOf($context, $context->GlobalObject()->Get($context, new \V8\StringValue($isolate, 'Symbol'))));
$helper->line();

$helper->header('Getters');
$helper->method_export($value, 'ValueOf');
$helper->pretty_dump('Symbol name:', $value->ValueOf()->Name()->Value());
$helper->space();

$v8_helper->run_checks($value, 'Checkers');

$context->GlobalObject()->Set($context, new \V8\StringValue($isolate, 'val'), $value);

$source    = '
console.log("val: ", val);
console.log("typeof val: ", typeof val);

val
';

$res = $v8_helper->CompileRun($context, $source);

$helper->space();

$helper->header('Returned value should be the same');
$helper->value_matches_with_no_output($res, $value);
$helper->space();

try {
    // This now not allowed, see https://developer.mozilla.org/en/docs/Web/JavaScript/Reference/Global_Objects/Symbol
    $res = $v8_helper->CompileRun($context, 'new Symbol("boxed")');
} catch (Exception $e) {
    $helper->exception_export($e);
}

?>
--EXPECT--
Object representation:
----------------------
object(V8\SymbolObject)#6 (2) {
  ["isolate":"V8\Value":private]=>
  object(V8\Isolate)#3 (4) {
    ["time_limit":"V8\Isolate":private]=>
    float(0)
    ["time_limit_hit":"V8\Isolate":private]=>
    bool(false)
    ["memory_limit":"V8\Isolate":private]=>
    int(0)
    ["memory_limit_hit":"V8\Isolate":private]=>
    bool(false)
  }
  ["context":"V8\ObjectValue":private]=>
  object(V8\Context)#4 (1) {
    ["isolate":"V8\Context":private]=>
    object(V8\Isolate)#3 (4) {
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
}


SymbolObject extends ObjectValue: ok
SymbolObject is instanceof Symbol: ok

Getters:
--------
V8\SymbolObject->ValueOf():
    object(V8\SymbolValue)#119 (1) {
      ["isolate":"V8\Value":private]=>
      object(V8\Isolate)#3 (4) {
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
Symbol name:: string(4) "test"


Checkers:
---------
V8\SymbolObject(V8\Value)->TypeOf(): V8\StringValue->Value(): string(6) "object"

V8\SymbolObject(V8\ObjectValue)->IsCallable(): bool(false)
V8\SymbolObject(V8\ObjectValue)->IsConstructor(): bool(false)
V8\SymbolObject(V8\Value)->IsUndefined(): bool(false)
V8\SymbolObject(V8\Value)->IsNull(): bool(false)
V8\SymbolObject(V8\Value)->IsNullOrUndefined(): bool(false)
V8\SymbolObject(V8\Value)->IsTrue(): bool(false)
V8\SymbolObject(V8\Value)->IsFalse(): bool(false)
V8\SymbolObject(V8\Value)->IsName(): bool(false)
V8\SymbolObject(V8\Value)->IsString(): bool(false)
V8\SymbolObject(V8\Value)->IsSymbol(): bool(false)
V8\SymbolObject(V8\Value)->IsFunction(): bool(false)
V8\SymbolObject(V8\Value)->IsArray(): bool(false)
V8\SymbolObject(V8\Value)->IsObject(): bool(true)
V8\SymbolObject(V8\Value)->IsBoolean(): bool(false)
V8\SymbolObject(V8\Value)->IsNumber(): bool(false)
V8\SymbolObject(V8\Value)->IsInt32(): bool(false)
V8\SymbolObject(V8\Value)->IsUint32(): bool(false)
V8\SymbolObject(V8\Value)->IsDate(): bool(false)
V8\SymbolObject(V8\Value)->IsArgumentsObject(): bool(false)
V8\SymbolObject(V8\Value)->IsBooleanObject(): bool(false)
V8\SymbolObject(V8\Value)->IsNumberObject(): bool(false)
V8\SymbolObject(V8\Value)->IsStringObject(): bool(false)
V8\SymbolObject(V8\Value)->IsSymbolObject(): bool(true)
V8\SymbolObject(V8\Value)->IsNativeError(): bool(false)
V8\SymbolObject(V8\Value)->IsRegExp(): bool(false)
V8\SymbolObject(V8\Value)->IsAsyncFunction(): bool(false)
V8\SymbolObject(V8\Value)->IsGeneratorFunction(): bool(false)
V8\SymbolObject(V8\Value)->IsGeneratorObject(): bool(false)
V8\SymbolObject(V8\Value)->IsPromise(): bool(false)
V8\SymbolObject(V8\Value)->IsMap(): bool(false)
V8\SymbolObject(V8\Value)->IsSet(): bool(false)
V8\SymbolObject(V8\Value)->IsMapIterator(): bool(false)
V8\SymbolObject(V8\Value)->IsSetIterator(): bool(false)
V8\SymbolObject(V8\Value)->IsWeakMap(): bool(false)
V8\SymbolObject(V8\Value)->IsWeakSet(): bool(false)
V8\SymbolObject(V8\Value)->IsArrayBuffer(): bool(false)
V8\SymbolObject(V8\Value)->IsArrayBufferView(): bool(false)
V8\SymbolObject(V8\Value)->IsTypedArray(): bool(false)
V8\SymbolObject(V8\Value)->IsUint8Array(): bool(false)
V8\SymbolObject(V8\Value)->IsUint8ClampedArray(): bool(false)
V8\SymbolObject(V8\Value)->IsInt8Array(): bool(false)
V8\SymbolObject(V8\Value)->IsUint16Array(): bool(false)
V8\SymbolObject(V8\Value)->IsInt16Array(): bool(false)
V8\SymbolObject(V8\Value)->IsUint32Array(): bool(false)
V8\SymbolObject(V8\Value)->IsInt32Array(): bool(false)
V8\SymbolObject(V8\Value)->IsFloat32Array(): bool(false)
V8\SymbolObject(V8\Value)->IsFloat64Array(): bool(false)
V8\SymbolObject(V8\Value)->IsDataView(): bool(false)
V8\SymbolObject(V8\Value)->IsSharedArrayBuffer(): bool(false)
V8\SymbolObject(V8\Value)->IsProxy(): bool(false)


val: {Symbol object: test}
typeof val: object


Returned value should be the same:
----------------------------------
Expected value is identical to actual value


V8\Exceptions\TryCatchException: TypeError: Symbol is not a constructor
