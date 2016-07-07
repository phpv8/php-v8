--TEST--
v8\RegExpObject\Flags
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

// Bootstraps:
$obj = new v8\RegExpObject\Flags();

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
object(v8\RegExpObject\Flags)#1 (0) {
}


Class constants:
----------------
v8\RegExpObject\Flags::kNone = 0
v8\RegExpObject\Flags::kGlobal = 1
v8\RegExpObject\Flags::kIgnoreCase = 2
v8\RegExpObject\Flags::kMultiline = 4
v8\RegExpObject\Flags::kSticky = 8
v8\RegExpObject\Flags::kUnicode = 16
