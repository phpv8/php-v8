--TEST--
V8\ScriptOriginOptions
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

$obj = new V8\ScriptOriginOptions();

$helper->header('Object representation (default)');
$helper->dump($obj);
$helper->space();

$helper->header('Test getters (default)');
$helper->method_matches_with_output($obj, 'IsSharedCrossOrigin', false);
$helper->method_matches_with_output($obj, 'IsOpaque', false);
$helper->space();


$obj = new V8\ScriptOriginOptions(true);

$helper->header('Object representation');
$helper->dump($obj);
$helper->space();

$helper->header('Test getters');
$helper->method_matches_with_output($obj, 'IsSharedCrossOrigin', true);
$helper->method_matches_with_output($obj, 'IsOpaque', false);
$helper->space();

$obj = new V8\ScriptOriginOptions(true, true);

$helper->header('Object representation');
$helper->dump($obj);
$helper->space();

$helper->header('Test getters');
$helper->method_matches_with_output($obj, 'IsSharedCrossOrigin', true);
$helper->method_matches_with_output($obj, 'IsOpaque', true);
$helper->space();

$obj = new V8\ScriptOriginOptions(false, true);

$helper->header('Object representation');
$helper->dump($obj);
$helper->space();

$helper->header('Test getters');
$helper->method_matches_with_output($obj, 'IsSharedCrossOrigin', false);
$helper->method_matches_with_output($obj, 'IsOpaque', true);
$helper->space();

$obj = new V8\ScriptOriginOptions(true, false);

$helper->header('Object representation');
$helper->dump($obj);
$helper->space();

$helper->header('Test getters');
$helper->method_matches_with_output($obj, 'IsSharedCrossOrigin', true);
$helper->method_matches_with_output($obj, 'IsOpaque', false);
$helper->space();

?>
--EXPECT--
Object representation (default):
--------------------------------
object(V8\ScriptOriginOptions)#2 (2) {
  ["is_shared_cross_origin":"V8\ScriptOriginOptions":private]=>
  bool(false)
  ["is_opaque":"V8\ScriptOriginOptions":private]=>
  bool(false)
}


Test getters (default):
-----------------------
V8\ScriptOriginOptions::IsSharedCrossOrigin() matches expected false
V8\ScriptOriginOptions::IsOpaque() matches expected false


Object representation:
----------------------
object(V8\ScriptOriginOptions)#3 (2) {
  ["is_shared_cross_origin":"V8\ScriptOriginOptions":private]=>
  bool(true)
  ["is_opaque":"V8\ScriptOriginOptions":private]=>
  bool(false)
}


Test getters:
-------------
V8\ScriptOriginOptions::IsSharedCrossOrigin() matches expected true
V8\ScriptOriginOptions::IsOpaque() matches expected false


Object representation:
----------------------
object(V8\ScriptOriginOptions)#2 (2) {
  ["is_shared_cross_origin":"V8\ScriptOriginOptions":private]=>
  bool(true)
  ["is_opaque":"V8\ScriptOriginOptions":private]=>
  bool(true)
}


Test getters:
-------------
V8\ScriptOriginOptions::IsSharedCrossOrigin() matches expected true
V8\ScriptOriginOptions::IsOpaque() matches expected true


Object representation:
----------------------
object(V8\ScriptOriginOptions)#3 (2) {
  ["is_shared_cross_origin":"V8\ScriptOriginOptions":private]=>
  bool(false)
  ["is_opaque":"V8\ScriptOriginOptions":private]=>
  bool(true)
}


Test getters:
-------------
V8\ScriptOriginOptions::IsSharedCrossOrigin() matches expected false
V8\ScriptOriginOptions::IsOpaque() matches expected true


Object representation:
----------------------
object(V8\ScriptOriginOptions)#2 (2) {
  ["is_shared_cross_origin":"V8\ScriptOriginOptions":private]=>
  bool(true)
  ["is_opaque":"V8\ScriptOriginOptions":private]=>
  bool(false)
}


Test getters:
-------------
V8\ScriptOriginOptions::IsSharedCrossOrigin() matches expected true
V8\ScriptOriginOptions::IsOpaque() matches expected false
