--TEST--
V8\Value - test emptiness checker
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

// Tests:


$isolate = new V8\Isolate();
$context = new V8\Context($isolate);


$bad = new class extends \V8\Value {
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
V8\Exceptions\Exception: Value is empty. Forgot to call parent::__construct()?
V8\Exceptions\Exception: Value is empty. Forgot to call parent::__construct()?
