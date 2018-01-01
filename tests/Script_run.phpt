--TEST--
V8\Script::run()
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';
require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);


$isolate = new V8\Isolate();
$global_template = new V8\ObjectTemplate($isolate);

$value = new V8\StringValue($isolate, 'TEST VALUE 111');

$global_template->set(new \V8\StringValue($isolate, 'test'), $value);
$context = new V8\Context($isolate, $global_template);


$source    = 'test; test = test + ", confirmed"';
$file_name = 'test.js';

$script = new V8\Script($context, new \V8\StringValue($isolate, $source), new \V8\ScriptOrigin($file_name));
$res = $script->run($context);

$v8_helper->run_checks($res);

$helper->dump($res->value());

$helper->space();

$scalar = new V8\Int32Value($isolate, 123);
$obj    = new V8\ObjectValue($context);
$context->globalObject()->set($context, new \V8\StringValue($isolate, 'scalar'), $scalar);
$context->globalObject()->set($context, new \V8\StringValue($isolate, 'obj'), $obj);

$res = $v8_helper->CompileTryRun($context, 'scalar');

$helper->header('Scalar');
$helper->value_matches($res->value(), $scalar->value());
$helper->value_matches_with_no_output($res, $scalar);

$helper->space();


$res = $v8_helper->CompileTryRun($context, 'obj');

$helper->header('Object');
$helper->value_matches_with_no_output($res, $obj);

?>
--EXPECT--
Checks on V8\StringValue:
-------------------------
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


string(25) "TEST VALUE 111, confirmed"


Scalar:
-------
Expected 123 value is identical to actual value 123
Expected value is not identical to actual value


Object:
-------
Expected value is identical to actual value
