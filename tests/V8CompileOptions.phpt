--TEST--
V8\ScriptCompiler\CompileOptions
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

// Bootstraps:
$obj = new V8\ScriptCompiler\CompileOptions();

// Tests:

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

$helper->header('Object representation');
$helper->dump($obj);
$helper->space();


$helper->header('Class constants');
$helper->dump_object_constants($obj);
$helper->space();

?>
--EXPECT--
Object representation:
----------------------
object(V8\ScriptCompiler\CompileOptions)#1 (0) {
}


Class constants:
----------------
V8\ScriptCompiler\CompileOptions::kNoCompileOptions = 0
V8\ScriptCompiler\CompileOptions::kProduceParserCache = 1
V8\ScriptCompiler\CompileOptions::kConsumeParserCache = 2
V8\ScriptCompiler\CompileOptions::kProduceCodeCache = 3
V8\ScriptCompiler\CompileOptions::kConsumeCodeCache = 4
