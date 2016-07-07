--TEST--
v8\Script
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

// Tests:


$isolate = new v8\Isolate();
$extensions = [];
$global_template = new v8\ObjectTemplate($isolate);

$value = new v8\StringValue($isolate, 'TEST VALUE 111');


$global_template->Set(new \v8\StringValue($isolate, 'test'), $value);


$context = new v8\Context($isolate, $extensions, $global_template);


$source    = 'var test = "passed"; 2+2*2-2/2 + test';
$file_name = 'test.js';

$script = new v8\Script($context, new \v8\StringValue($isolate, $source), new \v8\ScriptOrigin($file_name));

$helper->dump($script);

$helper->header('Accessors');
$helper->method_matches($script, 'GetContext', $context);
$helper->space();

$res = $script->Run();

$helper->header('Script result accessors');
$helper->method_matches($res, 'GetIsolate', $isolate);
$helper->space();

$v8_helper->run_checks($res, 'Checkers');
?>
--EXPECT--
object(v8\Script)#7 (4) {
  ["isolate":"v8\Script":private]=>
  object(v8\Isolate)#3 (5) {
    ["snapshot":"v8\Isolate":private]=>
    NULL
    ["time_limit":"v8\Isolate":private]=>
    float(0)
    ["time_limit_hit":"v8\Isolate":private]=>
    bool(false)
    ["memory_limit":"v8\Isolate":private]=>
    int(0)
    ["memory_limit_hit":"v8\Isolate":private]=>
    bool(false)
  }
  ["context":"v8\Script":private]=>
  object(v8\Context)#6 (4) {
    ["isolate":"v8\Context":private]=>
    object(v8\Isolate)#3 (5) {
      ["snapshot":"v8\Isolate":private]=>
      NULL
      ["time_limit":"v8\Isolate":private]=>
      float(0)
      ["time_limit_hit":"v8\Isolate":private]=>
      bool(false)
      ["memory_limit":"v8\Isolate":private]=>
      int(0)
      ["memory_limit_hit":"v8\Isolate":private]=>
      bool(false)
    }
    ["extensions":"v8\Context":private]=>
    array(0) {
    }
    ["global_template":"v8\Context":private]=>
    object(v8\ObjectTemplate)#4 (1) {
      ["isolate":"v8\Template":private]=>
      object(v8\Isolate)#3 (5) {
        ["snapshot":"v8\Isolate":private]=>
        NULL
        ["time_limit":"v8\Isolate":private]=>
        float(0)
        ["time_limit_hit":"v8\Isolate":private]=>
        bool(false)
        ["memory_limit":"v8\Isolate":private]=>
        int(0)
        ["memory_limit_hit":"v8\Isolate":private]=>
        bool(false)
      }
    }
    ["global_object":"v8\Context":private]=>
    NULL
  }
  ["source":"v8\Script":private]=>
  object(v8\StringValue)#8 (1) {
    ["isolate":"v8\Value":private]=>
    object(v8\Isolate)#3 (5) {
      ["snapshot":"v8\Isolate":private]=>
      NULL
      ["time_limit":"v8\Isolate":private]=>
      float(0)
      ["time_limit_hit":"v8\Isolate":private]=>
      bool(false)
      ["memory_limit":"v8\Isolate":private]=>
      int(0)
      ["memory_limit_hit":"v8\Isolate":private]=>
      bool(false)
    }
  }
  ["origin":"v8\Script":private]=>
  object(v8\ScriptOrigin)#9 (6) {
    ["resource_name":"v8\ScriptOrigin":private]=>
    string(7) "test.js"
    ["resource_line_offset":"v8\ScriptOrigin":private]=>
    int(0)
    ["resource_column_offset":"v8\ScriptOrigin":private]=>
    int(0)
    ["options":"v8\ScriptOrigin":private]=>
    object(v8\ScriptOriginOptions)#10 (3) {
      ["is_embedder_debug_script":"v8\ScriptOriginOptions":private]=>
      bool(false)
      ["is_shared_cross_origin":"v8\ScriptOriginOptions":private]=>
      bool(false)
      ["is_opaque":"v8\ScriptOriginOptions":private]=>
      bool(false)
    }
    ["script_id":"v8\ScriptOrigin":private]=>
    int(0)
    ["source_map_url":"v8\ScriptOrigin":private]=>
    string(0) ""
  }
}
Accessors:
----------
v8\Script::GetContext() matches expected value


Script result accessors:
------------------------
v8\StringValue::GetIsolate() matches expected value


Checkers:
---------
v8\StringValue->IsOneByte(): bool(true)
v8\StringValue(v8\Value)->IsUndefined(): bool(false)
v8\StringValue(v8\Value)->IsNull(): bool(false)
v8\StringValue(v8\Value)->IsTrue(): bool(false)
v8\StringValue(v8\Value)->IsFalse(): bool(false)
v8\StringValue(v8\Value)->IsName(): bool(true)
v8\StringValue(v8\Value)->IsString(): bool(true)
v8\StringValue(v8\Value)->IsSymbol(): bool(false)
v8\StringValue(v8\Value)->IsFunction(): bool(false)
v8\StringValue(v8\Value)->IsArray(): bool(false)
v8\StringValue(v8\Value)->IsObject(): bool(false)
v8\StringValue(v8\Value)->IsBoolean(): bool(false)
v8\StringValue(v8\Value)->IsNumber(): bool(false)
v8\StringValue(v8\Value)->IsInt32(): bool(false)
v8\StringValue(v8\Value)->IsUint32(): bool(false)
v8\StringValue(v8\Value)->IsDate(): bool(false)
v8\StringValue(v8\Value)->IsArgumentsObject(): bool(false)
v8\StringValue(v8\Value)->IsBooleanObject(): bool(false)
v8\StringValue(v8\Value)->IsNumberObject(): bool(false)
v8\StringValue(v8\Value)->IsStringObject(): bool(false)
v8\StringValue(v8\Value)->IsSymbolObject(): bool(false)
v8\StringValue(v8\Value)->IsNativeError(): bool(false)
v8\StringValue(v8\Value)->IsRegExp(): bool(false)
