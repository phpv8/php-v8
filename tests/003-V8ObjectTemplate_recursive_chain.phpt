--TEST--
V8\ObjectTemplate - recursive 2
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

use V8\Exceptions\Exception;

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

// Tests:

$isolate = new \V8\Isolate();

$template1 = new \V8\ObjectTemplate($isolate);
$template2 = new \V8\ObjectTemplate($isolate);
$template3 = new \V8\ObjectTemplate($isolate);

$template1->set(new \V8\StringValue($isolate, 'that2'), $template2);
$template2->set(new \V8\StringValue($isolate, 'that3'), $template3);

try {
    $template3->set(new \V8\StringValue($isolate, 'that1'), $template2);
} catch (Exception $e) {
    $helper->exception_export($e);
}


$context = new \V8\Context($isolate);
$context->globalObject()->set($context, new \V8\StringValue($isolate, 'test'), $template1->newInstance($context));

?>
--EXPECT--
V8\Exceptions\Exception: Can't set: recursion detected
