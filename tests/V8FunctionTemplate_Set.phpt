--TEST--
V8\FunctionTemplate::Set()
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
$context = new V8\Context($isolate);
$v8_helper->injectConsoleLog($context);

// Tests:

$ft = new \V8\FunctionTemplate($isolate);
$ft->SetClassName(new \V8\StringValue($isolate, 'Test'));

$proto = $ft->PrototypeTemplate();

$f = new \V8\FunctionObject($context, function (\V8\FunctionCallbackInfo $args) {
});

try {
    $proto->Set(new \V8\StringValue($isolate, 'foo'), $f);
} catch (TypeError $e) {
    $helper->exception_export($e);
}

$ftpl2 = new \V8\FunctionTemplate($isolate, function (\V8\FunctionCallbackInfo $args) {
    var_dump($args->This(), $args->Holder(), $args->NewTarget());
});

$proto->Set(new \V8\StringValue($isolate, 'foo'), $ftpl2);

$context->GlobalObject()->Set($context, new \V8\StringValue($isolate, 't'), $ft->GetFunction($context));


$v8_helper->CompileRun($context, '
console.log(t);
let nt = new t();
console.log(nt);
console.log(nt.foo);
');


?>
--EXPECT--
TypeError: Argument 3 passed to V8\ObjectTemplate::Set() should be instance of \V8\PrimitiveValue or \V8\Template, instance of V8\FunctionObject given
function Test() { [native code] }
[object Test]
function foo() { [native code] }
