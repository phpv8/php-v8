--TEST--
v8\FunctionObject
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

$global_template1 = new v8\ObjectTemplate($isolate1);

$context1 = new v8\Context($isolate1, $extensions1, $global_template1);


$func = new v8Tests\TrackingDtors\FunctionObject($context1, function (\v8\FunctionCallbackInfo $info) {
    echo 'Should output Hello World string', PHP_EOL;
});

$func->SetName(new \v8\StringValue($isolate1, 'custom_name'));

$helper->header('Object representation');
debug_zval_dump($func);
$helper->space();

$helper->assert('FunctionObject extends ObjectValue', $func instanceof \v8\ObjectValue);
$helper->line();


$context1->GlobalObject()->Set($context1, new \v8\StringValue($isolate1, 'print'), $func);

$source1 = 'print("Hello, world\n"); delete print; "Script done"';
$file_name1 = 'test.js';


$script1 = new v8\Script($context1, new \v8\StringValue($isolate1, $source1), new \v8\ScriptOrigin($file_name1));

debug_zval_dump($script1->Run()->ToString($context1)->Value());
$helper->line();

$helper->dump_object_methods($func, [], new ArrayMapFilter(['GetScriptOrigin' => true]));
$helper->line();

echo 'We are done for now', PHP_EOL;

?>
--EXPECTF--
Object representation:
----------------------
object(v8Tests\TrackingDtors\FunctionObject)#5 (2) refcount(2){
  ["isolate":"v8\Value":private]=>
  object(v8Tests\TrackingDtors\Isolate)#2 (1) refcount(4){
    ["snapshot":"v8\Isolate":private]=>
    NULL
  }
  ["context":"v8\ObjectValue":private]=>
  object(v8\Context)#4 (4) refcount(2){
    ["isolate":"v8\Context":private]=>
    object(v8Tests\TrackingDtors\Isolate)#2 (1) refcount(4){
      ["snapshot":"v8\Isolate":private]=>
      NULL
    }
    ["extensions":"v8\Context":private]=>
    array(0) refcount(2){
    }
    ["global_template":"v8\Context":private]=>
    object(v8\ObjectTemplate)#3 (1) refcount(2){
      ["isolate":"v8\Template":private]=>
      object(v8Tests\TrackingDtors\Isolate)#2 (1) refcount(4){
        ["snapshot":"v8\Isolate":private]=>
        NULL
      }
    }
    ["global_object":"v8\Context":private]=>
    NULL
  }
}


FunctionObject extends ObjectValue: ok

Should output Hello World string
string(11) "Script done" refcount(1)

v8Tests\TrackingDtors\FunctionObject(v8\FunctionObject)->GetScriptOrigin():
    object(v8\ScriptOrigin)#105 (6) refcount(5){
      ["resource_name":"v8\ScriptOrigin":private]=>
      string(0) "" refcount(%d)
      ["resource_line_offset":"v8\ScriptOrigin":private]=>
      int(0)
      ["resource_column_offset":"v8\ScriptOrigin":private]=>
      int(0)
      ["options":"v8\ScriptOrigin":private]=>
      object(v8\ScriptOriginOptions)#106 (3) refcount(1){
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
      string(0) "" refcount(%d)
    }

We are done for now
FunctionObject dies now!
Isolate dies now!
