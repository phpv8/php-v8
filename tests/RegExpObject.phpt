--TEST--
V8\RegExpObject
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
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

$value = new V8\RegExpObject($context, new \V8\StringValue($isolate, '([a-z]{1,4})-([0-9]+)'), \V8\RegExpObject::FLAG_IGNORE_CASE);

$helper->header('Object representation');
$helper->dump($value);
$helper->space();

$helper->assert('RegExpObject extends ObjectValue', $value instanceof \V8\ObjectValue);
$helper->line();

$helper->header('Getters');
$helper->dump_object_constants($value);
$helper->space();

$helper->header('Getters');
$helper->pretty_dump(get_class($value) . '->getSource()->value()', $value->getSource()->value());
$helper->method_export($value, 'getFlags');
$helper->space();

$v8_helper->run_checks($value, 'Checkers');

$context->globalObject()->set($context, new \V8\StringValue($isolate, 'val'), $value);

$source    = '
console.log("val: ", val);
console.log("typeof val: ", typeof val);
console.log("\"test-1\".replace(val, \"$2-$1\"): ", "test-1".replace(val, "$2-$1"));

val
';
$file_name = 'test.js';

$script = new V8\Script($context, new \V8\StringValue($isolate, $source), new \V8\ScriptOrigin($file_name));
$res = $script->run($context);
$helper->space();


$helper->header('Returned value should be the same');
$helper->value_matches_with_no_output($res, $value);
$helper->space();


?>
--EXPECT--
Object representation:
----------------------
object(V8\RegExpObject)#6 (2) {
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


RegExpObject extends ObjectValue: ok

Getters:
--------
V8\RegExpObject::FLAG_NONE = 0
V8\RegExpObject::FLAG_GLOBAL = 1
V8\RegExpObject::FLAG_IGNORE_CASE = 2
V8\RegExpObject::FLAG_MULTILINE = 4
V8\RegExpObject::FLAG_STICKY = 8
V8\RegExpObject::FLAG_UNICODE = 16
V8\RegExpObject::FLAG_DOTALL = 32


Getters:
--------
V8\RegExpObject->getSource()->value(): string(21) "([a-z]{1,4})-([0-9]+)"
V8\RegExpObject->getFlags(): int(2)


Checkers:
---------
V8\RegExpObject(V8\Value)->typeOf(): V8\StringValue->value(): string(6) "object"

V8\RegExpObject(V8\ObjectValue)->isCallable(): bool(false)
V8\RegExpObject(V8\ObjectValue)->isConstructor(): bool(false)
V8\RegExpObject(V8\Value)->isUndefined(): bool(false)
V8\RegExpObject(V8\Value)->isNull(): bool(false)
V8\RegExpObject(V8\Value)->isNullOrUndefined(): bool(false)
V8\RegExpObject(V8\Value)->isTrue(): bool(false)
V8\RegExpObject(V8\Value)->isFalse(): bool(false)
V8\RegExpObject(V8\Value)->isName(): bool(false)
V8\RegExpObject(V8\Value)->isString(): bool(false)
V8\RegExpObject(V8\Value)->isSymbol(): bool(false)
V8\RegExpObject(V8\Value)->isFunction(): bool(false)
V8\RegExpObject(V8\Value)->isArray(): bool(false)
V8\RegExpObject(V8\Value)->isObject(): bool(true)
V8\RegExpObject(V8\Value)->isBoolean(): bool(false)
V8\RegExpObject(V8\Value)->isNumber(): bool(false)
V8\RegExpObject(V8\Value)->isInt32(): bool(false)
V8\RegExpObject(V8\Value)->isUint32(): bool(false)
V8\RegExpObject(V8\Value)->isDate(): bool(false)
V8\RegExpObject(V8\Value)->isArgumentsObject(): bool(false)
V8\RegExpObject(V8\Value)->isBooleanObject(): bool(false)
V8\RegExpObject(V8\Value)->isNumberObject(): bool(false)
V8\RegExpObject(V8\Value)->isStringObject(): bool(false)
V8\RegExpObject(V8\Value)->isSymbolObject(): bool(false)
V8\RegExpObject(V8\Value)->isNativeError(): bool(false)
V8\RegExpObject(V8\Value)->isRegExp(): bool(true)
V8\RegExpObject(V8\Value)->isAsyncFunction(): bool(false)
V8\RegExpObject(V8\Value)->isGeneratorFunction(): bool(false)
V8\RegExpObject(V8\Value)->isGeneratorObject(): bool(false)
V8\RegExpObject(V8\Value)->isPromise(): bool(false)
V8\RegExpObject(V8\Value)->isMap(): bool(false)
V8\RegExpObject(V8\Value)->isSet(): bool(false)
V8\RegExpObject(V8\Value)->isMapIterator(): bool(false)
V8\RegExpObject(V8\Value)->isSetIterator(): bool(false)
V8\RegExpObject(V8\Value)->isWeakMap(): bool(false)
V8\RegExpObject(V8\Value)->isWeakSet(): bool(false)
V8\RegExpObject(V8\Value)->isArrayBuffer(): bool(false)
V8\RegExpObject(V8\Value)->isArrayBufferView(): bool(false)
V8\RegExpObject(V8\Value)->isTypedArray(): bool(false)
V8\RegExpObject(V8\Value)->isUint8Array(): bool(false)
V8\RegExpObject(V8\Value)->isUint8ClampedArray(): bool(false)
V8\RegExpObject(V8\Value)->isInt8Array(): bool(false)
V8\RegExpObject(V8\Value)->isUint16Array(): bool(false)
V8\RegExpObject(V8\Value)->isInt16Array(): bool(false)
V8\RegExpObject(V8\Value)->isUint32Array(): bool(false)
V8\RegExpObject(V8\Value)->isInt32Array(): bool(false)
V8\RegExpObject(V8\Value)->isFloat32Array(): bool(false)
V8\RegExpObject(V8\Value)->isFloat64Array(): bool(false)
V8\RegExpObject(V8\Value)->isBigInt64Array(): bool(false)
V8\RegExpObject(V8\Value)->isBigUint64Array(): bool(false)
V8\RegExpObject(V8\Value)->isDataView(): bool(false)
V8\RegExpObject(V8\Value)->isSharedArrayBuffer(): bool(false)
V8\RegExpObject(V8\Value)->isProxy(): bool(false)


val: /([a-z]{1,4})-([0-9]+)/i
typeof val: object
"test-1".replace(val, "$2-$1"): 1-test


Returned value should be the same:
----------------------------------
Expected value is identical to actual value
