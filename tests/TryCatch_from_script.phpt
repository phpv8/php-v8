--TEST--
V8\TryCatch - getting from script
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--ENV--
HOME=/tmp/we-need-home-env-var-set-to-load-valgrindrc
--FILE--
<?php

require '.tracking_dtors.php';

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);


$isolate = new \v8Tests\TrackingDtors\Isolate();


$nested_try_catch_func_tpl = new \v8Tests\TrackingDtors\FunctionTemplate($isolate, function (\V8\FunctionCallbackInfo $args) use ($helper) {
    $isolate = $args->getIsolate();
    $nested_context = new \V8\Context($isolate);

    $source = /** @lang JavaScript */
        'throw new Error("Nested error");';

    $file_name = 'nested-test.js';

    $script = new v8Tests\TrackingDtors\Script($nested_context, new \V8\StringValue($isolate, $source), new \V8\ScriptOrigin($file_name));

    try{
      $script->run($nested_context);
    } catch (V8\Exceptions\TryCatchException $e) {
        $helper->exception_export($e);
        $helper->line();

        $helper->assert('TryCatchException holds the same isolate it was thrown', $e->getIsolate(), $isolate);
        $helper->assert('TryCatchException holds the same context it was thrown', $e->getContext(), $nested_context);

        $try_catch = $e->getTryCatch();

        $helper->assert('TryCatch holds the same isolate it was thrown', $try_catch->getIsolate(), $isolate);
        $helper->assert('TryCatch holds the same context it was thrown', $try_catch->getContext(), $nested_context);

        $helper->dump($e->getTryCatch()->message()->get());
        $helper->line();
    }
});

$global_object_tpl = new \v8Tests\TrackingDtors\ObjectTemplate($isolate);
$global_object_tpl->set(new \V8\StringValue($isolate, 'nested_throw'), $nested_try_catch_func_tpl);
$context = new v8Tests\TrackingDtors\Context($isolate, $global_object_tpl);

$source = /** @lang JavaScript */
    'throw new Error("Top-level error");';
$file_name = 'test.js';

$script = new v8Tests\TrackingDtors\Script($context, new \V8\StringValue($isolate, $source), new \V8\ScriptOrigin($file_name));

try {
    $res = $script->run($context);
} catch (V8\Exceptions\TryCatchException $e) {
    $helper->exception_export($e);
    $helper->line();

    $helper->assert('TryCatchException holds the same isolate it was thrown', $e->getIsolate(), $script->getIsolate());
    $helper->assert('TryCatchException holds the same context it was thrown', $e->getContext(), $script->getContext());

    $try_catch = $e->getTryCatch();
    $helper->assert('TryCatch holds the same isolate it was thrown', $try_catch->getIsolate(), $script->getIsolate());
    $helper->assert('TryCatch holds the same context it was thrown', $try_catch->getContext(), $script->getContext());

    $helper->dump($e->getTryCatch()->message()->get());

    $helper->line();
    $helper->assert('TryCatchException message has not stack trace', $e->getTryCatch()->message()->getStackTrace() === null);
    $helper->line();
}

$isolate->setCaptureStackTraceForUncaughtExceptions(true);

try {
    $res = $script->run($context);
} catch (V8\Exceptions\TryCatchException $e) {
    $helper->exception_export($e);
    $helper->line();

    $helper->assert('TryCatchException message has stack trace', $e->getTryCatch()->message()->getStackTrace() instanceof \V8\StackTrace);
    $helper->line();
}


$source = /** @lang JavaScript */
    '
nested_throw();
throw new Error("Top-level error");
';
$file_name = 'test.js';

$script = new v8Tests\TrackingDtors\Script($context, new \V8\StringValue($isolate, $source));


try {
    $res = $script->run($context);
} catch (V8\Exceptions\TryCatchException $e) {
    $helper->exception_export($e);
    $helper->line();

    $helper->assert('TryCatchException holds the same isolate it was thrown', $e->getIsolate(), $script->getIsolate());
    $helper->assert('TryCatchException holds the same context it was thrown', $e->getContext(), $script->getContext());

    $try_catch = $e->getTryCatch();
    $helper->assert('TryCatch holds the same isolate it was thrown', $try_catch->getIsolate(), $script->getIsolate());
    $helper->assert('TryCatch holds the same context it was thrown', $try_catch->getContext(), $script->getContext());

    $helper->dump($e->getTryCatch()->message()->get());
    $helper->line();
}

try {
    $file_name = 'garbage.js';
    $script = new v8Tests\TrackingDtors\Script($context, new \V8\StringValue($isolate, 'asd 1221as1 e^'), new \V8\ScriptOrigin($file_name));
} catch (V8\Exceptions\TryCatchException $e) {
    $helper->exception_export($e);
    $helper->line();

    $helper->assert('TryCatchException holds the same context it was thrown', $e->getContext(), $context);
    $helper->assert('TryCatchException holds the same isolate it was thrown', $e->getIsolate(), $isolate);

    $helper->dump($e->getTryCatch()->message()->get());
    $helper->line();
}


$e = null;
$res = null;
$script = null;
$global_object_tpl = null;
$nested_try_catch_func_tpl = null;
$try_catch = null;
$isolate = null;
$context = null;

echo "END", PHP_EOL;
?>
--EXPECT--
V8\Exceptions\TryCatchException: Error: Top-level error

TryCatchException holds the same isolate it was thrown: ok
TryCatchException holds the same context it was thrown: ok
TryCatch holds the same isolate it was thrown: ok
TryCatch holds the same context it was thrown: ok
string(31) "Uncaught Error: Top-level error"

TryCatchException message has not stack trace: ok

V8\Exceptions\TryCatchException: Error: Top-level error

TryCatchException message has stack trace: ok

Script dies now!
V8\Exceptions\TryCatchException: Error: Nested error

TryCatchException holds the same isolate it was thrown: ok
TryCatchException holds the same context it was thrown: ok
TryCatch holds the same isolate it was thrown: ok
TryCatch holds the same context it was thrown: ok
string(28) "Uncaught Error: Nested error"

Script dies now!
V8\Exceptions\TryCatchException: Error: Top-level error

TryCatchException holds the same isolate it was thrown: ok
TryCatchException holds the same context it was thrown: ok
TryCatch holds the same isolate it was thrown: ok
TryCatch holds the same context it was thrown: ok
string(31) "Uncaught Error: Top-level error"

V8\Exceptions\TryCatchException: SyntaxError: Invalid or unexpected token

TryCatchException holds the same context it was thrown: ok
TryCatchException holds the same isolate it was thrown: ok
string(49) "Uncaught SyntaxError: Invalid or unexpected token"

Script dies now!
ObjectTemplate dies now!
FunctionTemplate dies now!
Context dies now!
Isolate dies now!
END
