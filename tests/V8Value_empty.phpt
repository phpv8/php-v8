--TEST--
v8\Value - test emptiness checker
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

// Tests:


$isolate = new v8\Isolate();
$context = new v8\Context($isolate);


$bad = new class extends \v8\Value {
    public function __construct()
    {
    }
};

try {
  $bad->BooleanValue($context);
} catch (Throwable $e) {
  $helper->exception_export($e);
}
try {
  $bad->GetIsolate();
} catch (Throwable $e) {
  $helper->exception_export($e);
}


?>
--EXPECT--
v8\Exceptions\GenericException: Value is empty. Forgot to call parent::__construct()?
v8\Exceptions\GenericException: Value is empty. Forgot to call parent::__construct()?
