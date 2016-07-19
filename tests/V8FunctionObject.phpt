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

require '.tracking_dtors.php';

$isolate1 = new v8Tests\TrackingDtors\Isolate();
$extensions1 = [];

$global_template1 = new V8\ObjectTemplate($isolate1);

$context1 = new V8\Context($isolate1, $extensions1, $global_template1);


$func = new v8Tests\TrackingDtors\FunctionObject($context1, function (\V8\FunctionCallbackInfo $info) {
    echo 'Should output Hello World string', PHP_EOL;
});

$func->SetName(new \V8\StringValue($isolate1, 'custom_name'));

$helper->header('Object representation');
$helper->dump($func);
$helper->space();

$helper->assert('FunctionObject extends ObjectValue', $func instanceof \V8\ObjectValue);
$helper->line();


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
object(v8Tests\TrackingDtors\FunctionObject)#5 (2) {
  ["isolate":"V8\Value":private]=>
  object(v8Tests\TrackingDtors\Isolate)#2 (5) {
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
  object(V8\Context)#4 (4) {
    ["isolate":"V8\Context":private]=>
    object(v8Tests\TrackingDtors\Isolate)#2 (5) {
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
    object(V8\ObjectTemplate)#3 (1) {
      ["isolate":"V8\Template":private]=>
      object(v8Tests\TrackingDtors\Isolate)#2 (5) {
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

Should output Hello World string
string(11) "Script done"

v8Tests\TrackingDtors\FunctionObject(V8\FunctionObject)->GetScriptOrigin():
    object(V8\ScriptOrigin)#105 (6) {
      ["resource_name":"V8\ScriptOrigin":private]=>
      string(0) ""
      ["resource_line_offset":"V8\ScriptOrigin":private]=>
      int(0)
      ["resource_column_offset":"V8\ScriptOrigin":private]=>
      int(0)
      ["options":"V8\ScriptOrigin":private]=>
      object(V8\ScriptOriginOptions)#106 (3) {
        ["is_embedder_debug_script":"V8\ScriptOriginOptions":private]=>
        bool(false)
        ["is_shared_cross_origin":"V8\ScriptOriginOptions":private]=>
        bool(false)
        ["is_opaque":"V8\ScriptOriginOptions":private]=>
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
