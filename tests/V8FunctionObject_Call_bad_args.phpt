--TEST--
V8\FunctionObject::Call() - calling with bad args
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

$isolate = new \V8\Isolate();
$context = new \V8\Context($isolate);

$callback = function () {
    throw new RuntimeException('Should never be invoked');
};

$fnc = new \V8\FunctionObject($context, $callback);

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
    $arg = new class extends \V8\Value {
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
    $isolate2 = new \V8\Isolate();

    $fnc->Call($context, $fnc, [new \V8\StringValue($isolate2)]);
} catch(Throwable $e) {
    $helper->exception_export($e);
}
?>
--EXPECT--
TypeError: Argument 3 passed to V8\FunctionObject::Call() must be an array of \V8\Value objects, integer given at 0 offset
TypeError: Argument 3 passed to V8\FunctionObject::Call() must be an array of \V8\Value objects, instance of stdClass given at 0 offset
V8\Exceptions\Exception: Value is empty. Forgot to call parent::__construct()?: argument 3 passed to V8\FunctionObject::Call() at 0 offset
V8\Exceptions\Exception: Isolates mismatch: argument 3 passed to V8\FunctionObject::Call() at 0 offset
