--TEST--
V8\FunctionObject
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

require '.tracking_dtors.php';

$isolate         = new v8Tests\TrackingDtors\Isolate();
$global_template = new V8\ObjectTemplate($isolate);
$context         = new V8\Context($isolate, $global_template);


$func = new v8Tests\TrackingDtors\FunctionObject($context, function (\V8\FunctionCallbackInfo $info) {
    echo 'Should output Hello World string', PHP_EOL;
});

$func->setName(new \V8\StringValue($isolate, 'custom_name'));

$helper->header('Object representation');
$helper->dump($func);
$helper->space();

$helper->assert('FunctionObject extends ObjectValue', $func instanceof \V8\ObjectValue);
$helper->assert('FunctionObject implements AdjustableExternalMemoryInterface', $func instanceof \V8\AdjustableExternalMemoryInterface);
$helper->assert('FunctionObject is instanceof Function', $func->instanceOf($context, $context->globalObject()->get($context, new \V8\StringValue($isolate, 'Function'))));
$helper->assert('Function created from php holds no script id', $func->getScriptId() === null);
$helper->line();

$v8_helper->run_checks($func, 'Checkers');

$context->globalObject()->set($context, new \V8\StringValue($isolate, 'print'), $func);

$source    = 'print("Hello, world"); delete print; "Script done"';
$file_name = 'test.js';


$script = new V8\Script($context, new \V8\StringValue($isolate, $source), new \V8\ScriptOrigin($file_name));

$helper->dump($script->run($context)->toString($context)->value());

$helper->assert('Function created from php still holds no script id after been passed to script', $func->getScriptId() === null);

$helper->line();

$helper->dump_object_methods($func, [], new ArrayMapFilter(['getScriptOrigin' => true]));
$helper->line();

$helper->line();
$fnc2 = $v8_helper->CompileRun($context, 'function test() {}; test');
$helper->assert('Function from script holds script id', $fnc2->getScriptId() !== null);

echo 'We are done for now', PHP_EOL;

?>
--EXPECT--
Object representation:
----------------------
object(v8Tests\TrackingDtors\FunctionObject)#6 (2) {
  ["isolate":"V8\Value":private]=>
  object(v8Tests\TrackingDtors\Isolate)#3 (0) {
  }
  ["context":"V8\ObjectValue":private]=>
  object(V8\Context)#5 (1) {
    ["isolate":"V8\Context":private]=>
    object(v8Tests\TrackingDtors\Isolate)#3 (0) {
    }
  }
}


FunctionObject extends ObjectValue: ok
FunctionObject implements AdjustableExternalMemoryInterface: ok
FunctionObject is instanceof Function: ok
Function created from php holds no script id: ok

Checkers:
---------
v8Tests\TrackingDtors\FunctionObject(V8\Value)->typeOf(): V8\StringValue->value(): string(8) "function"

v8Tests\TrackingDtors\FunctionObject(V8\ObjectValue)->isCallable(): bool(true)
v8Tests\TrackingDtors\FunctionObject(V8\ObjectValue)->isConstructor(): bool(true)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->isUndefined(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->isNull(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->isNullOrUndefined(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->isTrue(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->isFalse(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->isName(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->isString(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->isSymbol(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->isFunction(): bool(true)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->isArray(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->isObject(): bool(true)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->isBoolean(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->isNumber(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->isInt32(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->isUint32(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->isDate(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->isArgumentsObject(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->isBooleanObject(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->isNumberObject(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->isStringObject(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->isSymbolObject(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->isNativeError(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->isRegExp(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->isAsyncFunction(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->isGeneratorFunction(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->isGeneratorObject(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->isPromise(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->isMap(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->isSet(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->isMapIterator(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->isSetIterator(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->isWeakMap(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->isWeakSet(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->isArrayBuffer(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->isArrayBufferView(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->isTypedArray(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->isUint8Array(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->isUint8ClampedArray(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->isInt8Array(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->isUint16Array(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->isInt16Array(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->isUint32Array(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->isInt32Array(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->isFloat32Array(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->isFloat64Array(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->isBigInt64Array(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->isBigUint64Array(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->isDataView(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->isSharedArrayBuffer(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->isProxy(): bool(false)


Should output Hello World string
string(11) "Script done"
Function created from php still holds no script id after been passed to script: ok

v8Tests\TrackingDtors\FunctionObject(V8\FunctionObject)->getScriptOrigin():
    object(V8\ScriptOrigin)#132 (6) {
      ["resource_name":"V8\ScriptOrigin":private]=>
      string(0) ""
      ["resource_line_offset":"V8\ScriptOrigin":private]=>
      NULL
      ["resource_column_offset":"V8\ScriptOrigin":private]=>
      NULL
      ["script_id":"V8\ScriptOrigin":private]=>
      NULL
      ["source_map_url":"V8\ScriptOrigin":private]=>
      string(0) ""
      ["options":"V8\ScriptOrigin":private]=>
      object(V8\ScriptOriginOptions)#136 (1) {
        ["flags":"V8\ScriptOriginOptions":private]=>
        int(0)
      }
    }


Function from script holds script id: ok
We are done for now
FunctionObject dies now!
Isolate dies now!
