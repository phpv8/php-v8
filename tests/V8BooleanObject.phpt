--TEST--
V8\BooleanObject
--SKIPIF--
<?php if (!extension_loaded("v8")) { print "skip"; } ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

$isolate = new \V8\Isolate();
$context = new V8\Context($isolate);
$v8_helper->injectConsoleLog($context);

$value = new V8\BooleanObject($context, true);

$helper->header('Object representation');
$helper->dump($value);
$helper->space();

$helper->assert('BooleanObject extends ObjectValue', $value instanceof \V8\ObjectValue);
$helper->line();


$helper->header('Getters');
$helper->method_export($value, 'ValueOf');
$helper->space();

$v8_helper->run_checks($value, 'Checkers');

$context->GlobalObject()->Set($context, new \V8\StringValue($isolate, 'val'), $value);

$source = '
console.log("val: ", val);
console.log("typeof val: ", typeof val);

new Boolean(false);
';

$res = $v8_helper->CompileRun($context, $source);
$helper->space();

$v8_helper->run_checks($res, 'Checkers on boxed from script');

?>
--EXPECT--
Object representation:
----------------------
object(V8\BooleanObject)#6 (2) {
  ["isolate":"V8\Value":private]=>
  object(V8\Isolate)#3 (0) {
  }
  ["context":"V8\ObjectValue":private]=>
  object(V8\Context)#4 (1) {
    ["isolate":"V8\Context":private]=>
    object(V8\Isolate)#3 (0) {
    }
  }
}


BooleanObject extends ObjectValue: ok

Getters:
--------
V8\BooleanObject->ValueOf(): bool(true)


Checkers:
---------
V8\BooleanObject(V8\Value)->TypeOf(): V8\StringValue->Value(): string(6) "object"

V8\BooleanObject(V8\ObjectValue)->IsCallable(): bool(false)
V8\BooleanObject(V8\ObjectValue)->IsConstructor(): bool(false)
V8\BooleanObject(V8\Value)->IsUndefined(): bool(false)
V8\BooleanObject(V8\Value)->IsNull(): bool(false)
V8\BooleanObject(V8\Value)->IsNullOrUndefined(): bool(false)
V8\BooleanObject(V8\Value)->IsTrue(): bool(false)
V8\BooleanObject(V8\Value)->IsFalse(): bool(false)
V8\BooleanObject(V8\Value)->IsName(): bool(false)
V8\BooleanObject(V8\Value)->IsString(): bool(false)
V8\BooleanObject(V8\Value)->IsSymbol(): bool(false)
V8\BooleanObject(V8\Value)->IsFunction(): bool(false)
V8\BooleanObject(V8\Value)->IsArray(): bool(false)
V8\BooleanObject(V8\Value)->IsObject(): bool(true)
V8\BooleanObject(V8\Value)->IsBoolean(): bool(false)
V8\BooleanObject(V8\Value)->IsNumber(): bool(false)
V8\BooleanObject(V8\Value)->IsInt32(): bool(false)
V8\BooleanObject(V8\Value)->IsUint32(): bool(false)
V8\BooleanObject(V8\Value)->IsDate(): bool(false)
V8\BooleanObject(V8\Value)->IsArgumentsObject(): bool(false)
V8\BooleanObject(V8\Value)->IsBooleanObject(): bool(true)
V8\BooleanObject(V8\Value)->IsNumberObject(): bool(false)
V8\BooleanObject(V8\Value)->IsStringObject(): bool(false)
V8\BooleanObject(V8\Value)->IsSymbolObject(): bool(false)
V8\BooleanObject(V8\Value)->IsNativeError(): bool(false)
V8\BooleanObject(V8\Value)->IsRegExp(): bool(false)
V8\BooleanObject(V8\Value)->IsAsyncFunction(): bool(false)
V8\BooleanObject(V8\Value)->IsGeneratorFunction(): bool(false)
V8\BooleanObject(V8\Value)->IsGeneratorObject(): bool(false)
V8\BooleanObject(V8\Value)->IsPromise(): bool(false)
V8\BooleanObject(V8\Value)->IsMap(): bool(false)
V8\BooleanObject(V8\Value)->IsSet(): bool(false)
V8\BooleanObject(V8\Value)->IsMapIterator(): bool(false)
V8\BooleanObject(V8\Value)->IsSetIterator(): bool(false)
V8\BooleanObject(V8\Value)->IsWeakMap(): bool(false)
V8\BooleanObject(V8\Value)->IsWeakSet(): bool(false)
V8\BooleanObject(V8\Value)->IsArrayBuffer(): bool(false)
V8\BooleanObject(V8\Value)->IsArrayBufferView(): bool(false)
V8\BooleanObject(V8\Value)->IsTypedArray(): bool(false)
V8\BooleanObject(V8\Value)->IsUint8Array(): bool(false)
V8\BooleanObject(V8\Value)->IsUint8ClampedArray(): bool(false)
V8\BooleanObject(V8\Value)->IsInt8Array(): bool(false)
V8\BooleanObject(V8\Value)->IsUint16Array(): bool(false)
V8\BooleanObject(V8\Value)->IsInt16Array(): bool(false)
V8\BooleanObject(V8\Value)->IsUint32Array(): bool(false)
V8\BooleanObject(V8\Value)->IsInt32Array(): bool(false)
V8\BooleanObject(V8\Value)->IsFloat32Array(): bool(false)
V8\BooleanObject(V8\Value)->IsFloat64Array(): bool(false)
V8\BooleanObject(V8\Value)->IsDataView(): bool(false)
V8\BooleanObject(V8\Value)->IsSharedArrayBuffer(): bool(false)
V8\BooleanObject(V8\Value)->IsProxy(): bool(false)


val: true
typeof val: object


Checkers on boxed from script:
------------------------------
V8\BooleanObject(V8\Value)->TypeOf(): V8\StringValue->Value(): string(6) "object"

V8\BooleanObject(V8\ObjectValue)->IsCallable(): bool(false)
V8\BooleanObject(V8\ObjectValue)->IsConstructor(): bool(false)
V8\BooleanObject(V8\Value)->IsUndefined(): bool(false)
V8\BooleanObject(V8\Value)->IsNull(): bool(false)
V8\BooleanObject(V8\Value)->IsNullOrUndefined(): bool(false)
V8\BooleanObject(V8\Value)->IsTrue(): bool(false)
V8\BooleanObject(V8\Value)->IsFalse(): bool(false)
V8\BooleanObject(V8\Value)->IsName(): bool(false)
V8\BooleanObject(V8\Value)->IsString(): bool(false)
V8\BooleanObject(V8\Value)->IsSymbol(): bool(false)
V8\BooleanObject(V8\Value)->IsFunction(): bool(false)
V8\BooleanObject(V8\Value)->IsArray(): bool(false)
V8\BooleanObject(V8\Value)->IsObject(): bool(true)
V8\BooleanObject(V8\Value)->IsBoolean(): bool(false)
V8\BooleanObject(V8\Value)->IsNumber(): bool(false)
V8\BooleanObject(V8\Value)->IsInt32(): bool(false)
V8\BooleanObject(V8\Value)->IsUint32(): bool(false)
V8\BooleanObject(V8\Value)->IsDate(): bool(false)
V8\BooleanObject(V8\Value)->IsArgumentsObject(): bool(false)
V8\BooleanObject(V8\Value)->IsBooleanObject(): bool(true)
V8\BooleanObject(V8\Value)->IsNumberObject(): bool(false)
V8\BooleanObject(V8\Value)->IsStringObject(): bool(false)
V8\BooleanObject(V8\Value)->IsSymbolObject(): bool(false)
V8\BooleanObject(V8\Value)->IsNativeError(): bool(false)
V8\BooleanObject(V8\Value)->IsRegExp(): bool(false)
V8\BooleanObject(V8\Value)->IsAsyncFunction(): bool(false)
V8\BooleanObject(V8\Value)->IsGeneratorFunction(): bool(false)
V8\BooleanObject(V8\Value)->IsGeneratorObject(): bool(false)
V8\BooleanObject(V8\Value)->IsPromise(): bool(false)
V8\BooleanObject(V8\Value)->IsMap(): bool(false)
V8\BooleanObject(V8\Value)->IsSet(): bool(false)
V8\BooleanObject(V8\Value)->IsMapIterator(): bool(false)
V8\BooleanObject(V8\Value)->IsSetIterator(): bool(false)
V8\BooleanObject(V8\Value)->IsWeakMap(): bool(false)
V8\BooleanObject(V8\Value)->IsWeakSet(): bool(false)
V8\BooleanObject(V8\Value)->IsArrayBuffer(): bool(false)
V8\BooleanObject(V8\Value)->IsArrayBufferView(): bool(false)
V8\BooleanObject(V8\Value)->IsTypedArray(): bool(false)
V8\BooleanObject(V8\Value)->IsUint8Array(): bool(false)
V8\BooleanObject(V8\Value)->IsUint8ClampedArray(): bool(false)
V8\BooleanObject(V8\Value)->IsInt8Array(): bool(false)
V8\BooleanObject(V8\Value)->IsUint16Array(): bool(false)
V8\BooleanObject(V8\Value)->IsInt16Array(): bool(false)
V8\BooleanObject(V8\Value)->IsUint32Array(): bool(false)
V8\BooleanObject(V8\Value)->IsInt32Array(): bool(false)
V8\BooleanObject(V8\Value)->IsFloat32Array(): bool(false)
V8\BooleanObject(V8\Value)->IsFloat64Array(): bool(false)
V8\BooleanObject(V8\Value)->IsDataView(): bool(false)
V8\BooleanObject(V8\Value)->IsSharedArrayBuffer(): bool(false)
V8\BooleanObject(V8\Value)->IsProxy(): bool(false)
