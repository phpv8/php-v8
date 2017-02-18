--TEST--
V8\ScriptOrigin
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

$obj = new V8\ScriptOrigin('test');

$helper->header('Object representation (default)');
$helper->dump($obj);
$helper->space();

$helper->header('Test getters (default)');

$helper->method_matches_with_output($obj, 'ResourceName', 'test');
$helper->method_matches_with_output($obj, 'ResourceLineOffset', 0);
$helper->method_matches_with_output($obj, 'ResourceColumnOffset', 0);
$helper->method_matches_with_output($obj, 'ScriptID', 0);
$helper->method_matches_with_output($obj, 'SourceMapUrl', '');
$helper->method_matches_instanceof($obj, 'Options', V8\ScriptOriginOptions::class);
$helper->space();

$options = $obj->Options();

$helper->header('Test options getters (default)');
$helper->method_matches_with_output($options, 'IsSharedCrossOrigin', false);
$helper->method_matches_with_output($options, 'IsOpaque', false);
$helper->space();


$obj = new V8\ScriptOrigin('test', 1, 2, true, 3, 'map', true);
//
//$helper->header('Object representation');
//$helper->dump($obj);
//$helper->space();
//
//$helper->header('Test getters');
//
//$helper->method_matches_with_output($obj, 'ResourceName', 'test');
//$helper->method_matches_with_output($obj, 'ResourceLineOffset', 1);
//$helper->method_matches_with_output($obj, 'ResourceColumnOffset', 2);
//$helper->method_matches_with_output($obj, 'ScriptID', 3);
//$helper->method_matches_with_output($obj, 'SourceMapUrl', 'map');
//$helper->method_matches_instanceof($obj, 'Options', V8\ScriptOriginOptions::class);
//$helper->space();
//
//$options = $obj->Options();
//
//$helper->header('Test options getters');
//$helper->method_matches_with_output($options, 'IsSharedCrossOrigin', true);
//$helper->method_matches_with_output($options, 'IsOpaque', true);
//$helper->space();

?>
--EXPECT--
Object representation (default):
--------------------------------
object(V8\ScriptOrigin)#2 (6) {
  ["resource_name":"V8\ScriptOrigin":private]=>
  string(4) "test"
  ["resource_line_offset":"V8\ScriptOrigin":private]=>
  int(0)
  ["resource_column_offset":"V8\ScriptOrigin":private]=>
  int(0)
  ["options":"V8\ScriptOrigin":private]=>
  object(V8\ScriptOriginOptions)#3 (4) {
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


Test getters (default):
-----------------------
V8\ScriptOrigin::ResourceName() matches expected 'test'
V8\ScriptOrigin::ResourceLineOffset() matches expected 0
V8\ScriptOrigin::ResourceColumnOffset() matches expected 0
V8\ScriptOrigin::ScriptID() matches expected 0
V8\ScriptOrigin::SourceMapUrl() matches expected ''
V8\ScriptOrigin::Options() result is instance of V8\ScriptOriginOptions


Test options getters (default):
-------------------------------
V8\ScriptOriginOptions::IsSharedCrossOrigin() matches expected false
V8\ScriptOriginOptions::IsOpaque() matches expected false
