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
$helper->assert('MapObject is instanceof Map', $value->InstanceOf($context, $context->GlobalObject()->Get($context, new \V8\StringValue($isolate, 'Map'))));
$helper->line();

$helper->header('Accessors');
$helper->method_matches($value, 'GetIsolate', $isolate);
$helper->method_matches($value, 'GetContext', $context);
$helper->space();

$helper->header('Getters');
$helper->assert('GetIdentityHash is integer', gettype($value->GetIdentityHash()), 'integer');
$helper->space();

$v8_helper->run_checks($value, 'Checkers');

$helper->header('Converters');
$helper->dump_object_methods($value, ['@@default' => [$context]], new RegexpFilter('/^To/'));
$helper->space();


$helper->header('New value creation from V8 runtime');
$filter = new ArrayListFilter(['IsObject', 'IsMap', 'IsWeakMap', 'IsSet', 'IsWeakSet'], false);
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

$helper->method_export($value, 'Size');
$helper->assert('Can set value', $value->Set($context, $key, $val), $value);
$helper->assert('Value exists', $value->Has($context, $key));
$helper->assert('Can get value', $value->Get($context, $key), $val);
$helper->assert('Nonexistent value does not exists', $value->Has($context, $nonexistent_key), false);
$helper->assert('Getting nonexistent value returns undefined', ($ret = $value->Get($context, $nonexistent_key)) instanceof \V8\Value && $ret->IsUndefined());
$helper->line();

$helper->method_export($value, 'Size');
$helper->method_matches_instanceof($value, 'AsArray', \V8\ArrayObject::class);
$helper->line();

$arr = $value->AsArray();
$helper->assert('MapObject Array representation has valid length', $arr->Length() == 2);
$helper->assert('MapObject Array contains key', $arr->Get($context, new \V8\Uint32Value($isolate, 0)), $key);
$helper->assert('MapObject Array contains value', $arr->Get($context, new \V8\Uint32Value($isolate, 1)), $val);
$helper->line();

$helper->assert('Delete existent value', $value->Delete($context, $key));
$helper->assert('Deleted value does not exists', $value->Has($context, $key), false);
$helper->assert('Delete nonexistent value fails', $value->Delete($context, $nonexistent_key), false);
$helper->assert('Deleted nonexistent value does not exists', $value->Has($context, $nonexistent_key), false);
$helper->method_export($value, 'Size');
$helper->line();

$value->Set($context, new \V8\NumberValue($isolate, 1), $val);
$value->Set($context, new \V8\NumberValue($isolate, 2), $val);
$helper->method_export($value, 'Size');
$helper->method_export($value, 'Clear');
$helper->method_export($value, 'Size');


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
V8\MapObject::GetIsolate() matches expected value
V8\MapObject::GetContext() matches expected value


Getters:
--------
GetIdentityHash is integer: ok


Checkers:
---------
V8\MapObject(V8\Value)->TypeOf(): V8\StringValue->Value(): string(6) "object"

V8\MapObject(V8\ObjectValue)->IsCallable(): bool(false)
V8\MapObject(V8\ObjectValue)->IsConstructor(): bool(false)
V8\MapObject(V8\Value)->IsUndefined(): bool(false)
V8\MapObject(V8\Value)->IsNull(): bool(false)
V8\MapObject(V8\Value)->IsNullOrUndefined(): bool(false)
V8\MapObject(V8\Value)->IsTrue(): bool(false)
V8\MapObject(V8\Value)->IsFalse(): bool(false)
V8\MapObject(V8\Value)->IsName(): bool(false)
V8\MapObject(V8\Value)->IsString(): bool(false)
V8\MapObject(V8\Value)->IsSymbol(): bool(false)
V8\MapObject(V8\Value)->IsFunction(): bool(false)
V8\MapObject(V8\Value)->IsArray(): bool(false)
V8\MapObject(V8\Value)->IsObject(): bool(true)
V8\MapObject(V8\Value)->IsBoolean(): bool(false)
V8\MapObject(V8\Value)->IsNumber(): bool(false)
V8\MapObject(V8\Value)->IsInt32(): bool(false)
V8\MapObject(V8\Value)->IsUint32(): bool(false)
V8\MapObject(V8\Value)->IsDate(): bool(false)
V8\MapObject(V8\Value)->IsArgumentsObject(): bool(false)
V8\MapObject(V8\Value)->IsBooleanObject(): bool(false)
V8\MapObject(V8\Value)->IsNumberObject(): bool(false)
V8\MapObject(V8\Value)->IsStringObject(): bool(false)
V8\MapObject(V8\Value)->IsSymbolObject(): bool(false)
V8\MapObject(V8\Value)->IsNativeError(): bool(false)
V8\MapObject(V8\Value)->IsRegExp(): bool(false)
V8\MapObject(V8\Value)->IsAsyncFunction(): bool(false)
V8\MapObject(V8\Value)->IsGeneratorFunction(): bool(false)
V8\MapObject(V8\Value)->IsGeneratorObject(): bool(false)
V8\MapObject(V8\Value)->IsPromise(): bool(false)
V8\MapObject(V8\Value)->IsMap(): bool(true)
V8\MapObject(V8\Value)->IsSet(): bool(false)
V8\MapObject(V8\Value)->IsMapIterator(): bool(false)
V8\MapObject(V8\Value)->IsSetIterator(): bool(false)
V8\MapObject(V8\Value)->IsWeakMap(): bool(false)
V8\MapObject(V8\Value)->IsWeakSet(): bool(false)
V8\MapObject(V8\Value)->IsArrayBuffer(): bool(false)
V8\MapObject(V8\Value)->IsArrayBufferView(): bool(false)
V8\MapObject(V8\Value)->IsTypedArray(): bool(false)
V8\MapObject(V8\Value)->IsUint8Array(): bool(false)
V8\MapObject(V8\Value)->IsUint8ClampedArray(): bool(false)
V8\MapObject(V8\Value)->IsInt8Array(): bool(false)
V8\MapObject(V8\Value)->IsUint16Array(): bool(false)
V8\MapObject(V8\Value)->IsInt16Array(): bool(false)
V8\MapObject(V8\Value)->IsUint32Array(): bool(false)
V8\MapObject(V8\Value)->IsInt32Array(): bool(false)
V8\MapObject(V8\Value)->IsFloat32Array(): bool(false)
V8\MapObject(V8\Value)->IsFloat64Array(): bool(false)
V8\MapObject(V8\Value)->IsDataView(): bool(false)
V8\MapObject(V8\Value)->IsSharedArrayBuffer(): bool(false)
V8\MapObject(V8\Value)->IsProxy(): bool(false)


Converters:
-----------
V8\MapObject(V8\Value)->ToBoolean():
    object(V8\BooleanValue)#120 (1) {
      ["isolate":"V8\Value":private]=>
      object(V8\Isolate)#3 (0) {
      }
    }
V8\MapObject(V8\Value)->ToNumber():
    object(V8\NumberValue)#120 (1) {
      ["isolate":"V8\Value":private]=>
      object(V8\Isolate)#3 (0) {
      }
    }
V8\MapObject(V8\Value)->ToString():
    object(V8\StringValue)#120 (1) {
      ["isolate":"V8\Value":private]=>
      object(V8\Isolate)#3 (0) {
      }
    }
V8\MapObject(V8\Value)->ToDetailString():
    object(V8\StringValue)#120 (1) {
      ["isolate":"V8\Value":private]=>
      object(V8\Isolate)#3 (0) {
      }
    }
V8\MapObject(V8\Value)->ToObject():
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
V8\MapObject(V8\Value)->ToInteger():
    object(V8\Int32Value)#120 (1) {
      ["isolate":"V8\Value":private]=>
      object(V8\Isolate)#3 (0) {
      }
    }
V8\MapObject(V8\Value)->ToUint32():
    object(V8\Int32Value)#120 (1) {
      ["isolate":"V8\Value":private]=>
      object(V8\Isolate)#3 (0) {
      }
    }
V8\MapObject(V8\Value)->ToInt32():
    object(V8\Int32Value)#120 (1) {
      ["isolate":"V8\Value":private]=>
      object(V8\Isolate)#3 (0) {
      }
    }
V8\MapObject(V8\Value)->ToArrayIndex(): V8\Exceptions\Exception: Failed to convert


New value creation from V8 runtime:
-----------------------------------
New map from V8 is instance of \V8\MapObject: ok
V8\MapObject(V8\Value)->IsObject(): bool(true)
V8\MapObject(V8\Value)->IsMap(): bool(true)
V8\MapObject(V8\Value)->IsSet(): bool(false)
V8\MapObject(V8\Value)->IsWeakMap(): bool(false)
V8\MapObject(V8\Value)->IsWeakSet(): bool(false)

New weak map from V8 is NOT an instance of \V8\MapObject: ok
V8\ObjectValue(V8\Value)->IsObject(): bool(true)
V8\ObjectValue(V8\Value)->IsMap(): bool(false)
V8\ObjectValue(V8\Value)->IsSet(): bool(false)
V8\ObjectValue(V8\Value)->IsWeakMap(): bool(true)
V8\ObjectValue(V8\Value)->IsWeakSet(): bool(false)


Class-specific methods:
-----------------------
V8\MapObject->Size(): float(0)
Can set value: ok
Value exists: ok
Can get value: ok
Nonexistent value does not exists: ok
Getting nonexistent value returns undefined: ok

V8\MapObject->Size(): float(1)
V8\MapObject::AsArray() result is instance of V8\ArrayObject

MapObject Array representation has valid length: ok
MapObject Array contains key: ok
MapObject Array contains value: ok

Delete existent value: ok
Deleted value does not exists: ok
Delete nonexistent value fails: ok
Deleted nonexistent value does not exists: ok
V8\MapObject->Size(): float(0)

V8\MapObject->Size(): float(2)
V8\MapObject->Clear(): NULL
V8\MapObject->Size(): float(0)
