--TEST--
v8\FunctionObject::Call() - calling with bad args
--SKIPIF--
<?php if (!extension_loaded("v8")) {
    print "skip";
} ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new \PhpV8Helpers($helper);

$isolate = new \v8\Isolate();
$context = new \v8\Context($isolate);

$callback = function () {
    throw new RuntimeException('Should never be invoked');
};

$fnc = new \v8\FunctionObject($context, $callback);

try {
    $fnc->Call($context, $fnc, [1]);
} catch(Throwable $e) {
    $helper->exception_export($e);
}

try {
    $fnc->Call($context, $fnc, [new stdClass()]);
} catch(Throwable $e) {
    $helper->exception_export($e);
}

try {
    $arg = new class extends \v8\Value {
        public function __construct()
        {
            //parent::__construct($isolate); // yes, we don't invoke parent constructor
        }
    };

    $fnc->Call($context, $fnc, [$arg]);
} catch(Throwable $e) {
    $helper->exception_export($e);
}

try {
    $isolate2 = new \v8\Isolate();

    $fnc->Call($context, $fnc, [new \v8\StringValue($isolate2)]);
} catch(Throwable $e) {
    $helper->exception_export($e);
}
?>
--EXPECT--
TypeError: Argument 3 passed to v8\FunctionObject::Call() should be array of \v8\Value objects, integer given at 0 offset
TypeError: Argument 3 passed to v8\FunctionObject::Call() should be array of \v8\Value objects, instance of stdClass given at 0 offset
v8\Exceptions\GenericException: Value is empty. Forgot to call parent::__construct()?: argument 3 passed to v8\FunctionObject::Call() at 0 offset
v8\Exceptions\GenericException: Isolates mismatch: argument 3 passed to v8\FunctionObject::Call() at 0 offset
