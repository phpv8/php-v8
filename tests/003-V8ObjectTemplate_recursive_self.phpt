--TEST--
V8\ObjectTemplate::set() - recursive self
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--ENV--
HOME=/tmp/we-need-home-env-var-set-to-load-valgrindrc
--FILE--
<?php

use V8\Exceptions\Exception;

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

// Tests:

$isolate = new \V8\Isolate();

$template = new \V8\ObjectTemplate($isolate);

try {
    $template->set(new \V8\StringValue($isolate, 'self'), $template);
} catch (Exception $e) {
    $helper->exception_export($e);
}

$context = new \V8\Context($isolate);
$context->globalObject()->set($context, new \V8\StringValue($isolate, 'test'), $template->newInstance($context));

?>
--EXPECT--
V8\Exceptions\Exception: Can't set: recursion detected
