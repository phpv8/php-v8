--TEST--
V8\ObjectTemplate::setAccessor() - with receiver
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

$isolate = new \V8\Isolate();
$context = new \V8\Context($isolate);
$v8_helper->injectConsoleLog($context);

$getter_checks = 0;

$getter = function (\V8\NameValue $prop, \V8\PropertyCallbackInfo $info) use (&$getter_checks) {
    echo 'Getter callback', PHP_EOL;
    $getter_checks++;
};

$setter_checks = 0;

$setter = function (\V8\NameValue $prop, \V8\Value $value, \V8\PropertyCallbackInfo $info) use (&$setter_checks) {
    echo 'Setter callback', PHP_EOL;
    $setter_checks++;
};


$templ = new \V8\FunctionTemplate($isolate);

$inst = $templ->instanceTemplate();
$inst->setAccessor(new \V8\StringValue($isolate, 'foo'), $getter, $setter, \V8\AccessControl::DEFAULT_ACCESS, \V8\PropertyAttribute::None, $templ);

$context->globalObject()->set($context, new \V8\StringValue($isolate, 'f'), $templ->getFunction($context));

$helper->header('Testing positive');
$obj = $v8_helper->CompileRun($context, "var obj = new f(); obj");
$v8_helper->CHECK($templ->hasInstance($obj), '$obj instance of $templ');

// Test path through generic runtime code.
$v8_helper->CompileRun($context, "obj.foo");
$v8_helper->CompileRun($context, "obj.foo = 23");

$helper->line();

$helper->header('Testing negative');

$obj = $v8_helper->CompileRun($context, "var obj = {}; obj.__proto__ = new f(); obj");
$v8_helper->CHECK(!$templ->hasInstance($obj), '$obj is not an instance of $templ');

// Test path through generic runtime code.
try {
    $v8_helper->CompileRun($context, "obj.foo");
    $helper->fail();
} catch (\V8\Exceptions\TryCatchException $e) {
    $helper->exception_export($e);
}

try {
    $v8_helper->CompileRun($context, "obj.foo = 23");
    $helper->fail();
} catch (\V8\Exceptions\TryCatchException $e) {
    $helper->exception_export($e);
}

$helper->line();
echo 'Done here for now', PHP_EOL;
?>
--EXPECT--
Testing positive:
-----------------
CHECK $obj instance of $templ: OK
Getter callback
Setter callback

Testing negative:
-----------------
CHECK $obj is not an instance of $templ: OK
V8\Exceptions\TryCatchException: TypeError: Method foo called on incompatible receiver [object Object]
V8\Exceptions\TryCatchException: TypeError: Method foo called on incompatible receiver [object Object]

Done here for now
