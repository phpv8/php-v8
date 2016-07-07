--TEST--
v8\ScriptOrigin
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

$obj = new v8\ScriptOrigin('test');

$helper->header('Object representation (default)');
$helper->dump($obj);
$helper->space();

$helper->header('Test getters (default)');

$helper->method_matches_with_output($obj, 'ResourceName', 'test');
$helper->method_matches_with_output($obj, 'ResourceLineOffset', 0);
$helper->method_matches_with_output($obj, 'ResourceColumnOffset', 0);
$helper->method_matches_with_output($obj, 'ScriptID', 0);
$helper->method_matches_with_output($obj, 'SourceMapUrl', '');
$helper->method_matches_instanceof($obj, 'Options', v8\ScriptOriginOptions::class);
$helper->space();

$options = $obj->Options();

$helper->header('Test options getters (default)');
$helper->method_matches_with_output($options, 'IsEmbedderDebugScript', false);
$helper->method_matches_with_output($options, 'IsSharedCrossOrigin', false);
$helper->method_matches_with_output($options, 'IsOpaque', false);
$helper->space();


$obj = new v8\ScriptOrigin('test', 1, 2, true, 3, true, 'map', true);

$helper->header('Object representation');
$helper->dump($obj);
$helper->space();

$helper->header('Test getters');

$helper->method_matches_with_output($obj, 'ResourceName', 'test');
$helper->method_matches_with_output($obj, 'ResourceLineOffset', 1);
$helper->method_matches_with_output($obj, 'ResourceColumnOffset', 2);
$helper->method_matches_with_output($obj, 'ScriptID', 3);
$helper->method_matches_with_output($obj, 'SourceMapUrl', 'map');
$helper->method_matches_instanceof($obj, 'Options', v8\ScriptOriginOptions::class);
$helper->space();

$options = $obj->Options();

$helper->header('Test options getters');
$helper->method_matches_with_output($options, 'IsEmbedderDebugScript', true);
$helper->method_matches_with_output($options, 'IsSharedCrossOrigin', true);
$helper->method_matches_with_output($options, 'IsOpaque', true);
$helper->space();

?>
--EXPECT--
Object representation (default):
--------------------------------
object(v8\ScriptOrigin)#2 (6) {
  ["resource_name":"v8\ScriptOrigin":private]=>
  string(4) "test"
  ["resource_line_offset":"v8\ScriptOrigin":private]=>
  int(0)
  ["resource_column_offset":"v8\ScriptOrigin":private]=>
  int(0)
  ["options":"v8\ScriptOrigin":private]=>
  object(v8\ScriptOriginOptions)#3 (3) {
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


Test getters (default):
-----------------------
v8\ScriptOrigin::ResourceName() matches expected 'test'
v8\ScriptOrigin::ResourceLineOffset() matches expected 0
v8\ScriptOrigin::ResourceColumnOffset() matches expected 0
v8\ScriptOrigin::ScriptID() matches expected 0
v8\ScriptOrigin::SourceMapUrl() matches expected ''
v8\ScriptOrigin::Options() result is instance of v8\ScriptOriginOptions


Test options getters (default):
-------------------------------
v8\ScriptOriginOptions::IsEmbedderDebugScript() matches expected false
v8\ScriptOriginOptions::IsSharedCrossOrigin() matches expected false
v8\ScriptOriginOptions::IsOpaque() matches expected false


Object representation:
----------------------
object(v8\ScriptOrigin)#4 (6) {
  ["resource_name":"v8\ScriptOrigin":private]=>
  string(4) "test"
  ["resource_line_offset":"v8\ScriptOrigin":private]=>
  int(1)
  ["resource_column_offset":"v8\ScriptOrigin":private]=>
  int(2)
  ["options":"v8\ScriptOrigin":private]=>
  object(v8\ScriptOriginOptions)#5 (3) {
    ["is_embedder_debug_script":"v8\ScriptOriginOptions":private]=>
    bool(true)
    ["is_shared_cross_origin":"v8\ScriptOriginOptions":private]=>
    bool(true)
    ["is_opaque":"v8\ScriptOriginOptions":private]=>
    bool(true)
  }
  ["script_id":"v8\ScriptOrigin":private]=>
  int(3)
  ["source_map_url":"v8\ScriptOrigin":private]=>
  string(3) "map"
}


Test getters:
-------------
v8\ScriptOrigin::ResourceName() matches expected 'test'
v8\ScriptOrigin::ResourceLineOffset() matches expected 1
v8\ScriptOrigin::ResourceColumnOffset() matches expected 2
v8\ScriptOrigin::ScriptID() matches expected 3
v8\ScriptOrigin::SourceMapUrl() matches expected 'map'
v8\ScriptOrigin::Options() result is instance of v8\ScriptOriginOptions


Test options getters:
---------------------
v8\ScriptOriginOptions::IsEmbedderDebugScript() matches expected true
v8\ScriptOriginOptions::IsSharedCrossOrigin() matches expected true
v8\ScriptOriginOptions::IsOpaque() matches expected true
