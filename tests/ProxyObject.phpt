--TEST--
V8\ProxyObject
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

$isolate = new \V8\Isolate();
$context = new V8\Context($isolate);

$target = new \V8\ObjectValue($context);
$handler = new \V8\ObjectValue($context);
$value = new V8\ProxyObject($context, $target, $handler);

$helper->header('Object representation');
$helper->dump($value);
$helper->space();

$helper->assert('ProxyObject extends Value', $value instanceof \V8\Value);
$helper->assert('ProxyObject does not extend PrimitiveValue', !($value instanceof \V8\PrimitiveValue));
$helper->assert('ProxyObject implements AdjustableExternalMemoryInterface', $value instanceof \V8\AdjustableExternalMemoryInterface);
$helper->assert('ProxyObject is instanceof Proxy', $value->instanceOf($context, $context->globalObject()->get($context, new \V8\StringValue($isolate, 'Proxy'))));
$helper->line();

$helper->header('Accessors');
$helper->method_matches($value, 'getIsolate', $isolate);
$helper->method_matches($value, 'getContext', $context);
$helper->space();

$helper->header('Getters');
$helper->assert('GetIdentityHash is integer', gettype($value->getIdentityHash()), 'integer');
$helper->space();

$v8_helper->run_checks($value, 'Checkers');


$helper->header('New value creation from V8 runtime');
$new_value = $v8_helper->CompileRun($context, "new Proxy({}, {})");
$helper->assert('New set from V8 is instance of \V8\ProxyObject', $new_value instanceof \V8\ProxyObject);
$helper->space();

$helper->header('Object representation');
$helper->dump($new_value);
$helper->space();

$v8_helper->run_checks($new_value, 'Checkers');

?>
--EXPECT--
Object representation:
----------------------
object(V8\ProxyObject)#7 (2) {
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


ProxyObject extends Value: ok
ProxyObject does not extend PrimitiveValue: ok
ProxyObject implements AdjustableExternalMemoryInterface: ok
ProxyObject is instanceof Proxy: failed

Accessors:
----------
V8\ProxyObject::getIsolate() matches expected value
V8\ProxyObject::getContext() matches expected value


Getters:
--------
GetIdentityHash is integer: ok


Checkers:
---------
V8\ProxyObject(V8\Value)->typeOf(): V8\StringValue->value(): string(6) "object"

V8\ProxyObject->isRevoked(): bool(false)
V8\ProxyObject(V8\ObjectValue)->isCallable(): bool(false)
V8\ProxyObject(V8\ObjectValue)->isConstructor(): bool(false)
V8\ProxyObject(V8\Value)->isUndefined(): bool(false)
V8\ProxyObject(V8\Value)->isNull(): bool(false)
V8\ProxyObject(V8\Value)->isNullOrUndefined(): bool(false)
V8\ProxyObject(V8\Value)->isTrue(): bool(false)
V8\ProxyObject(V8\Value)->isFalse(): bool(false)
V8\ProxyObject(V8\Value)->isName(): bool(false)
V8\ProxyObject(V8\Value)->isString(): bool(false)
V8\ProxyObject(V8\Value)->isSymbol(): bool(false)
V8\ProxyObject(V8\Value)->isFunction(): bool(false)
V8\ProxyObject(V8\Value)->isArray(): bool(false)
V8\ProxyObject(V8\Value)->isObject(): bool(true)
V8\ProxyObject(V8\Value)->isBoolean(): bool(false)
V8\ProxyObject(V8\Value)->isNumber(): bool(false)
V8\ProxyObject(V8\Value)->isInt32(): bool(false)
V8\ProxyObject(V8\Value)->isUint32(): bool(false)
V8\ProxyObject(V8\Value)->isDate(): bool(false)
V8\ProxyObject(V8\Value)->isArgumentsObject(): bool(false)
V8\ProxyObject(V8\Value)->isBooleanObject(): bool(false)
V8\ProxyObject(V8\Value)->isNumberObject(): bool(false)
V8\ProxyObject(V8\Value)->isStringObject(): bool(false)
V8\ProxyObject(V8\Value)->isSymbolObject(): bool(false)
V8\ProxyObject(V8\Value)->isNativeError(): bool(false)
V8\ProxyObject(V8\Value)->isRegExp(): bool(false)
V8\ProxyObject(V8\Value)->isAsyncFunction(): bool(false)
V8\ProxyObject(V8\Value)->isGeneratorFunction(): bool(false)
V8\ProxyObject(V8\Value)->isGeneratorObject(): bool(false)
V8\ProxyObject(V8\Value)->isPromise(): bool(false)
V8\ProxyObject(V8\Value)->isMap(): bool(false)
V8\ProxyObject(V8\Value)->isSet(): bool(false)
V8\ProxyObject(V8\Value)->isMapIterator(): bool(false)
V8\ProxyObject(V8\Value)->isSetIterator(): bool(false)
V8\ProxyObject(V8\Value)->isWeakMap(): bool(false)
V8\ProxyObject(V8\Value)->isWeakSet(): bool(false)
V8\ProxyObject(V8\Value)->isArrayBuffer(): bool(false)
V8\ProxyObject(V8\Value)->isArrayBufferView(): bool(false)
V8\ProxyObject(V8\Value)->isTypedArray(): bool(false)
V8\ProxyObject(V8\Value)->isUint8Array(): bool(false)
V8\ProxyObject(V8\Value)->isUint8ClampedArray(): bool(false)
V8\ProxyObject(V8\Value)->isInt8Array(): bool(false)
V8\ProxyObject(V8\Value)->isUint16Array(): bool(false)
V8\ProxyObject(V8\Value)->isInt16Array(): bool(false)
V8\ProxyObject(V8\Value)->isUint32Array(): bool(false)
V8\ProxyObject(V8\Value)->isInt32Array(): bool(false)
V8\ProxyObject(V8\Value)->isFloat32Array(): bool(false)
V8\ProxyObject(V8\Value)->isFloat64Array(): bool(false)
V8\ProxyObject(V8\Value)->isDataView(): bool(false)
V8\ProxyObject(V8\Value)->isSharedArrayBuffer(): bool(false)
V8\ProxyObject(V8\Value)->isProxy(): bool(true)


New value creation from V8 runtime:
-----------------------------------
New set from V8 is instance of \V8\ProxyObject: ok


Object representation:
----------------------
object(V8\ProxyObject)#8 (2) {
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


Checkers:
---------
V8\ProxyObject(V8\Value)->typeOf(): V8\StringValue->value(): string(6) "object"

V8\ProxyObject->isRevoked(): bool(false)
V8\ProxyObject(V8\ObjectValue)->isCallable(): bool(false)
V8\ProxyObject(V8\ObjectValue)->isConstructor(): bool(false)
V8\ProxyObject(V8\Value)->isUndefined(): bool(false)
V8\ProxyObject(V8\Value)->isNull(): bool(false)
V8\ProxyObject(V8\Value)->isNullOrUndefined(): bool(false)
V8\ProxyObject(V8\Value)->isTrue(): bool(false)
V8\ProxyObject(V8\Value)->isFalse(): bool(false)
V8\ProxyObject(V8\Value)->isName(): bool(false)
V8\ProxyObject(V8\Value)->isString(): bool(false)
V8\ProxyObject(V8\Value)->isSymbol(): bool(false)
V8\ProxyObject(V8\Value)->isFunction(): bool(false)
V8\ProxyObject(V8\Value)->isArray(): bool(false)
V8\ProxyObject(V8\Value)->isObject(): bool(true)
V8\ProxyObject(V8\Value)->isBoolean(): bool(false)
V8\ProxyObject(V8\Value)->isNumber(): bool(false)
V8\ProxyObject(V8\Value)->isInt32(): bool(false)
V8\ProxyObject(V8\Value)->isUint32(): bool(false)
V8\ProxyObject(V8\Value)->isDate(): bool(false)
V8\ProxyObject(V8\Value)->isArgumentsObject(): bool(false)
V8\ProxyObject(V8\Value)->isBooleanObject(): bool(false)
V8\ProxyObject(V8\Value)->isNumberObject(): bool(false)
V8\ProxyObject(V8\Value)->isStringObject(): bool(false)
V8\ProxyObject(V8\Value)->isSymbolObject(): bool(false)
V8\ProxyObject(V8\Value)->isNativeError(): bool(false)
V8\ProxyObject(V8\Value)->isRegExp(): bool(false)
V8\ProxyObject(V8\Value)->isAsyncFunction(): bool(false)
V8\ProxyObject(V8\Value)->isGeneratorFunction(): bool(false)
V8\ProxyObject(V8\Value)->isGeneratorObject(): bool(false)
V8\ProxyObject(V8\Value)->isPromise(): bool(false)
V8\ProxyObject(V8\Value)->isMap(): bool(false)
V8\ProxyObject(V8\Value)->isSet(): bool(false)
V8\ProxyObject(V8\Value)->isMapIterator(): bool(false)
V8\ProxyObject(V8\Value)->isSetIterator(): bool(false)
V8\ProxyObject(V8\Value)->isWeakMap(): bool(false)
V8\ProxyObject(V8\Value)->isWeakSet(): bool(false)
V8\ProxyObject(V8\Value)->isArrayBuffer(): bool(false)
V8\ProxyObject(V8\Value)->isArrayBufferView(): bool(false)
V8\ProxyObject(V8\Value)->isTypedArray(): bool(false)
V8\ProxyObject(V8\Value)->isUint8Array(): bool(false)
V8\ProxyObject(V8\Value)->isUint8ClampedArray(): bool(false)
V8\ProxyObject(V8\Value)->isInt8Array(): bool(false)
V8\ProxyObject(V8\Value)->isUint16Array(): bool(false)
V8\ProxyObject(V8\Value)->isInt16Array(): bool(false)
V8\ProxyObject(V8\Value)->isUint32Array(): bool(false)
V8\ProxyObject(V8\Value)->isInt32Array(): bool(false)
V8\ProxyObject(V8\Value)->isFloat32Array(): bool(false)
V8\ProxyObject(V8\Value)->isFloat64Array(): bool(false)
V8\ProxyObject(V8\Value)->isDataView(): bool(false)
V8\ProxyObject(V8\Value)->isSharedArrayBuffer(): bool(false)
V8\ProxyObject(V8\Value)->isProxy(): bool(true)
