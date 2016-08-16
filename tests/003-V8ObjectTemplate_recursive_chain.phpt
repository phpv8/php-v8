--TEST--
V8\ObjectTemplate - recursive 2
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

$template1 = new \V8\ObjectTemplate($isolate);
$template2 = new \V8\ObjectTemplate($isolate);
$template3 = new \V8\ObjectTemplate($isolate);

$template1->Set(new \V8\StringValue($isolate, 'that2'), $template2);
$template2->Set(new \V8\StringValue($isolate, 'that3'), $template3);

try {
    $template3->Set(new \V8\StringValue($isolate, 'that1'), $template2);
} catch (GenericException $e) {
    $helper->exception_export($e);
}


$context = new \V8\Context($isolate);
$context->GlobalObject()->Set($context, new \V8\StringValue($isolate, 'test'), $template1->NewInstance($context));

?>
--EXPECT--
V8\Exceptions\GenericException: Can't set: recursion detected
