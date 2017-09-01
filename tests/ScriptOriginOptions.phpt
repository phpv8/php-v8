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
$helper->dump_object_methods($obj);
$helper->space();



$obj = new V8\ScriptOriginOptions(true);

$helper->header('Object representation');
$helper->dump($obj);
$helper->space();

$helper->header('Test getters');
$helper->dump_object_methods($obj);
$helper->space();

$obj = new V8\ScriptOriginOptions(false, true);

$helper->header('Object representation');
$helper->dump($obj);
$helper->space();

$helper->header('Test getters');
$helper->dump_object_methods($obj);
$helper->space();

$obj = new V8\ScriptOriginOptions(false, false, true);

$helper->header('Object representation');
$helper->dump($obj);
$helper->space();

$helper->header('Test getters');
$helper->dump_object_methods($obj);
$helper->space();

$obj = new V8\ScriptOriginOptions(false, false, false, true);

$helper->header('Object representation');
$helper->dump($obj);
$helper->space();

$helper->header('Test getters');
$helper->dump_object_methods($obj);
$helper->space();

?>
--EXPECT--
Object representation (default):
--------------------------------
object(V8\ScriptOriginOptions)#2 (4) {
  ["is_shared_cross_origin":"V8\ScriptOriginOptions":private]=>
  bool(false)
  ["is_opaque":"V8\ScriptOriginOptions":private]=>
  bool(false)
  ["is_wasm":"V8\ScriptOriginOptions":private]=>
  bool(false)
  ["is_module":"V8\ScriptOriginOptions":private]=>
  bool(false)
}


Test getters (default):
-----------------------
V8\ScriptOriginOptions->isSharedCrossOrigin(): bool(false)
V8\ScriptOriginOptions->isOpaque(): bool(false)
V8\ScriptOriginOptions->isWasm(): bool(false)
V8\ScriptOriginOptions->isModule(): bool(false)


Object representation:
----------------------
object(V8\ScriptOriginOptions)#11 (4) {
  ["is_shared_cross_origin":"V8\ScriptOriginOptions":private]=>
  bool(true)
  ["is_opaque":"V8\ScriptOriginOptions":private]=>
  bool(false)
  ["is_wasm":"V8\ScriptOriginOptions":private]=>
  bool(false)
  ["is_module":"V8\ScriptOriginOptions":private]=>
  bool(false)
}


Test getters:
-------------
V8\ScriptOriginOptions->isSharedCrossOrigin(): bool(true)
V8\ScriptOriginOptions->isOpaque(): bool(false)
V8\ScriptOriginOptions->isWasm(): bool(false)
V8\ScriptOriginOptions->isModule(): bool(false)


Object representation:
----------------------
object(V8\ScriptOriginOptions)#4 (4) {
  ["is_shared_cross_origin":"V8\ScriptOriginOptions":private]=>
  bool(false)
  ["is_opaque":"V8\ScriptOriginOptions":private]=>
  bool(true)
  ["is_wasm":"V8\ScriptOriginOptions":private]=>
  bool(false)
  ["is_module":"V8\ScriptOriginOptions":private]=>
  bool(false)
}


Test getters:
-------------
V8\ScriptOriginOptions->isSharedCrossOrigin(): bool(false)
V8\ScriptOriginOptions->isOpaque(): bool(true)
V8\ScriptOriginOptions->isWasm(): bool(false)
V8\ScriptOriginOptions->isModule(): bool(false)


Object representation:
----------------------
object(V8\ScriptOriginOptions)#10 (4) {
  ["is_shared_cross_origin":"V8\ScriptOriginOptions":private]=>
  bool(false)
  ["is_opaque":"V8\ScriptOriginOptions":private]=>
  bool(false)
  ["is_wasm":"V8\ScriptOriginOptions":private]=>
  bool(false)
  ["is_module":"V8\ScriptOriginOptions":private]=>
  bool(false)
}


Test getters:
-------------
V8\ScriptOriginOptions->isSharedCrossOrigin(): bool(false)
V8\ScriptOriginOptions->isOpaque(): bool(false)
V8\ScriptOriginOptions->isWasm(): bool(false)
V8\ScriptOriginOptions->isModule(): bool(false)


Object representation:
----------------------
object(V8\ScriptOriginOptions)#6 (4) {
  ["is_shared_cross_origin":"V8\ScriptOriginOptions":private]=>
  bool(false)
  ["is_opaque":"V8\ScriptOriginOptions":private]=>
  bool(false)
  ["is_wasm":"V8\ScriptOriginOptions":private]=>
  bool(false)
  ["is_module":"V8\ScriptOriginOptions":private]=>
  bool(false)
}


Test getters:
-------------
V8\ScriptOriginOptions->isSharedCrossOrigin(): bool(false)
V8\ScriptOriginOptions->isOpaque(): bool(false)
V8\ScriptOriginOptions->isWasm(): bool(false)
V8\ScriptOriginOptions->isModule(): bool(false)
