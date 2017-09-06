--TEST--
V8\ScriptOrigin
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--ENV--
HOME=/tmp/we-need-home-env-var-set-to-load-valgrindrc
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
$helper->method_matches_with_output($obj, 'resourceLineOffset', null);
$helper->method_matches_with_output($obj, 'resourceColumnOffset', null);
$helper->method_matches_with_output($obj, 'scriptId', null);
$helper->method_matches_with_output($obj, 'sourceMapUrl', '');
$helper->method_matches_instanceof($obj, 'options', V8\ScriptOriginOptions::class);
$helper->space();

$options = $obj->options();

$helper->header('Test options getters (default)');
$helper->method_matches_with_output($options, 'isSharedCrossOrigin', false);
$helper->method_matches_with_output($options, 'isOpaque', false);
$helper->space();



$obj = new V8\ScriptOrigin('test', 1, 2, 3,'map', new \V8\ScriptOriginOptions());

$helper->header('Object representation');
$helper->dump($obj);
$helper->space();

$helper->header('Test getters');

$helper->method_matches_with_output($obj, 'resourceName', 'test');
$helper->method_matches_with_output($obj, 'resourceLineOffset', 1);
$helper->method_matches_with_output($obj, 'resourceColumnOffset', 2);
$helper->method_matches_with_output($obj, 'scriptId', 3);
$helper->method_matches_with_output($obj, 'sourceMapUrl', 'map');
$helper->method_matches_instanceof($obj, 'options', V8\ScriptOriginOptions::class);
$helper->space();

?>
--EXPECT--
Object representation (default):
--------------------------------
object(V8\ScriptOrigin)#2 (6) {
  ["resource_name":"V8\ScriptOrigin":private]=>
  string(4) "test"
  ["resource_line_offset":"V8\ScriptOrigin":private]=>
  NULL
  ["resource_column_offset":"V8\ScriptOrigin":private]=>
  NULL
  ["script_id":"V8\ScriptOrigin":private]=>
  NULL
  ["source_map_url":"V8\ScriptOrigin":private]=>
  string(0) ""
  ["options":"V8\ScriptOrigin":private]=>
  object(V8\ScriptOriginOptions)#3 (1) {
    ["flags":"V8\ScriptOriginOptions":private]=>
    int(0)
  }
}


Test getters (default):
-----------------------
V8\ScriptOrigin::resourceName() matches expected 'test'
V8\ScriptOrigin::resourceLineOffset() matches expected NULL
V8\ScriptOrigin::resourceColumnOffset() matches expected NULL
V8\ScriptOrigin::scriptId() matches expected NULL
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
  ["script_id":"V8\ScriptOrigin":private]=>
  int(3)
  ["source_map_url":"V8\ScriptOrigin":private]=>
  string(3) "map"
  ["options":"V8\ScriptOrigin":private]=>
  object(V8\ScriptOriginOptions)#5 (1) {
    ["flags":"V8\ScriptOriginOptions":private]=>
    int(0)
  }
}


Test getters:
-------------
V8\ScriptOrigin::resourceName() matches expected 'test'
V8\ScriptOrigin::resourceLineOffset() matches expected 1
V8\ScriptOrigin::resourceColumnOffset() matches expected 2
V8\ScriptOrigin::scriptId() matches expected 3
V8\ScriptOrigin::sourceMapUrl() matches expected 'map'
V8\ScriptOrigin::options() result is instance of V8\ScriptOriginOptions
