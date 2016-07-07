--TEST--
v8\ScriptOriginOptions
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

$obj = new v8\ScriptOriginOptions();

$helper->header('Object representation (default)');
$helper->dump($obj);
$helper->space();

$helper->header('Test getters (default)');
$helper->method_matches_with_output($obj, 'IsEmbedderDebugScript', false);
$helper->method_matches_with_output($obj, 'IsSharedCrossOrigin', false);
$helper->method_matches_with_output($obj, 'IsOpaque', false);
$helper->space();


$obj = new v8\ScriptOriginOptions(true);

$helper->header('Object representation');
$helper->dump($obj);
$helper->space();

$helper->header('Test getters');
$helper->method_matches_with_output($obj, 'IsEmbedderDebugScript', true);
$helper->method_matches_with_output($obj, 'IsSharedCrossOrigin', false);
$helper->method_matches_with_output($obj, 'IsOpaque', false);
$helper->space();

$obj = new v8\ScriptOriginOptions(false, true);

$helper->header('Object representation');
$helper->dump($obj);
$helper->space();

$helper->header('Test getters');
$helper->method_matches_with_output($obj, 'IsEmbedderDebugScript', false);
$helper->method_matches_with_output($obj, 'IsSharedCrossOrigin', true);
$helper->method_matches_with_output($obj, 'IsOpaque', false);
$helper->space();

$obj = new v8\ScriptOriginOptions(false, false, true);

$helper->header('Object representation');
$helper->dump($obj);
$helper->space();

$helper->header('Test getters');
$helper->method_matches_with_output($obj, 'IsEmbedderDebugScript', false);
$helper->method_matches_with_output($obj, 'IsSharedCrossOrigin', false);
$helper->method_matches_with_output($obj, 'IsOpaque', true);
$helper->space();

$obj = new v8\ScriptOriginOptions(true, true, true);

$helper->header('Object representation');
$helper->dump($obj);
$helper->space();

$helper->header('Test getters');
$helper->method_matches_with_output($obj, 'IsEmbedderDebugScript', true);
$helper->method_matches_with_output($obj, 'IsSharedCrossOrigin', true);
$helper->method_matches_with_output($obj, 'IsOpaque', true);
$helper->space();

?>
--EXPECT--
Object representation (default):
--------------------------------
object(v8\ScriptOriginOptions)#2 (3) {
  ["is_embedder_debug_script":"v8\ScriptOriginOptions":private]=>
  bool(false)
  ["is_shared_cross_origin":"v8\ScriptOriginOptions":private]=>
  bool(false)
  ["is_opaque":"v8\ScriptOriginOptions":private]=>
  bool(false)
}


Test getters (default):
-----------------------
v8\ScriptOriginOptions::IsEmbedderDebugScript() matches expected false
v8\ScriptOriginOptions::IsSharedCrossOrigin() matches expected false
v8\ScriptOriginOptions::IsOpaque() matches expected false


Object representation:
----------------------
object(v8\ScriptOriginOptions)#3 (3) {
  ["is_embedder_debug_script":"v8\ScriptOriginOptions":private]=>
  bool(true)
  ["is_shared_cross_origin":"v8\ScriptOriginOptions":private]=>
  bool(false)
  ["is_opaque":"v8\ScriptOriginOptions":private]=>
  bool(false)
}


Test getters:
-------------
v8\ScriptOriginOptions::IsEmbedderDebugScript() matches expected true
v8\ScriptOriginOptions::IsSharedCrossOrigin() matches expected false
v8\ScriptOriginOptions::IsOpaque() matches expected false


Object representation:
----------------------
object(v8\ScriptOriginOptions)#2 (3) {
  ["is_embedder_debug_script":"v8\ScriptOriginOptions":private]=>
  bool(false)
  ["is_shared_cross_origin":"v8\ScriptOriginOptions":private]=>
  bool(true)
  ["is_opaque":"v8\ScriptOriginOptions":private]=>
  bool(false)
}


Test getters:
-------------
v8\ScriptOriginOptions::IsEmbedderDebugScript() matches expected false
v8\ScriptOriginOptions::IsSharedCrossOrigin() matches expected true
v8\ScriptOriginOptions::IsOpaque() matches expected false


Object representation:
----------------------
object(v8\ScriptOriginOptions)#3 (3) {
  ["is_embedder_debug_script":"v8\ScriptOriginOptions":private]=>
  bool(false)
  ["is_shared_cross_origin":"v8\ScriptOriginOptions":private]=>
  bool(false)
  ["is_opaque":"v8\ScriptOriginOptions":private]=>
  bool(true)
}


Test getters:
-------------
v8\ScriptOriginOptions::IsEmbedderDebugScript() matches expected false
v8\ScriptOriginOptions::IsSharedCrossOrigin() matches expected false
v8\ScriptOriginOptions::IsOpaque() matches expected true


Object representation:
----------------------
object(v8\ScriptOriginOptions)#2 (3) {
  ["is_embedder_debug_script":"v8\ScriptOriginOptions":private]=>
  bool(true)
  ["is_shared_cross_origin":"v8\ScriptOriginOptions":private]=>
  bool(true)
  ["is_opaque":"v8\ScriptOriginOptions":private]=>
  bool(true)
}


Test getters:
-------------
v8\ScriptOriginOptions::IsEmbedderDebugScript() matches expected true
v8\ScriptOriginOptions::IsSharedCrossOrigin() matches expected true
v8\ScriptOriginOptions::IsOpaque() matches expected true
