--TEST--
V8\ScriptCompiler
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--ENV--
HOME=/tmp/we-need-home-env-var-set-to-load-valgrindrc
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);


$isolate = new V8\Isolate();

$helper->header('Methods');
$helper->inline_dump('V8\ScriptCompiler::cachedDataVersionTag()', V8\ScriptCompiler::cachedDataVersionTag());
$helper->space();


?>
--EXPECTF--
Methods:
--------
V8\ScriptCompiler::cachedDataVersionTag(): int(%d)
