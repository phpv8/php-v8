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
$extensions = [];
$global_template = new V8\ObjectTemplate($isolate);

$value = new V8\StringValue($isolate, 'TEST VALUE 111');


$global_template->Set(new \V8\StringValue($isolate, 'test'), $value);


$context = new V8\Context($isolate, $extensions, $global_template);


$source    = 'var test = "passed"; 2+2*2-2/2 + test';
$file_name = 'test.js';

$script = new V8\Script($context, new \V8\StringValue($isolate, $source), new \V8\ScriptOrigin($file_name));

$helper->dump($script);

$helper->header('Accessors');
$helper->method_matches($script, 'GetContext', $context);
$helper->space();

$res = $script->Run($context);

$helper->header('Script result accessors');
$helper->method_matches($res, 'GetIsolate', $isolate);
$helper->space();

$v8_helper->run_checks($res, 'Checkers');
?>
--EXPECT--
object(V8\Script)#7 (2) {
  ["isolate":"V8\Script":private]=>
  object(V8\Isolate)#3 (5) {
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
  ["context":"V8\Script":private]=>
  object(V8\Context)#6 (4) {
    ["isolate":"V8\Context":private]=>
    object(V8\Isolate)#3 (5) {
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
    ["extensions":"V8\Context":private]=>
    array(0) {
    }
    ["global_template":"V8\Context":private]=>
    object(V8\ObjectTemplate)#4 (1) {
      ["isolate":"V8\Template":private]=>
      object(V8\Isolate)#3 (5) {
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
Accessors:
----------
V8\Script::GetContext() matches expected value


Script result accessors:
------------------------
V8\StringValue::GetIsolate() matches expected value


Checkers:
---------
V8\StringValue(V8\Value)->TypeOf(): V8\StringValue->Value(): string(6) "string"

V8\StringValue->IsOneByte(): bool(true)
V8\StringValue(V8\Value)->IsUndefined(): bool(false)
V8\StringValue(V8\Value)->IsNull(): bool(false)
V8\StringValue(V8\Value)->IsNullOrUndefined(): bool(false)
V8\StringValue(V8\Value)->IsTrue(): bool(false)
V8\StringValue(V8\Value)->IsFalse(): bool(false)
V8\StringValue(V8\Value)->IsName(): bool(true)
V8\StringValue(V8\Value)->IsString(): bool(true)
V8\StringValue(V8\Value)->IsSymbol(): bool(false)
V8\StringValue(V8\Value)->IsFunction(): bool(false)
V8\StringValue(V8\Value)->IsArray(): bool(false)
V8\StringValue(V8\Value)->IsObject(): bool(false)
V8\StringValue(V8\Value)->IsBoolean(): bool(false)
V8\StringValue(V8\Value)->IsNumber(): bool(false)
V8\StringValue(V8\Value)->IsInt32(): bool(false)
V8\StringValue(V8\Value)->IsUint32(): bool(false)
V8\StringValue(V8\Value)->IsDate(): bool(false)
V8\StringValue(V8\Value)->IsArgumentsObject(): bool(false)
V8\StringValue(V8\Value)->IsBooleanObject(): bool(false)
V8\StringValue(V8\Value)->IsNumberObject(): bool(false)
V8\StringValue(V8\Value)->IsStringObject(): bool(false)
V8\StringValue(V8\Value)->IsSymbolObject(): bool(false)
V8\StringValue(V8\Value)->IsNativeError(): bool(false)
V8\StringValue(V8\Value)->IsRegExp(): bool(false)
V8\StringValue(V8\Value)->IsAsyncFunction(): bool(false)
V8\StringValue(V8\Value)->IsGeneratorFunction(): bool(false)
V8\StringValue(V8\Value)->IsGeneratorObject(): bool(false)
V8\StringValue(V8\Value)->IsPromise(): bool(false)
V8\StringValue(V8\Value)->IsMap(): bool(false)
V8\StringValue(V8\Value)->IsSet(): bool(false)
V8\StringValue(V8\Value)->IsMapIterator(): bool(false)
V8\StringValue(V8\Value)->IsSetIterator(): bool(false)
V8\StringValue(V8\Value)->IsWeakMap(): bool(false)
V8\StringValue(V8\Value)->IsWeakSet(): bool(false)
V8\StringValue(V8\Value)->IsArrayBuffer(): bool(false)
V8\StringValue(V8\Value)->IsArrayBufferView(): bool(false)
V8\StringValue(V8\Value)->IsTypedArray(): bool(false)
V8\StringValue(V8\Value)->IsUint8Array(): bool(false)
V8\StringValue(V8\Value)->IsUint8ClampedArray(): bool(false)
V8\StringValue(V8\Value)->IsInt8Array(): bool(false)
V8\StringValue(V8\Value)->IsUint16Array(): bool(false)
V8\StringValue(V8\Value)->IsInt16Array(): bool(false)
V8\StringValue(V8\Value)->IsUint32Array(): bool(false)
V8\StringValue(V8\Value)->IsInt32Array(): bool(false)
V8\StringValue(V8\Value)->IsFloat32Array(): bool(false)
V8\StringValue(V8\Value)->IsFloat64Array(): bool(false)
V8\StringValue(V8\Value)->IsDataView(): bool(false)
V8\StringValue(V8\Value)->IsSharedArrayBuffer(): bool(false)
V8\StringValue(V8\Value)->IsProxy(): bool(false)
