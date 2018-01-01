--TEST--
V8\Isolate::throwException() - external exception is not lost when provided with refcount=1
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php
/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);


class TestException extends RuntimeException {
    public function __destruct()
    {
        echo __METHOD__, PHP_EOL;
    }
}

$isolate = new \V8\Isolate();
$context = new \V8\Context($isolate);
$v8_helper->injectConsoleLog($context);

$global = $context->globalObject();

$func_tpl = new \V8\FunctionObject($context, function (\V8\FunctionCallbackInfo $info) {
    $isolate = $info->getIsolate();
    $context = $info->getContext();
    $info->getIsolate()->throwException($info->getContext(), \V8\ExceptionManager::createError($context, new \V8\StringValue($isolate, 'test')), new TestException('test'));
});

$global->set($context, new \V8\StringValue($isolate, 'e'), $func_tpl);

try {
    $v8_helper->CompileRun($context, 'e()');
} catch (\V8\Exceptions\TryCatchException $e) {
    $helper->exception_export($e);
    $helper->assert('External exception present', $e->getTryCatch()->getExternalException() instanceof TestException);
    $helper->exception_export($e->getTryCatch()->getExternalException());
    $e = null;
}

$helper->message('done');
$helper->line();

$helper->header('Run with js try-catch');
$v8_helper->CompileRun($context, 'try {e()} catch(e) {}');

$helper->message('done');

$func_tpl = null;
$global = null;
$context = null;
$v8_helper = null;

$isolate = null;

?>
--EXPECT--
V8\Exceptions\TryCatchException: Error: test
External exception present: ok
TestException: test
TestException::__destruct
done

Run with js try-catch:
----------------------
TestException::__destruct
done
