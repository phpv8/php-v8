--TEST--
V8\ObjectValue - external memory
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php
/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

// Tests:

$isolate = new \V8\Isolate();
$context = new \V8\Context($isolate);
$value = new \V8\ObjectValue($context);

$helper->inline('Adjusted external memory size by default', $value->getExternalAllocatedMemory());
$helper->inline('After adjusting from zero to 1kb', $value->adjustExternalAllocatedMemory(1024));
$helper->inline('After adjusting from 1kb to 2kb', $value->adjustExternalAllocatedMemory(1024));
$helper->inline('After adjusting down from 2kb to 1kb', $value->adjustExternalAllocatedMemory(-1024));
$helper->inline('After adjusting down to more that was adjusted initially', $value->adjustExternalAllocatedMemory(-9999999999));
$helper->line();

?>
--EXPECT--
Adjusted external memory size by default: 0
After adjusting from zero to 1kb: 1024
After adjusting from 1kb to 2kb: 2048
After adjusting down from 2kb to 1kb: 1024
After adjusting down to more that was adjusted initially: 0
