--TEST--
V8\ObjectTemplate::Set() - recursive self
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

use V8\Exceptions\GenericException;

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

// Tests:

$isolate = new \V8\Isolate();

$template = new \V8\ObjectTemplate($isolate);

try {
    $template->Set(new \V8\StringValue($isolate, 'self'), $template);
} catch (GenericException $e) {
    $helper->exception_export($e);
}

$context = new \V8\Context($isolate);
$context->GlobalObject()->Set($context, new \V8\StringValue($isolate, 'test'), $template->NewInstance($context));

?>
--EXPECT--
V8\Exceptions\GenericException: Can't set: recursion detected
