--TEST--
v8\ObjectTemplate
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
<?php print "skip this test is known to fail and it hangs on travis"; ?>
--FILE--
<?php
/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

// Tests:

$isolate = new \v8\Isolate();

$template = new \v8\ObjectTemplate($isolate);
$template->Set(new \v8\StringValue($isolate, 'self'), $template);

$context = new \v8\Context($isolate, [], $template);


?>
--XFAIL--
Recursive templates are known to segfault
--EXPECT--
