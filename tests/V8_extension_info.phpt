--TEST--
v8 extension info
--SKIPIF--
<?php if (!extension_loaded("v8")) {
    print "skip";
} ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

$ext = new ReflectionExtension('v8');
ob_start();
$ext->info();
$info = ob_get_clean();

$matches = [];
preg_match('/V8 Engine Compiled Version => (.+)/', $info, $matches);
$helper->assert('V8 Engine Compiled Version string found', !empty($matches));
$v8_version_compiled = $matches[1];

preg_match('/V8 Engine Linked Version => (.+)/', $info, $matches);
$helper->assert('V8 Engine Linked Version string found', !empty($matches));
$v8_version_linked = $matches[1];

$helper->assert('V8 Engine Compiled and Linked versions match', $v8_version_compiled === $v8_version_linked);
$helper->line();

$ext->info();

?>
--EXPECTF--
V8 Engine Compiled Version string found: ok
V8 Engine Linked Version string found: ok
V8 Engine Compiled and Linked versions match: ok


v8

V8 support => enabled
Version => %s
Revision => %s
Compiled => %s @ %s

V8 Engine Compiled Version => %s
V8 Engine Linked Version => %s
