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

$helper->method_matches_with_output($obj, 'resourceName', 'test');
$helper->method_matches_with_output($obj, 'resourceLineOffset', 0);
$helper->method_matches_with_output($obj, 'resourceColumnOffset', 0);
$helper->method_matches_with_output($obj, 'scriptID', 0);
$helper->method_matches_with_output($obj, 'sourceMapUrl', '');
$helper->method_matches_instanceof($obj, 'options', V8\ScriptOriginOptions::class);
$helper->space();

$options = $obj->options();

$helper->header('Test options getters (default)');
$helper->method_matches_with_output($options, 'isSharedCrossOrigin', false);
$helper->method_matches_with_output($options, 'isOpaque', false);
$helper->space();


$obj = new V8\ScriptOrigin('test', 1, 2, true, 3, 'map', true, true, true);

$helper->header('Object representation');
$helper->dump($obj);
$helper->space();

$helper->header('Test getters');

$helper->method_matches_with_output($obj, 'resourceName', 'test');
$helper->method_matches_with_output($obj, 'resourceLineOffset', 1);
$helper->method_matches_with_output($obj, 'resourceColumnOffset', 2);
$helper->method_matches_with_output($obj, 'scriptID', 3);
$helper->method_matches_with_output($obj, 'sourceMapUrl', 'map');
$helper->method_matches_instanceof($obj, 'options', V8\ScriptOriginOptions::class);
$helper->space();

$options = $obj->options();

$helper->header('Test options getters');
$helper->method_matches_with_output($options, 'isSharedCrossOrigin', true);
$helper->method_matches_with_output($options, 'isOpaque', true);
$helper->method_matches_with_output($options, 'isWasm', true);
$helper->method_matches_with_output($options, 'isModule', true);
$helper->space();

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
V8\ScriptOrigin::resourceName() matches expected 'test'
V8\ScriptOrigin::resourceLineOffset() matches expected 0
V8\ScriptOrigin::resourceColumnOffset() matches expected 0
V8\ScriptOrigin::scriptID() matches expected 0
V8\ScriptOrigin::sourceMapUrl() matches expected ''
V8\ScriptOrigin::options() result is instance of V8\ScriptOriginOptions


Test options getters (default):
-------------------------------
V8\ScriptOriginOptions::isSharedCrossOrigin() matches expected false
V8\ScriptOriginOptions::isOpaque() matches expected false


Object representation:
----------------------
object(V8\ScriptOrigin)#4 (6) {
  ["resource_name":"V8\ScriptOrigin":private]=>
  string(4) "test"
  ["resource_line_offset":"V8\ScriptOrigin":private]=>
  int(1)
  ["resource_column_offset":"V8\ScriptOrigin":private]=>
  int(2)
  ["options":"V8\ScriptOrigin":private]=>
  object(V8\ScriptOriginOptions)#5 (4) {
    ["is_shared_cross_origin":"V8\ScriptOriginOptions":private]=>
    bool(true)
    ["is_opaque":"V8\ScriptOriginOptions":private]=>
    bool(true)
    ["is_wasm":"V8\ScriptOriginOptions":private]=>
    bool(true)
    ["is_module":"V8\ScriptOriginOptions":private]=>
    bool(true)
  }
  ["script_id":"V8\ScriptOrigin":private]=>
  int(3)
  ["source_map_url":"V8\ScriptOrigin":private]=>
  string(3) "map"
}


Test getters:
-------------
V8\ScriptOrigin::resourceName() matches expected 'test'
V8\ScriptOrigin::resourceLineOffset() matches expected 1
V8\ScriptOrigin::resourceColumnOffset() matches expected 2
V8\ScriptOrigin::scriptID() matches expected 3
V8\ScriptOrigin::sourceMapUrl() matches expected 'map'
V8\ScriptOrigin::options() result is instance of V8\ScriptOriginOptions


Test options getters:
---------------------
V8\ScriptOriginOptions::isSharedCrossOrigin() matches expected true
V8\ScriptOriginOptions::isOpaque() matches expected true
V8\ScriptOriginOptions::isWasm() matches expected true
V8\ScriptOriginOptions::isModule() matches expected true
