--TEST--
V8\ScriptCompiler
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);


$isolate = new V8\Isolate();

$helper->header('Methods');
$helper->inline_dump('V8\ScriptCompiler::CachedDataVersionTag()', V8\ScriptCompiler::CachedDataVersionTag());
$helper->space();


?>
--EXPECTF--
Methods:
--------
V8\ScriptCompiler::CachedDataVersionTag(): int(%d)
