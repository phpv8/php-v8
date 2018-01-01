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



$obj = new V8\ScriptOriginOptions(\V8\ScriptOriginOptions::IS_SHARED_CROSS_ORIGIN);

$helper->header('Object representation');
$helper->dump($obj);
$helper->space();

$helper->header('Test getters');
$helper->dump_object_methods($obj);
$helper->space();

$obj = new V8\ScriptOriginOptions(\V8\ScriptOriginOptions::IS_OPAQUE);

$helper->header('Object representation');
$helper->dump($obj);
$helper->space();

$helper->header('Test getters');
$helper->dump_object_methods($obj);
$helper->space();

$obj = new V8\ScriptOriginOptions(\V8\ScriptOriginOptions::IS_WASM);

$helper->header('Object representation');
$helper->dump($obj);
$helper->space();

$helper->header('Test getters');
$helper->dump_object_methods($obj);
$helper->space();

$obj = new V8\ScriptOriginOptions(\V8\ScriptOriginOptions::IS_MODULE);

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
object(V8\ScriptOriginOptions)#2 (1) {
  ["flags":"V8\ScriptOriginOptions":private]=>
  int(0)
}


Test getters (default):
-----------------------
V8\ScriptOriginOptions->getFlags(): int(0)
V8\ScriptOriginOptions->isSharedCrossOrigin(): bool(false)
V8\ScriptOriginOptions->isOpaque(): bool(false)
V8\ScriptOriginOptions->isWasm(): bool(false)
V8\ScriptOriginOptions->isModule(): bool(false)


Object representation:
----------------------
object(V8\ScriptOriginOptions)#12 (1) {
  ["flags":"V8\ScriptOriginOptions":private]=>
  int(1)
}


Test getters:
-------------
V8\ScriptOriginOptions->getFlags(): int(1)
V8\ScriptOriginOptions->isSharedCrossOrigin(): bool(true)
V8\ScriptOriginOptions->isOpaque(): bool(false)
V8\ScriptOriginOptions->isWasm(): bool(false)
V8\ScriptOriginOptions->isModule(): bool(false)


Object representation:
----------------------
object(V8\ScriptOriginOptions)#4 (1) {
  ["flags":"V8\ScriptOriginOptions":private]=>
  int(2)
}


Test getters:
-------------
V8\ScriptOriginOptions->getFlags(): int(2)
V8\ScriptOriginOptions->isSharedCrossOrigin(): bool(false)
V8\ScriptOriginOptions->isOpaque(): bool(true)
V8\ScriptOriginOptions->isWasm(): bool(false)
V8\ScriptOriginOptions->isModule(): bool(false)


Object representation:
----------------------
object(V8\ScriptOriginOptions)#11 (1) {
  ["flags":"V8\ScriptOriginOptions":private]=>
  int(4)
}


Test getters:
-------------
V8\ScriptOriginOptions->getFlags(): int(4)
V8\ScriptOriginOptions->isSharedCrossOrigin(): bool(false)
V8\ScriptOriginOptions->isOpaque(): bool(false)
V8\ScriptOriginOptions->isWasm(): bool(true)
V8\ScriptOriginOptions->isModule(): bool(false)


Object representation:
----------------------
object(V8\ScriptOriginOptions)#6 (1) {
  ["flags":"V8\ScriptOriginOptions":private]=>
  int(8)
}


Test getters:
-------------
V8\ScriptOriginOptions->getFlags(): int(8)
V8\ScriptOriginOptions->isSharedCrossOrigin(): bool(false)
V8\ScriptOriginOptions->isOpaque(): bool(false)
V8\ScriptOriginOptions->isWasm(): bool(false)
V8\ScriptOriginOptions->isModule(): bool(true)
