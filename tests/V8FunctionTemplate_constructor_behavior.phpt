--TEST--
V8\FunctionTemplate - constructor behavior
--SKIPIF--
<?php if (!extension_loaded("v8")) {
    print "skip";
} ?>
--FILE--
<?php
/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';
require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

require '.tracking_dtors.php';

// Tests:

$isolate = new \V8\Isolate();
$context = new \V8\Context($isolate);
$v8_helper->injectConsoleLog($context);

$ftpl_allow = new \V8\FunctionTemplate($isolate, function () {
    echo 'Allow', PHP_EOL;
});

$ftpl_throw = new \V8\FunctionTemplate($isolate, function () {
    echo 'Throw', PHP_EOL;
}, 0, \V8\ConstructorBehavior::kThrow);

$context->GlobalObject()->Set($context, new \V8\StringValue($isolate, 'f_allow'), $ftpl_allow->GetFunction($context));
$context->GlobalObject()->Set($context, new \V8\StringValue($isolate, 'f_throw'), $ftpl_throw->GetFunction($context));


$v8_helper->CompileRun($context, 'f_allow(); new f_allow();');
try {
    $v8_helper->CompileRun($context, 'f_throw(); new f_throw();');
} catch (\V8\Exceptions\TryCatchException $e) {
    $helper->exception_export($e);
}

?>
--EXPECT--
Allow
Allow
Throw
V8\Exceptions\TryCatchException: TypeError: f_throw is not a constructor
