--TEST--
V8\Script
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

// Tests:


$isolate = new V8\Isolate();
$global_template = new V8\ObjectTemplate($isolate);

$value = new V8\StringValue($isolate, 'TEST VALUE 111');


$global_template->set(new \V8\StringValue($isolate, 'test'), $value);


$context = new V8\Context($isolate, $global_template);


$source    = 'var test = "passed"; 2+2*2-2/2 + test';
$file_name = 'test.js';

$script = new V8\Script($context, new \V8\StringValue($isolate, $source), new \V8\ScriptOrigin($file_name));

$helper->dump($script);

$helper->header('Accessors');
$helper->method_matches($script, 'getContext', $context);
$helper->space();

$helper->header('Get unbound script');
$helper->method_matches_instanceof($script, 'getUnboundScript', \V8\UnboundScript::class);
$helper->dump($script->getUnboundScript());
$helper->space();

$res = $script->run($context);

$helper->header('Script result accessors');
$helper->method_matches($res, 'getIsolate', $isolate);
$helper->space();

$v8_helper->run_checks($res, 'Checkers');
?>
--EXPECT--
object(V8\Script)#7 (2) {
  ["isolate":"V8\Script":private]=>
  object(V8\Isolate)#3 (0) {
  }
  ["context":"V8\Script":private]=>
  object(V8\Context)#6 (1) {
    ["isolate":"V8\Context":private]=>
    object(V8\Isolate)#3 (0) {
    }
  }
}
Accessors:
----------
V8\Script::getContext() matches expected value


Get unbound script:
-------------------
V8\Script::getUnboundScript() result is instance of V8\UnboundScript
object(V8\UnboundScript)#8 (1) {
  ["isolate":"V8\UnboundScript":private]=>
  object(V8\Isolate)#3 (0) {
  }
}


Script result accessors:
------------------------
V8\StringValue::getIsolate() matches expected value


Checkers:
---------
V8\StringValue(V8\Value)->typeOf(): V8\StringValue->value(): string(6) "string"

V8\StringValue->isOneByte(): bool(true)
V8\StringValue(V8\Value)->isUndefined(): bool(false)
V8\StringValue(V8\Value)->isNull(): bool(false)
V8\StringValue(V8\Value)->isNullOrUndefined(): bool(false)
V8\StringValue(V8\Value)->isTrue(): bool(false)
V8\StringValue(V8\Value)->isFalse(): bool(false)
V8\StringValue(V8\Value)->isName(): bool(true)
V8\StringValue(V8\Value)->isString(): bool(true)
V8\StringValue(V8\Value)->isSymbol(): bool(false)
V8\StringValue(V8\Value)->isFunction(): bool(false)
V8\StringValue(V8\Value)->isArray(): bool(false)
V8\StringValue(V8\Value)->isObject(): bool(false)
V8\StringValue(V8\Value)->isBoolean(): bool(false)
V8\StringValue(V8\Value)->isNumber(): bool(false)
V8\StringValue(V8\Value)->isInt32(): bool(false)
V8\StringValue(V8\Value)->isUint32(): bool(false)
V8\StringValue(V8\Value)->isDate(): bool(false)
V8\StringValue(V8\Value)->isArgumentsObject(): bool(false)
V8\StringValue(V8\Value)->isBooleanObject(): bool(false)
V8\StringValue(V8\Value)->isNumberObject(): bool(false)
V8\StringValue(V8\Value)->isStringObject(): bool(false)
V8\StringValue(V8\Value)->isSymbolObject(): bool(false)
V8\StringValue(V8\Value)->isNativeError(): bool(false)
V8\StringValue(V8\Value)->isRegExp(): bool(false)
V8\StringValue(V8\Value)->isAsyncFunction(): bool(false)
V8\StringValue(V8\Value)->isGeneratorFunction(): bool(false)
V8\StringValue(V8\Value)->isGeneratorObject(): bool(false)
V8\StringValue(V8\Value)->isPromise(): bool(false)
V8\StringValue(V8\Value)->isMap(): bool(false)
V8\StringValue(V8\Value)->isSet(): bool(false)
V8\StringValue(V8\Value)->isMapIterator(): bool(false)
V8\StringValue(V8\Value)->isSetIterator(): bool(false)
V8\StringValue(V8\Value)->isWeakMap(): bool(false)
V8\StringValue(V8\Value)->isWeakSet(): bool(false)
V8\StringValue(V8\Value)->isArrayBuffer(): bool(false)
V8\StringValue(V8\Value)->isArrayBufferView(): bool(false)
V8\StringValue(V8\Value)->isTypedArray(): bool(false)
V8\StringValue(V8\Value)->isUint8Array(): bool(false)
V8\StringValue(V8\Value)->isUint8ClampedArray(): bool(false)
V8\StringValue(V8\Value)->isInt8Array(): bool(false)
V8\StringValue(V8\Value)->isUint16Array(): bool(false)
V8\StringValue(V8\Value)->isInt16Array(): bool(false)
V8\StringValue(V8\Value)->isUint32Array(): bool(false)
V8\StringValue(V8\Value)->isInt32Array(): bool(false)
V8\StringValue(V8\Value)->isFloat32Array(): bool(false)
V8\StringValue(V8\Value)->isFloat64Array(): bool(false)
V8\StringValue(V8\Value)->isDataView(): bool(false)
V8\StringValue(V8\Value)->isSharedArrayBuffer(): bool(false)
V8\StringValue(V8\Value)->isProxy(): bool(false)
