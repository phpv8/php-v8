--TEST--
V8\ArrayObject
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
$v8_helper->injectConsoleLog($context);

$value = new V8\ArrayObject($context);

$helper->header('Object representation');
$helper->dump($value);
$helper->space();

$helper->assert('ArrayObject extends ObjectValue', $value instanceof \V8\ObjectValue);
$helper->assert('ArrayObject is instanceof Array', $value->instanceOf($context, $context->globalObject()->get($context, new \V8\StringValue($isolate, 'Array'))));
$helper->line();

$helper->header('Accessors');
$helper->method_matches($value, 'getIsolate', $isolate);
$helper->method_matches($value, 'getContext', $context);
$helper->space();

$v8_helper->run_checks($value, 'Checkers');


$helper->header('Converters');
$helper->dump_object_methods($value, ['@@default' => [$context]], new RegexpFilter('/^to/'));
$helper->space();


$value->set($context, new \V8\Uint32Value($isolate, 0), new \V8\StringValue($isolate, 'first'));
$value->set($context, new \V8\Uint32Value($isolate, 1), new \V8\StringValue($isolate, 'second'));
$value->set($context, new \V8\Uint32Value($isolate, 2), new \V8\StringValue($isolate, 'third'));

$value->set($context, new \V8\StringValue($isolate, 'test'), new \V8\StringValue($isolate, 'property'));

$context->globalObject()->set($context, new \V8\StringValue($isolate, 'arr'), $value);

$source    = '
console.log("typeof arr: ", typeof arr);
console.log("arr: ", arr);
console.log("arr.length: ", arr.length);
console.log("arr[0]: ", arr[0]);
console.log("arr.test: ", arr.test);
console.log("arr.slice(1): ", arr.slice(1));
';

$v8_helper->CompileRun($context, $source);

?>
--EXPECT--
Object representation:
----------------------
object(V8\ArrayObject)#6 (2) {
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


ArrayObject extends ObjectValue: ok
ArrayObject is instanceof Array: ok

Accessors:
----------
V8\ArrayObject::getIsolate() matches expected value
V8\ArrayObject::getContext() matches expected value


Checkers:
---------
V8\ArrayObject(V8\Value)->typeOf(): V8\StringValue->value(): string(6) "object"

V8\ArrayObject(V8\ObjectValue)->isCallable(): bool(false)
V8\ArrayObject(V8\ObjectValue)->isConstructor(): bool(false)
V8\ArrayObject(V8\Value)->isUndefined(): bool(false)
V8\ArrayObject(V8\Value)->isNull(): bool(false)
V8\ArrayObject(V8\Value)->isNullOrUndefined(): bool(false)
V8\ArrayObject(V8\Value)->isTrue(): bool(false)
V8\ArrayObject(V8\Value)->isFalse(): bool(false)
V8\ArrayObject(V8\Value)->isName(): bool(false)
V8\ArrayObject(V8\Value)->isString(): bool(false)
V8\ArrayObject(V8\Value)->isSymbol(): bool(false)
V8\ArrayObject(V8\Value)->isFunction(): bool(false)
V8\ArrayObject(V8\Value)->isArray(): bool(true)
V8\ArrayObject(V8\Value)->isObject(): bool(true)
V8\ArrayObject(V8\Value)->isBoolean(): bool(false)
V8\ArrayObject(V8\Value)->isNumber(): bool(false)
V8\ArrayObject(V8\Value)->isInt32(): bool(false)
V8\ArrayObject(V8\Value)->isUint32(): bool(false)
V8\ArrayObject(V8\Value)->isDate(): bool(false)
V8\ArrayObject(V8\Value)->isArgumentsObject(): bool(false)
V8\ArrayObject(V8\Value)->isBooleanObject(): bool(false)
V8\ArrayObject(V8\Value)->isNumberObject(): bool(false)
V8\ArrayObject(V8\Value)->isStringObject(): bool(false)
V8\ArrayObject(V8\Value)->isSymbolObject(): bool(false)
V8\ArrayObject(V8\Value)->isNativeError(): bool(false)
V8\ArrayObject(V8\Value)->isRegExp(): bool(false)
V8\ArrayObject(V8\Value)->isAsyncFunction(): bool(false)
V8\ArrayObject(V8\Value)->isGeneratorFunction(): bool(false)
V8\ArrayObject(V8\Value)->isGeneratorObject(): bool(false)
V8\ArrayObject(V8\Value)->isPromise(): bool(false)
V8\ArrayObject(V8\Value)->isMap(): bool(false)
V8\ArrayObject(V8\Value)->isSet(): bool(false)
V8\ArrayObject(V8\Value)->isMapIterator(): bool(false)
V8\ArrayObject(V8\Value)->isSetIterator(): bool(false)
V8\ArrayObject(V8\Value)->isWeakMap(): bool(false)
V8\ArrayObject(V8\Value)->isWeakSet(): bool(false)
V8\ArrayObject(V8\Value)->isArrayBuffer(): bool(false)
V8\ArrayObject(V8\Value)->isArrayBufferView(): bool(false)
V8\ArrayObject(V8\Value)->isTypedArray(): bool(false)
V8\ArrayObject(V8\Value)->isUint8Array(): bool(false)
V8\ArrayObject(V8\Value)->isUint8ClampedArray(): bool(false)
V8\ArrayObject(V8\Value)->isInt8Array(): bool(false)
V8\ArrayObject(V8\Value)->isUint16Array(): bool(false)
V8\ArrayObject(V8\Value)->isInt16Array(): bool(false)
V8\ArrayObject(V8\Value)->isUint32Array(): bool(false)
V8\ArrayObject(V8\Value)->isInt32Array(): bool(false)
V8\ArrayObject(V8\Value)->isFloat32Array(): bool(false)
V8\ArrayObject(V8\Value)->isFloat64Array(): bool(false)
V8\ArrayObject(V8\Value)->isDataView(): bool(false)
V8\ArrayObject(V8\Value)->isSharedArrayBuffer(): bool(false)
V8\ArrayObject(V8\Value)->isProxy(): bool(false)


Converters:
-----------
V8\ArrayObject(V8\Value)->toBoolean():
    object(V8\BooleanValue)#118 (1) {
      ["isolate":"V8\Value":private]=>
      object(V8\Isolate)#3 (0) {
      }
    }
V8\ArrayObject(V8\Value)->toNumber():
    object(V8\Int32Value)#118 (1) {
      ["isolate":"V8\Value":private]=>
      object(V8\Isolate)#3 (0) {
      }
    }
V8\ArrayObject(V8\Value)->toString():
    object(V8\StringValue)#118 (1) {
      ["isolate":"V8\Value":private]=>
      object(V8\Isolate)#3 (0) {
      }
    }
V8\ArrayObject(V8\Value)->toDetailString():
    object(V8\StringValue)#118 (1) {
      ["isolate":"V8\Value":private]=>
      object(V8\Isolate)#3 (0) {
      }
    }
V8\ArrayObject(V8\Value)->toObject():
    object(V8\ArrayObject)#6 (2) {
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
V8\ArrayObject(V8\Value)->toInteger():
    object(V8\Int32Value)#118 (1) {
      ["isolate":"V8\Value":private]=>
      object(V8\Isolate)#3 (0) {
      }
    }
V8\ArrayObject(V8\Value)->toUint32():
    object(V8\Int32Value)#118 (1) {
      ["isolate":"V8\Value":private]=>
      object(V8\Isolate)#3 (0) {
      }
    }
V8\ArrayObject(V8\Value)->toInt32():
    object(V8\Int32Value)#118 (1) {
      ["isolate":"V8\Value":private]=>
      object(V8\Isolate)#3 (0) {
      }
    }
V8\ArrayObject(V8\Value)->toArrayIndex(): V8\Exceptions\Exception: Failed to convert


typeof arr: object
arr: [first, second, third]
arr.length: 3
arr[0]: first
arr.test: property
arr.slice(1): [second, third]
