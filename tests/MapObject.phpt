--TEST--
V8\MapObject
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

$value = new V8\MapObject($context);


$helper->header('Object representation');
$helper->dump($value);
$helper->space();

$helper->assert('MapObject extends Value', $value instanceof \V8\Value);
$helper->assert('MapObject does not extend PrimitiveValue', !($value instanceof \V8\PrimitiveValue));
$helper->assert('MapObject implements AdjustableExternalMemoryInterface', $value instanceof \V8\AdjustableExternalMemoryInterface);
$helper->assert('MapObject is instanceof Map', $value->instanceOf($context, $context->globalObject()->get($context, new \V8\StringValue($isolate, 'Map'))));
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
$new_map = $v8_helper->CompileRun($context, "new Map()");
$helper->assert('New map from V8 is instance of \V8\MapObject', $new_map instanceof \V8\MapObject);
$helper->dump_object_methods($new_map, [], $filter);
$helper->line();

$new_map = $v8_helper->CompileRun($context, "new WeakMap()");
$helper->assert('New weak map from V8 is NOT an instance of \V8\MapObject', $new_map instanceof \V8\MapObject, false);
$helper->dump_object_methods($new_map, [], $filter);
$helper->space();


$helper->header('Class-specific methods');

$key = new \V8\ObjectValue($context);
$nonexistent_key = new \V8\ObjectValue($context);
$val = new \V8\ObjectValue($context);

$helper->method_export($value, 'size');
$helper->assert('Can set value', $value->set($context, $key, $val), $value);
$helper->assert('Value exists', $value->has($context, $key));
$helper->assert('Can get value', $value->get($context, $key), $val);
$helper->assert('Nonexistent value does not exists', $value->has($context, $nonexistent_key), false);
$helper->assert('Getting nonexistent value returns undefined', ($ret = $value->get($context, $nonexistent_key)) instanceof \V8\Value && $ret->isUndefined());
$helper->line();

$helper->method_export($value, 'size');
$helper->method_matches_instanceof($value, 'asArray', \V8\ArrayObject::class);
$helper->line();

$arr = $value->asArray();
$helper->assert('MapObject Array representation has valid length', $arr->length() == 2);
$helper->assert('MapObject Array contains key', $arr->get($context, new \V8\Uint32Value($isolate, 0)), $key);
$helper->assert('MapObject Array contains value', $arr->get($context, new \V8\Uint32Value($isolate, 1)), $val);
$helper->line();

$helper->assert('Delete existent value', $value->delete($context, $key));
$helper->assert('Deleted value does not exists', $value->has($context, $key), false);
$helper->assert('Delete nonexistent value fails', $value->delete($context, $nonexistent_key), false);
$helper->assert('Deleted nonexistent value does not exists', $value->has($context, $nonexistent_key), false);
$helper->method_export($value, 'size');
$helper->line();

$value->set($context, new \V8\NumberValue($isolate, 1), $val);
$value->set($context, new \V8\NumberValue($isolate, 2), $val);
$helper->method_export($value, 'size');
$helper->method_export($value, 'clear');
$helper->method_export($value, 'size');


?>
--EXPECT--
Object representation:
----------------------
object(V8\MapObject)#6 (2) {
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


MapObject extends Value: ok
MapObject does not extend PrimitiveValue: ok
MapObject implements AdjustableExternalMemoryInterface: ok
MapObject is instanceof Map: ok

Accessors:
----------
V8\MapObject::getIsolate() matches expected value
V8\MapObject::getContext() matches expected value


Getters:
--------
GetIdentityHash is integer: ok


Checkers:
---------
V8\MapObject(V8\Value)->typeOf(): V8\StringValue->value(): string(6) "object"

V8\MapObject(V8\ObjectValue)->isCallable(): bool(false)
V8\MapObject(V8\ObjectValue)->isConstructor(): bool(false)
V8\MapObject(V8\Value)->isUndefined(): bool(false)
V8\MapObject(V8\Value)->isNull(): bool(false)
V8\MapObject(V8\Value)->isNullOrUndefined(): bool(false)
V8\MapObject(V8\Value)->isTrue(): bool(false)
V8\MapObject(V8\Value)->isFalse(): bool(false)
V8\MapObject(V8\Value)->isName(): bool(false)
V8\MapObject(V8\Value)->isString(): bool(false)
V8\MapObject(V8\Value)->isSymbol(): bool(false)
V8\MapObject(V8\Value)->isFunction(): bool(false)
V8\MapObject(V8\Value)->isArray(): bool(false)
V8\MapObject(V8\Value)->isObject(): bool(true)
V8\MapObject(V8\Value)->isBoolean(): bool(false)
V8\MapObject(V8\Value)->isNumber(): bool(false)
V8\MapObject(V8\Value)->isInt32(): bool(false)
V8\MapObject(V8\Value)->isUint32(): bool(false)
V8\MapObject(V8\Value)->isDate(): bool(false)
V8\MapObject(V8\Value)->isArgumentsObject(): bool(false)
V8\MapObject(V8\Value)->isBooleanObject(): bool(false)
V8\MapObject(V8\Value)->isNumberObject(): bool(false)
V8\MapObject(V8\Value)->isStringObject(): bool(false)
V8\MapObject(V8\Value)->isSymbolObject(): bool(false)
V8\MapObject(V8\Value)->isNativeError(): bool(false)
V8\MapObject(V8\Value)->isRegExp(): bool(false)
V8\MapObject(V8\Value)->isAsyncFunction(): bool(false)
V8\MapObject(V8\Value)->isGeneratorFunction(): bool(false)
V8\MapObject(V8\Value)->isGeneratorObject(): bool(false)
V8\MapObject(V8\Value)->isPromise(): bool(false)
V8\MapObject(V8\Value)->isMap(): bool(true)
V8\MapObject(V8\Value)->isSet(): bool(false)
V8\MapObject(V8\Value)->isMapIterator(): bool(false)
V8\MapObject(V8\Value)->isSetIterator(): bool(false)
V8\MapObject(V8\Value)->isWeakMap(): bool(false)
V8\MapObject(V8\Value)->isWeakSet(): bool(false)
V8\MapObject(V8\Value)->isArrayBuffer(): bool(false)
V8\MapObject(V8\Value)->isArrayBufferView(): bool(false)
V8\MapObject(V8\Value)->isTypedArray(): bool(false)
V8\MapObject(V8\Value)->isUint8Array(): bool(false)
V8\MapObject(V8\Value)->isUint8ClampedArray(): bool(false)
V8\MapObject(V8\Value)->isInt8Array(): bool(false)
V8\MapObject(V8\Value)->isUint16Array(): bool(false)
V8\MapObject(V8\Value)->isInt16Array(): bool(false)
V8\MapObject(V8\Value)->isUint32Array(): bool(false)
V8\MapObject(V8\Value)->isInt32Array(): bool(false)
V8\MapObject(V8\Value)->isFloat32Array(): bool(false)
V8\MapObject(V8\Value)->isFloat64Array(): bool(false)
V8\MapObject(V8\Value)->isBigInt64Array(): bool(false)
V8\MapObject(V8\Value)->isBigUint64Array(): bool(false)
V8\MapObject(V8\Value)->isDataView(): bool(false)
V8\MapObject(V8\Value)->isSharedArrayBuffer(): bool(false)
V8\MapObject(V8\Value)->isProxy(): bool(false)


Converters:
-----------
V8\MapObject(V8\Value)->toBoolean():
    object(V8\BooleanValue)#123 (1) {
      ["isolate":"V8\Value":private]=>
      object(V8\Isolate)#3 (0) {
      }
    }
V8\MapObject(V8\Value)->toNumber():
    object(V8\NumberValue)#123 (1) {
      ["isolate":"V8\Value":private]=>
      object(V8\Isolate)#3 (0) {
      }
    }
V8\MapObject(V8\Value)->toString():
    object(V8\StringValue)#123 (1) {
      ["isolate":"V8\Value":private]=>
      object(V8\Isolate)#3 (0) {
      }
    }
V8\MapObject(V8\Value)->toDetailString():
    object(V8\StringValue)#123 (1) {
      ["isolate":"V8\Value":private]=>
      object(V8\Isolate)#3 (0) {
      }
    }
V8\MapObject(V8\Value)->toObject():
    object(V8\MapObject)#6 (2) {
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
V8\MapObject(V8\Value)->toInteger():
    object(V8\Int32Value)#123 (1) {
      ["isolate":"V8\Value":private]=>
      object(V8\Isolate)#3 (0) {
      }
    }
V8\MapObject(V8\Value)->toUint32():
    object(V8\Int32Value)#123 (1) {
      ["isolate":"V8\Value":private]=>
      object(V8\Isolate)#3 (0) {
      }
    }
V8\MapObject(V8\Value)->toInt32():
    object(V8\Int32Value)#123 (1) {
      ["isolate":"V8\Value":private]=>
      object(V8\Isolate)#3 (0) {
      }
    }
V8\MapObject(V8\Value)->toArrayIndex(): V8\Exceptions\Exception: Failed to convert


New value creation from V8 runtime:
-----------------------------------
New map from V8 is instance of \V8\MapObject: ok
V8\MapObject(V8\Value)->isObject(): bool(true)
V8\MapObject(V8\Value)->isMap(): bool(true)
V8\MapObject(V8\Value)->isSet(): bool(false)
V8\MapObject(V8\Value)->isWeakMap(): bool(false)
V8\MapObject(V8\Value)->isWeakSet(): bool(false)

New weak map from V8 is NOT an instance of \V8\MapObject: ok
V8\ObjectValue(V8\Value)->isObject(): bool(true)
V8\ObjectValue(V8\Value)->isMap(): bool(false)
V8\ObjectValue(V8\Value)->isSet(): bool(false)
V8\ObjectValue(V8\Value)->isWeakMap(): bool(true)
V8\ObjectValue(V8\Value)->isWeakSet(): bool(false)


Class-specific methods:
-----------------------
V8\MapObject->size(): float(0)
Can set value: ok
Value exists: ok
Can get value: ok
Nonexistent value does not exists: ok
Getting nonexistent value returns undefined: ok

V8\MapObject->size(): float(1)
V8\MapObject::asArray() result is instance of V8\ArrayObject

MapObject Array representation has valid length: ok
MapObject Array contains key: ok
MapObject Array contains value: ok

Delete existent value: ok
Deleted value does not exists: ok
Delete nonexistent value fails: ok
Deleted nonexistent value does not exists: ok
V8\MapObject->size(): float(0)

V8\MapObject->size(): float(2)
V8\MapObject->clear(): NULL
V8\MapObject->size(): float(0)
