--TEST--
V8\SetObject
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

$isolate = new \V8\Isolate();
$global_template = new V8\ObjectTemplate($isolate);

$context = new V8\Context($isolate, $global_template);

$value = new V8\SetObject($context);


$helper->header('Object representation');
$helper->dump($value);
$helper->space();

$helper->assert('SetObject extends Value', $value instanceof \V8\Value);
$helper->assert('SetObject does not extend PrimitiveValue', !($value instanceof \V8\PrimitiveValue));
$helper->assert('SetObject implements AdjustableExternalMemoryInterface', $value instanceof \V8\AdjustableExternalMemoryInterface);
$helper->assert('SetObject is instanceof Set', $value->instanceOf($context, $context->globalObject()->get($context, new \V8\StringValue($isolate, 'Set'))));
$helper->line();

$helper->header('Accessors');
$helper->method_matches($value, 'getIsolate', $isolate);
$helper->method_matches($value, 'getContext', $context);
$helper->space();

$helper->header('Getters');
$helper->assert('GetIdentityHash is integer', gettype($value->getIdentityHash()), 'integer');
$helper->space();

$v8_helper->run_checks($value, 'Checkers');

$helper->header('Converters');
$helper->dump_object_methods($value, ['@@default' => [$context]], new RegexpFilter('/^to/'));
$helper->space();


$helper->header('New value creation from V8 runtime');
$filter = new ArrayListFilter(['isObject', 'isMap', 'isWeakMap', 'isSet', 'isWeakSet'], false);
$new_map = $v8_helper->CompileRun($context, "new Set()");
$helper->assert('New set from V8 is instance of \V8\SetObject', $new_map instanceof \V8\SetObject);
$helper->dump_object_methods($new_map, [], $filter);
$helper->line();

$new_map = $v8_helper->CompileRun($context, "new WeakSet()");
$helper->assert('New weak set from V8 is NOT an instance of \V8\SetObject', $new_map instanceof \V8\SetObject, false);
$helper->dump_object_methods($new_map, [], $filter);
$helper->space();


$helper->header('Class-specific methods');

$key = new \V8\ObjectValue($context);
$key2 = new \V8\ObjectValue($context);
$nonexistent_key = new \V8\ObjectValue($context);

$helper->method_export($value, 'size');
$helper->assert('Can add key', $value->add($context, $key), $value);
$helper->assert('Can add another different key', $value->add($context, $key2), $value);
$helper->method_export($value, 'size');
$helper->assert('Cannot add another same key', $value->add($context, $key2), $value);
$helper->method_export($value, 'size');
$helper->line();


$helper->assert('Key exists', $value->has($context, $key));
$helper->assert('Another key exists', $value->has($context, $key2));

$helper->assert('Nonexistent key does not exists', $value->has($context, $nonexistent_key), false);
$helper->line();

$helper->method_export($value, 'size');
$helper->method_matches_instanceof($value, 'asArray', \V8\ArrayObject::class);
$helper->line();

$arr = $value->asArray();
$helper->assert('SetObject Array representation has valid length', $arr->length() == 2);
$helper->assert('SetObject Array contains key', $arr->get($context, new \V8\Uint32Value($isolate, 0)), $key);
$helper->assert('SetObject Array contains another key', $arr->get($context, new \V8\Uint32Value($isolate, 1)), $key2);
$helper->line();

$helper->assert('Delete existent key', $value->delete($context, $key));
$helper->assert('Deleted key does not exists', $value->has($context, $key), false);
$helper->assert('Delete nonexistent key fails', $value->delete($context, $nonexistent_key), false);
$helper->assert('Deleted nonexistent key does not exists', $value->has($context, $nonexistent_key), false);
$helper->method_export($value, 'size');
$helper->line();

$value->add($context, new \V8\ObjectValue($context));
$value->add($context, new \V8\NumberValue($isolate, 42));
$helper->method_export($value, 'size');
$helper->method_export($value, 'clear');
$helper->method_export($value, 'size');


?>
--EXPECT--
Object representation:
----------------------
object(V8\SetObject)#6 (2) {
  ["isolate":"V8\Value":private]=>
  object(V8\Isolate)#3 (0) {
  }
  ["context":"V8\ObjectValue":private]=>
  object(V8\Context)#5 (1) {
    ["isolate":"V8\Context":private]=>
    object(V8\Isolate)#3 (0) {
    }
  }
}


SetObject extends Value: ok
SetObject does not extend PrimitiveValue: ok
SetObject implements AdjustableExternalMemoryInterface: ok
SetObject is instanceof Set: ok

Accessors:
----------
V8\SetObject::getIsolate() matches expected value
V8\SetObject::getContext() matches expected value


Getters:
--------
GetIdentityHash is integer: ok


Checkers:
---------
V8\SetObject(V8\Value)->typeOf(): V8\StringValue->value(): string(6) "object"

V8\SetObject(V8\ObjectValue)->isCallable(): bool(false)
V8\SetObject(V8\ObjectValue)->isConstructor(): bool(false)
V8\SetObject(V8\Value)->isUndefined(): bool(false)
V8\SetObject(V8\Value)->isNull(): bool(false)
V8\SetObject(V8\Value)->isNullOrUndefined(): bool(false)
V8\SetObject(V8\Value)->isTrue(): bool(false)
V8\SetObject(V8\Value)->isFalse(): bool(false)
V8\SetObject(V8\Value)->isName(): bool(false)
V8\SetObject(V8\Value)->isString(): bool(false)
V8\SetObject(V8\Value)->isSymbol(): bool(false)
V8\SetObject(V8\Value)->isFunction(): bool(false)
V8\SetObject(V8\Value)->isArray(): bool(false)
V8\SetObject(V8\Value)->isObject(): bool(true)
V8\SetObject(V8\Value)->isBoolean(): bool(false)
V8\SetObject(V8\Value)->isNumber(): bool(false)
V8\SetObject(V8\Value)->isInt32(): bool(false)
V8\SetObject(V8\Value)->isUint32(): bool(false)
V8\SetObject(V8\Value)->isDate(): bool(false)
V8\SetObject(V8\Value)->isArgumentsObject(): bool(false)
V8\SetObject(V8\Value)->isBooleanObject(): bool(false)
V8\SetObject(V8\Value)->isNumberObject(): bool(false)
V8\SetObject(V8\Value)->isStringObject(): bool(false)
V8\SetObject(V8\Value)->isSymbolObject(): bool(false)
V8\SetObject(V8\Value)->isNativeError(): bool(false)
V8\SetObject(V8\Value)->isRegExp(): bool(false)
V8\SetObject(V8\Value)->isAsyncFunction(): bool(false)
V8\SetObject(V8\Value)->isGeneratorFunction(): bool(false)
V8\SetObject(V8\Value)->isGeneratorObject(): bool(false)
V8\SetObject(V8\Value)->isPromise(): bool(false)
V8\SetObject(V8\Value)->isMap(): bool(false)
V8\SetObject(V8\Value)->isSet(): bool(true)
V8\SetObject(V8\Value)->isMapIterator(): bool(false)
V8\SetObject(V8\Value)->isSetIterator(): bool(false)
V8\SetObject(V8\Value)->isWeakMap(): bool(false)
V8\SetObject(V8\Value)->isWeakSet(): bool(false)
V8\SetObject(V8\Value)->isArrayBuffer(): bool(false)
V8\SetObject(V8\Value)->isArrayBufferView(): bool(false)
V8\SetObject(V8\Value)->isTypedArray(): bool(false)
V8\SetObject(V8\Value)->isUint8Array(): bool(false)
V8\SetObject(V8\Value)->isUint8ClampedArray(): bool(false)
V8\SetObject(V8\Value)->isInt8Array(): bool(false)
V8\SetObject(V8\Value)->isUint16Array(): bool(false)
V8\SetObject(V8\Value)->isInt16Array(): bool(false)
V8\SetObject(V8\Value)->isUint32Array(): bool(false)
V8\SetObject(V8\Value)->isInt32Array(): bool(false)
V8\SetObject(V8\Value)->isFloat32Array(): bool(false)
V8\SetObject(V8\Value)->isFloat64Array(): bool(false)
V8\SetObject(V8\Value)->isBigInt64Array(): bool(false)
V8\SetObject(V8\Value)->isBigUint64Array(): bool(false)
V8\SetObject(V8\Value)->isDataView(): bool(false)
V8\SetObject(V8\Value)->isSharedArrayBuffer(): bool(false)
V8\SetObject(V8\Value)->isProxy(): bool(false)


Converters:
-----------
V8\SetObject(V8\Value)->toBoolean():
    object(V8\BooleanValue)#123 (1) {
      ["isolate":"V8\Value":private]=>
      object(V8\Isolate)#3 (0) {
      }
    }
V8\SetObject(V8\Value)->toNumber():
    object(V8\NumberValue)#123 (1) {
      ["isolate":"V8\Value":private]=>
      object(V8\Isolate)#3 (0) {
      }
    }
V8\SetObject(V8\Value)->toString():
    object(V8\StringValue)#123 (1) {
      ["isolate":"V8\Value":private]=>
      object(V8\Isolate)#3 (0) {
      }
    }
V8\SetObject(V8\Value)->toDetailString():
    object(V8\StringValue)#123 (1) {
      ["isolate":"V8\Value":private]=>
      object(V8\Isolate)#3 (0) {
      }
    }
V8\SetObject(V8\Value)->toObject():
    object(V8\SetObject)#6 (2) {
      ["isolate":"V8\Value":private]=>
      object(V8\Isolate)#3 (0) {
      }
      ["context":"V8\ObjectValue":private]=>
      object(V8\Context)#5 (1) {
        ["isolate":"V8\Context":private]=>
        object(V8\Isolate)#3 (0) {
        }
      }
    }
V8\SetObject(V8\Value)->toInteger():
    object(V8\Int32Value)#123 (1) {
      ["isolate":"V8\Value":private]=>
      object(V8\Isolate)#3 (0) {
      }
    }
V8\SetObject(V8\Value)->toUint32():
    object(V8\Int32Value)#123 (1) {
      ["isolate":"V8\Value":private]=>
      object(V8\Isolate)#3 (0) {
      }
    }
V8\SetObject(V8\Value)->toInt32():
    object(V8\Int32Value)#123 (1) {
      ["isolate":"V8\Value":private]=>
      object(V8\Isolate)#3 (0) {
      }
    }
V8\SetObject(V8\Value)->toArrayIndex(): V8\Exceptions\Exception: Failed to convert


New value creation from V8 runtime:
-----------------------------------
New set from V8 is instance of \V8\SetObject: ok
V8\SetObject(V8\Value)->isObject(): bool(true)
V8\SetObject(V8\Value)->isMap(): bool(false)
V8\SetObject(V8\Value)->isSet(): bool(true)
V8\SetObject(V8\Value)->isWeakMap(): bool(false)
V8\SetObject(V8\Value)->isWeakSet(): bool(false)

New weak set from V8 is NOT an instance of \V8\SetObject: ok
V8\ObjectValue(V8\Value)->isObject(): bool(true)
V8\ObjectValue(V8\Value)->isMap(): bool(false)
V8\ObjectValue(V8\Value)->isSet(): bool(false)
V8\ObjectValue(V8\Value)->isWeakMap(): bool(false)
V8\ObjectValue(V8\Value)->isWeakSet(): bool(true)


Class-specific methods:
-----------------------
V8\SetObject->size(): float(0)
Can add key: ok
Can add another different key: ok
V8\SetObject->size(): float(2)
Cannot add another same key: ok
V8\SetObject->size(): float(2)

Key exists: ok
Another key exists: ok
Nonexistent key does not exists: ok

V8\SetObject->size(): float(2)
V8\SetObject::asArray() result is instance of V8\ArrayObject

SetObject Array representation has valid length: ok
SetObject Array contains key: ok
SetObject Array contains another key: ok

Delete existent key: ok
Deleted key does not exists: ok
Delete nonexistent key fails: ok
Deleted nonexistent key does not exists: ok
V8\SetObject->size(): float(1)

V8\SetObject->size(): float(3)
V8\SetObject->clear(): NULL
V8\SetObject->size(): float(0)
