--TEST--
V8\ObjectValue
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

$value = new V8\ObjectValue($context);


$helper->header('Object representation');
$helper->dump($value);
$helper->space();

$helper->assert('ObjectValue extends Value', $value instanceof \V8\Value);
$helper->assert('ObjectValue does not extend PrimitiveValue', !($value instanceof \V8\PrimitiveValue));
$helper->assert('ObjectValue implements AdjustableExternalMemoryInterface', $value instanceof \V8\AdjustableExternalMemoryInterface);
$helper->assert('ObjectValue is instanceof Object', $value->instanceOf($context, $context->globalObject()->get($context, new \V8\StringValue($isolate, 'Object'))));
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

?>
--EXPECT--
Object representation:
----------------------
object(V8\ObjectValue)#6 (2) {
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


ObjectValue extends Value: ok
ObjectValue does not extend PrimitiveValue: ok
ObjectValue implements AdjustableExternalMemoryInterface: ok
ObjectValue is instanceof Object: ok

Accessors:
----------
V8\ObjectValue::getIsolate() matches expected value
V8\ObjectValue::getContext() matches expected value


Getters:
--------
GetIdentityHash is integer: ok


Checkers:
---------
V8\ObjectValue(V8\Value)->typeOf(): V8\StringValue->value(): string(6) "object"

V8\ObjectValue->isCallable(): bool(false)
V8\ObjectValue->isConstructor(): bool(false)
V8\ObjectValue(V8\Value)->isUndefined(): bool(false)
V8\ObjectValue(V8\Value)->isNull(): bool(false)
V8\ObjectValue(V8\Value)->isNullOrUndefined(): bool(false)
V8\ObjectValue(V8\Value)->isTrue(): bool(false)
V8\ObjectValue(V8\Value)->isFalse(): bool(false)
V8\ObjectValue(V8\Value)->isName(): bool(false)
V8\ObjectValue(V8\Value)->isString(): bool(false)
V8\ObjectValue(V8\Value)->isSymbol(): bool(false)
V8\ObjectValue(V8\Value)->isFunction(): bool(false)
V8\ObjectValue(V8\Value)->isArray(): bool(false)
V8\ObjectValue(V8\Value)->isObject(): bool(true)
V8\ObjectValue(V8\Value)->isBoolean(): bool(false)
V8\ObjectValue(V8\Value)->isNumber(): bool(false)
V8\ObjectValue(V8\Value)->isInt32(): bool(false)
V8\ObjectValue(V8\Value)->isUint32(): bool(false)
V8\ObjectValue(V8\Value)->isDate(): bool(false)
V8\ObjectValue(V8\Value)->isArgumentsObject(): bool(false)
V8\ObjectValue(V8\Value)->isBooleanObject(): bool(false)
V8\ObjectValue(V8\Value)->isNumberObject(): bool(false)
V8\ObjectValue(V8\Value)->isStringObject(): bool(false)
V8\ObjectValue(V8\Value)->isSymbolObject(): bool(false)
V8\ObjectValue(V8\Value)->isNativeError(): bool(false)
V8\ObjectValue(V8\Value)->isRegExp(): bool(false)
V8\ObjectValue(V8\Value)->isAsyncFunction(): bool(false)
V8\ObjectValue(V8\Value)->isGeneratorFunction(): bool(false)
V8\ObjectValue(V8\Value)->isGeneratorObject(): bool(false)
V8\ObjectValue(V8\Value)->isPromise(): bool(false)
V8\ObjectValue(V8\Value)->isMap(): bool(false)
V8\ObjectValue(V8\Value)->isSet(): bool(false)
V8\ObjectValue(V8\Value)->isMapIterator(): bool(false)
V8\ObjectValue(V8\Value)->isSetIterator(): bool(false)
V8\ObjectValue(V8\Value)->isWeakMap(): bool(false)
V8\ObjectValue(V8\Value)->isWeakSet(): bool(false)
V8\ObjectValue(V8\Value)->isArrayBuffer(): bool(false)
V8\ObjectValue(V8\Value)->isArrayBufferView(): bool(false)
V8\ObjectValue(V8\Value)->isTypedArray(): bool(false)
V8\ObjectValue(V8\Value)->isUint8Array(): bool(false)
V8\ObjectValue(V8\Value)->isUint8ClampedArray(): bool(false)
V8\ObjectValue(V8\Value)->isInt8Array(): bool(false)
V8\ObjectValue(V8\Value)->isUint16Array(): bool(false)
V8\ObjectValue(V8\Value)->isInt16Array(): bool(false)
V8\ObjectValue(V8\Value)->isUint32Array(): bool(false)
V8\ObjectValue(V8\Value)->isInt32Array(): bool(false)
V8\ObjectValue(V8\Value)->isFloat32Array(): bool(false)
V8\ObjectValue(V8\Value)->isFloat64Array(): bool(false)
V8\ObjectValue(V8\Value)->isBigInt64Array(): bool(false)
V8\ObjectValue(V8\Value)->isBigUint64Array(): bool(false)
V8\ObjectValue(V8\Value)->isDataView(): bool(false)
V8\ObjectValue(V8\Value)->isSharedArrayBuffer(): bool(false)
V8\ObjectValue(V8\Value)->isProxy(): bool(false)


Converters:
-----------
V8\ObjectValue(V8\Value)->toBoolean():
    object(V8\BooleanValue)#119 (1) {
      ["isolate":"V8\Value":private]=>
      object(V8\Isolate)#3 (0) {
      }
    }
V8\ObjectValue(V8\Value)->toNumber():
    object(V8\NumberValue)#119 (1) {
      ["isolate":"V8\Value":private]=>
      object(V8\Isolate)#3 (0) {
      }
    }
V8\ObjectValue(V8\Value)->toString():
    object(V8\StringValue)#119 (1) {
      ["isolate":"V8\Value":private]=>
      object(V8\Isolate)#3 (0) {
      }
    }
V8\ObjectValue(V8\Value)->toDetailString():
    object(V8\StringValue)#119 (1) {
      ["isolate":"V8\Value":private]=>
      object(V8\Isolate)#3 (0) {
      }
    }
V8\ObjectValue(V8\Value)->toObject():
    object(V8\ObjectValue)#6 (2) {
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
V8\ObjectValue(V8\Value)->toInteger():
    object(V8\Int32Value)#119 (1) {
      ["isolate":"V8\Value":private]=>
      object(V8\Isolate)#3 (0) {
      }
    }
V8\ObjectValue(V8\Value)->toUint32():
    object(V8\Int32Value)#119 (1) {
      ["isolate":"V8\Value":private]=>
      object(V8\Isolate)#3 (0) {
      }
    }
V8\ObjectValue(V8\Value)->toInt32():
    object(V8\Int32Value)#119 (1) {
      ["isolate":"V8\Value":private]=>
      object(V8\Isolate)#3 (0) {
      }
    }
V8\ObjectValue(V8\Value)->toArrayIndex(): V8\Exceptions\Exception: Failed to convert
