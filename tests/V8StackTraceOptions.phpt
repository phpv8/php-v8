--TEST--
v8\StackTrace\StackTraceOptions
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

// Bootstraps:
$obj = new v8\StackTrace\StackTraceOptions();

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
object(v8\StackTrace\StackTraceOptions)#1 (0) {
}


Class constants:
----------------
v8\StackTrace\StackTraceOptions::kLineNumber = 1
v8\StackTrace\StackTraceOptions::kColumnOffset = 3
v8\StackTrace\StackTraceOptions::kScriptName = 4
v8\StackTrace\StackTraceOptions::kFunctionName = 8
v8\StackTrace\StackTraceOptions::kIsEval = 16
v8\StackTrace\StackTraceOptions::kIsConstructor = 32
v8\StackTrace\StackTraceOptions::kScriptNameOrSourceURL = 64
v8\StackTrace\StackTraceOptions::kScriptId = 128
v8\StackTrace\StackTraceOptions::kExposeFramesAcrossSecurityOrigins = 256
v8\StackTrace\StackTraceOptions::kOverview = 15
v8\StackTrace\StackTraceOptions::kDetailed = 127
