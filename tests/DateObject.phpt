--TEST--
V8\DateObject
--SKIPIF--
<?php if (!extension_loaded("v8")) {
    print "skip";
} ?>
--ENV--
TZ=UTC
--INI--
date.timezone = "UTC"
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

$isolate = new \V8\Isolate();
$context = new V8\Context($isolate);
$v8_helper->injectConsoleLog($context);

$test_time = 1445444940000.0;
$value = new V8\DateObject($context, $test_time);

$helper->header('Object representation');
$helper->dump($value);
$helper->space();

$helper->assert('DateObject extends ObjectValue', $value instanceof \V8\ObjectValue);
$helper->assert('ObjectValue is instanceof Date', $value->instanceOf($context, $context->globalObject()->get($context, new \V8\StringValue($isolate, 'Date'))));$helper->line();

$helper->header('Getters');
$helper->method_export($value, 'valueOf');
$helper->space();

$v8_helper->run_checks($value, 'Checkers');

$context->globalObject()->set($context, new \V8\StringValue($isolate, 'val'), $value);

$source = '
var orig = val;
console.log("val: ", val);
console.log("typeof val: ", typeof val);
orig
';
$file_name = 'test.js';

$script = new V8\Script($context, new \V8\StringValue($isolate, $source), new \V8\ScriptOrigin($file_name));
$res = $script->run($context);
$helper->space();

$helper->header('Returned value should be the same');
$helper->value_matches_with_no_output($res, $value);
$helper->space();

$helper->header('Timezone change (with notification to v8)');

// we suppose that tests run within UTC timezone, now let's change that
// ini_set('date.timezone', 'America/Los_Angeles'); // NOTE: this works only for PHP code, for v8 we should touch env TZ variable:
$old_tz = getenv('TZ');

putenv('TZ=America/Los_Angeles'); // UTC offset DST (ISO 8601)‎: ‎−07:00, UTC offset (ISO 8601)‎: ‎−08:00
\V8\DateObject::dateTimeConfigurationChangeNotification($isolate);
$value = new V8\DateObject($context, $test_time);

$context->globalObject()->set($context, new \V8\StringValue($isolate, 'val'), $value);

$source = '
console.log("val: ", val);
console.log("typeof val: ", typeof val);
val
';
$file_name = 'test.js';


$script = new V8\Script($context, new \V8\StringValue($isolate, $source), new \V8\ScriptOrigin($file_name));
$res = $script->run($context);
$helper->value_matches($test_time, $value->valueOf());
$helper->space();


$helper->header('Timezone change (without notification to v8)');

putenv('TZ=America/New_York'); // UTC offset DST (ISO 8601)‎: ‎−05:00, UTC offset (ISO 8601)‎: ‎−04:00

$value = new V8\DateObject($context, $test_time);
$context->globalObject()->set($context, new \V8\StringValue($isolate, 'val'), $value);

$source = '
console.log("val: ", val);
console.log("typeof val: ", typeof val);
val
';
$file_name = 'test.js';

// TODO: for some reason v8 still be notified about TZ changes, see https://groups.google.com/forum/?fromgroups#!topic/v8-users/f249jR67ANk
// We temporary set EDT instead of PDT which was before, this should lead to no error, but the output date value is
// undefined, it's a must to invoke \V8\DateObject::dateTimeConfigurationChangeNotification($isolate); as we did before.
// This case is verify that no segfault or exception thrown and to demonstrate that result is not what you expect to get.
$script = new V8\Script($context, new \V8\StringValue($isolate, $source), new \V8\ScriptOrigin($file_name));
$res = $script->run($context);
$helper->value_matches($test_time, $value->valueOf());
$helper->space();

putenv("TZ={$old_tz}"); // Go back


?>
--EXPECTF--
Object representation:
----------------------
object(V8\DateObject)#6 (2) {
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


DateObject extends ObjectValue: ok
ObjectValue is instanceof Date: ok

Getters:
--------
V8\DateObject->valueOf(): float(1445444940000)


Checkers:
---------
V8\DateObject(V8\Value)->typeOf(): V8\StringValue->value(): string(6) "object"

V8\DateObject(V8\ObjectValue)->isCallable(): bool(false)
V8\DateObject(V8\ObjectValue)->isConstructor(): bool(false)
V8\DateObject(V8\Value)->isUndefined(): bool(false)
V8\DateObject(V8\Value)->isNull(): bool(false)
V8\DateObject(V8\Value)->isNullOrUndefined(): bool(false)
V8\DateObject(V8\Value)->isTrue(): bool(false)
V8\DateObject(V8\Value)->isFalse(): bool(false)
V8\DateObject(V8\Value)->isName(): bool(false)
V8\DateObject(V8\Value)->isString(): bool(false)
V8\DateObject(V8\Value)->isSymbol(): bool(false)
V8\DateObject(V8\Value)->isFunction(): bool(false)
V8\DateObject(V8\Value)->isArray(): bool(false)
V8\DateObject(V8\Value)->isObject(): bool(true)
V8\DateObject(V8\Value)->isBoolean(): bool(false)
V8\DateObject(V8\Value)->isNumber(): bool(false)
V8\DateObject(V8\Value)->isInt32(): bool(false)
V8\DateObject(V8\Value)->isUint32(): bool(false)
V8\DateObject(V8\Value)->isDate(): bool(true)
V8\DateObject(V8\Value)->isArgumentsObject(): bool(false)
V8\DateObject(V8\Value)->isBooleanObject(): bool(false)
V8\DateObject(V8\Value)->isNumberObject(): bool(false)
V8\DateObject(V8\Value)->isStringObject(): bool(false)
V8\DateObject(V8\Value)->isSymbolObject(): bool(false)
V8\DateObject(V8\Value)->isNativeError(): bool(false)
V8\DateObject(V8\Value)->isRegExp(): bool(false)
V8\DateObject(V8\Value)->isAsyncFunction(): bool(false)
V8\DateObject(V8\Value)->isGeneratorFunction(): bool(false)
V8\DateObject(V8\Value)->isGeneratorObject(): bool(false)
V8\DateObject(V8\Value)->isPromise(): bool(false)
V8\DateObject(V8\Value)->isMap(): bool(false)
V8\DateObject(V8\Value)->isSet(): bool(false)
V8\DateObject(V8\Value)->isMapIterator(): bool(false)
V8\DateObject(V8\Value)->isSetIterator(): bool(false)
V8\DateObject(V8\Value)->isWeakMap(): bool(false)
V8\DateObject(V8\Value)->isWeakSet(): bool(false)
V8\DateObject(V8\Value)->isArrayBuffer(): bool(false)
V8\DateObject(V8\Value)->isArrayBufferView(): bool(false)
V8\DateObject(V8\Value)->isTypedArray(): bool(false)
V8\DateObject(V8\Value)->isUint8Array(): bool(false)
V8\DateObject(V8\Value)->isUint8ClampedArray(): bool(false)
V8\DateObject(V8\Value)->isInt8Array(): bool(false)
V8\DateObject(V8\Value)->isUint16Array(): bool(false)
V8\DateObject(V8\Value)->isInt16Array(): bool(false)
V8\DateObject(V8\Value)->isUint32Array(): bool(false)
V8\DateObject(V8\Value)->isInt32Array(): bool(false)
V8\DateObject(V8\Value)->isFloat32Array(): bool(false)
V8\DateObject(V8\Value)->isFloat64Array(): bool(false)
V8\DateObject(V8\Value)->isDataView(): bool(false)
V8\DateObject(V8\Value)->isSharedArrayBuffer(): bool(false)
V8\DateObject(V8\Value)->isProxy(): bool(false)


val: Wed Oct 21 2015 16:29:00 GMT+0000 (UTC)
typeof val: object


Returned value should be the same:
----------------------------------
Expected value is identical to actual value


Timezone change (with notification to v8):
------------------------------------------
val: Wed Oct 21 2015 09:29:00 GMT-0700 (PDT)
typeof val: object
Expected 1445444940000.0 value is identical to actual value 1445444940000.0


Timezone change (without notification to v8):
---------------------------------------------
val: Wed Oct 21 2015 09:29:00 GMT-0700 (%sDT)
typeof val: object
Expected 1445444940000.0 value is identical to actual value 1445444940000.0
