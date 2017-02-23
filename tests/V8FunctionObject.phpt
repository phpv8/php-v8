--TEST--
V8\FunctionObject
--SKIPIF--
<?php if (!extension_loaded("v8")) {
    print "skip";
} ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

require '.tracking_dtors.php';

$isolate1 = new v8Tests\TrackingDtors\Isolate();
$global_template1 = new V8\ObjectTemplate($isolate1);
$context1 = new V8\Context($isolate1, $global_template1);


$func = new v8Tests\TrackingDtors\FunctionObject($context1, function (\V8\FunctionCallbackInfo $info) {
    echo 'Should output Hello World string', PHP_EOL;
});

$func->SetName(new \V8\StringValue($isolate1, 'custom_name'));

$helper->header('Object representation');
$helper->dump($func);
$helper->space();

$helper->assert('FunctionObject extends ObjectValue', $func instanceof \V8\ObjectValue);
$helper->assert('FunctionObject implements AdjustableExternalMemoryInterface', $func instanceof \V8\AdjustableExternalMemoryInterface);
$helper->line();

$v8_helper->run_checks($func, 'Checkers');

$context1->GlobalObject()->Set($context1, new \V8\StringValue($isolate1, 'print'), $func);

$source1 = 'print("Hello, world\n"); delete print; "Script done"';
$file_name1 = 'test.js';


$script1 = new V8\Script($context1, new \V8\StringValue($isolate1, $source1), new \V8\ScriptOrigin($file_name1));

$helper->dump($script1->Run($context1)->ToString($context1)->Value());
$helper->line();

$helper->dump_object_methods($func, [], new ArrayMapFilter(['GetScriptOrigin' => true]));
$helper->line();

echo 'We are done for now', PHP_EOL;

?>
--EXPECT--
Object representation:
----------------------
object(v8Tests\TrackingDtors\FunctionObject)#6 (2) {
  ["isolate":"V8\Value":private]=>
  object(v8Tests\TrackingDtors\Isolate)#3 (5) {
    ["snapshot":"V8\Isolate":private]=>
    NULL
    ["time_limit":"V8\Isolate":private]=>
    float(0)
    ["time_limit_hit":"V8\Isolate":private]=>
    bool(false)
    ["memory_limit":"V8\Isolate":private]=>
    int(0)
    ["memory_limit_hit":"V8\Isolate":private]=>
    bool(false)
  }
  ["context":"V8\ObjectValue":private]=>
  object(V8\Context)#5 (3) {
    ["isolate":"V8\Context":private]=>
    object(v8Tests\TrackingDtors\Isolate)#3 (5) {
      ["snapshot":"V8\Isolate":private]=>
      NULL
      ["time_limit":"V8\Isolate":private]=>
      float(0)
      ["time_limit_hit":"V8\Isolate":private]=>
      bool(false)
      ["memory_limit":"V8\Isolate":private]=>
      int(0)
      ["memory_limit_hit":"V8\Isolate":private]=>
      bool(false)
    }
    ["global_template":"V8\Context":private]=>
    object(V8\ObjectTemplate)#4 (1) {
      ["isolate":"V8\Template":private]=>
      object(v8Tests\TrackingDtors\Isolate)#3 (5) {
        ["snapshot":"V8\Isolate":private]=>
        NULL
        ["time_limit":"V8\Isolate":private]=>
        float(0)
        ["time_limit_hit":"V8\Isolate":private]=>
        bool(false)
        ["memory_limit":"V8\Isolate":private]=>
        int(0)
        ["memory_limit_hit":"V8\Isolate":private]=>
        bool(false)
      }
    }
    ["global_object":"V8\Context":private]=>
    NULL
  }
}


FunctionObject extends ObjectValue: ok
FunctionObject implements AdjustableExternalMemoryInterface: ok

Checkers:
---------
v8Tests\TrackingDtors\FunctionObject(V8\Value)->TypeOf(): V8\StringValue->Value(): string(8) "function"

v8Tests\TrackingDtors\FunctionObject(V8\ObjectValue)->IsCallable(): bool(true)
v8Tests\TrackingDtors\FunctionObject(V8\ObjectValue)->IsConstructor(): bool(true)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->IsUndefined(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->IsNull(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->IsNullOrUndefined(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->IsTrue(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->IsFalse(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->IsName(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->IsString(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->IsSymbol(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->IsFunction(): bool(true)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->IsArray(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->IsObject(): bool(true)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->IsBoolean(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->IsNumber(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->IsInt32(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->IsUint32(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->IsDate(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->IsArgumentsObject(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->IsBooleanObject(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->IsNumberObject(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->IsStringObject(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->IsSymbolObject(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->IsNativeError(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->IsRegExp(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->IsAsyncFunction(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->IsGeneratorFunction(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->IsGeneratorObject(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->IsPromise(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->IsMap(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->IsSet(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->IsMapIterator(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->IsSetIterator(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->IsWeakMap(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->IsWeakSet(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->IsArrayBuffer(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->IsArrayBufferView(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->IsTypedArray(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->IsUint8Array(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->IsUint8ClampedArray(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->IsInt8Array(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->IsUint16Array(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->IsInt16Array(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->IsUint32Array(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->IsInt32Array(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->IsFloat32Array(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->IsFloat64Array(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->IsDataView(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->IsSharedArrayBuffer(): bool(false)
v8Tests\TrackingDtors\FunctionObject(V8\Value)->IsProxy(): bool(false)


Should output Hello World string
string(11) "Script done"

v8Tests\TrackingDtors\FunctionObject(V8\FunctionObject)->GetScriptOrigin():
    object(V8\ScriptOrigin)#126 (6) {
      ["resource_name":"V8\ScriptOrigin":private]=>
      string(0) ""
      ["resource_line_offset":"V8\ScriptOrigin":private]=>
      int(0)
      ["resource_column_offset":"V8\ScriptOrigin":private]=>
      int(0)
      ["options":"V8\ScriptOrigin":private]=>
      object(V8\ScriptOriginOptions)#130 (4) {
        ["is_shared_cross_origin":"V8\ScriptOriginOptions":private]=>
        bool(false)
        ["is_opaque":"V8\ScriptOriginOptions":private]=>
        bool(false)
        ["is_wasm":"V8\ScriptOriginOptions":private]=>
        bool(false)
        ["is_module":"V8\ScriptOriginOptions":private]=>
        bool(false)
      }
      ["script_id":"V8\ScriptOrigin":private]=>
      int(0)
      ["source_map_url":"V8\ScriptOrigin":private]=>
      string(0) ""
    }

We are done for now
FunctionObject dies now!
Isolate dies now!
