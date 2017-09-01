--TEST--
V8\Isolate::throwException() - exception object is still the same
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php
/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);


$isolate = new \V8\Isolate();
$context = new \V8\Context($isolate);
$v8_helper->injectConsoleLog($context);

$global = $context->globalObject();

try {
    // associating external exception with non-object v8 values is not possible
    $isolate->throwException($context, new \V8\StringValue($isolate, 'test'), new RuntimeException('test'));
} catch (\V8\Exceptions\ValueException $e) {
    $helper->exception_export($e);
}


$v8_exception = \V8\ExceptionManager::createError($context, new \V8\StringValue($isolate, 'test'));

$func_tpl = new \V8\FunctionObject($context, function (\V8\FunctionCallbackInfo $info) use (&$v8_exception) {
    $info->getIsolate()->throwException($info->getContext(), $v8_exception, new RuntimeException('test'));
});

$global->set($context, new \V8\StringValue($isolate, 'e'), $func_tpl);


try {
    $v8_helper->CompileRun($context, 'e()');
} catch (\V8\Exceptions\TryCatchException $e) {
    $helper->exception_export($e);

    $helper->assert('Thrown exception object is the same', $e->getTryCatch()->exception(), $v8_exception);

    $helper->exception_export($e->getTryCatch()->getExternalException());
}

$v8_helper->CompileRun($context, 'try {e()} catch(e) {}');

try {
    // when we catch thrown exception within v8 runtime, we can't un-wire association, so it becomes illegal to reuse
    // the same v8 object exception to throw with external exception again
    $isolate->throwException($context, $v8_exception, new RuntimeException('test'));
} catch (\V8\Exceptions\ValueException $e) {
    $helper->exception_export($e);
}

$v8_exception = \V8\ExceptionManager::createError($context, new \V8\StringValue($isolate, 'test'));


// re-throw the same v8 object after it was propagated through TryCatch mechanism is OK
$isolate->throwException($context, $v8_exception, new RuntimeException('test'));

try {
    // re-throw v8 object while it has not been propagated through TryCatch mechanism is NOT OK
    $isolate->throwException($context, $v8_exception, new RuntimeException('test'));
} catch (\V8\Exceptions\ValueException $e) {
    $helper->exception_export($e);
}


?>
--EXPECT--
V8\Exceptions\ValueException: Unable to associate external exception with non-object value
V8\Exceptions\TryCatchException: Error: test
Thrown exception object is the same: ok
RuntimeException: test
V8\Exceptions\ValueException: Another external exception is already associated with a given value
V8\Exceptions\ValueException: Another external exception is already associated with a given value
