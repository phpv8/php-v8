--TEST--
V8\TryCatch - getting from script
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

require '.tracking_dtors.php';

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);


$isolate = new \v8Tests\TrackingDtors\Isolate();


$nested_try_catch_func_tpl = new \v8Tests\TrackingDtors\FunctionTemplate($isolate, function (\V8\FunctionCallbackInfo $args) use ($helper) {
    $isolate = $args->GetIsolate();
    $nested_context = new \V8\Context($isolate);

    $source = /** @lang JavaScript */
        'throw new Error("Nested error");';

    $file_name = 'nested-test.js';

    $script = new v8Tests\TrackingDtors\Script($nested_context, new \V8\StringValue($isolate, $source), new \V8\ScriptOrigin($file_name));

    try{
      $script->Run($nested_context);
    } catch (V8\Exceptions\TryCatchException $e) {
        $helper->exception_export($e);
        $helper->line();

        $helper->assert('TryCatchException holds the same isolate it was thrown', $e->GetIsolate(), $isolate);
        $helper->assert('TryCatchException holds the same context it was thrown', $e->GetContext(), $nested_context);

        $try_catch = $e->GetTryCatch();

        $helper->assert('TryCatch holds the same isolate it was thrown', $try_catch->GetIsolate(), $isolate);
        $helper->assert('TryCatch holds the same context it was thrown', $try_catch->GetContext(), $nested_context);

        $helper->dump($e->GetTryCatch()->Message()->Get());
        $helper->line();
    }
});

$global_object_tpl = new \v8Tests\TrackingDtors\ObjectTemplate($isolate);
$global_object_tpl->Set(new \V8\StringValue($isolate, 'nested_throw'), $nested_try_catch_func_tpl);
$context = new v8Tests\TrackingDtors\Context($isolate, [], $global_object_tpl);

$source = /** @lang JavaScript */
    'throw new Error("Top-level error");';
$file_name = 'test.js';

$script = new v8Tests\TrackingDtors\Script($context, new \V8\StringValue($isolate, $source), new \V8\ScriptOrigin($file_name));

try {
    $res = $script->Run($context);
} catch (V8\Exceptions\TryCatchException $e) {
    $helper->exception_export($e);
    $helper->line();

    $helper->assert('TryCatchException holds the same isolate it was thrown', $e->GetIsolate(), $script->GetIsolate());
    $helper->assert('TryCatchException holds the same context it was thrown', $e->GetContext(), $script->GetContext());

    $try_catch = $e->GetTryCatch();
    $helper->assert('TryCatch holds the same isolate it was thrown', $try_catch->GetIsolate(), $script->GetIsolate());
    $helper->assert('TryCatch holds the same context it was thrown', $try_catch->GetContext(), $script->GetContext());

    $helper->dump($e->GetTryCatch()->Message()->Get());

    $helper->line();
    $helper->assert('TryCatchException message has not stack trace', $e->GetTryCatch()->Message()->GetStackTrace() === null);
    $helper->line();
}

$isolate->SetCaptureStackTraceForUncaughtExceptions(true);

try {
    $res = $script->Run($context);
} catch (V8\Exceptions\TryCatchException $e) {
    $helper->exception_export($e);
    $helper->line();

    $helper->assert('TryCatchException message has stack trace', $e->GetTryCatch()->Message()->GetStackTrace() instanceof \V8\StackTrace);
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
    $res = $script->Run($context);
} catch (V8\Exceptions\TryCatchException $e) {
    $helper->exception_export($e);
    $helper->line();

    $helper->assert('TryCatchException holds the same isolate it was thrown', $e->GetIsolate(), $script->GetIsolate());
    $helper->assert('TryCatchException holds the same context it was thrown', $e->GetContext(), $script->GetContext());

    $try_catch = $e->GetTryCatch();
    $helper->assert('TryCatch holds the same isolate it was thrown', $try_catch->GetIsolate(), $script->GetIsolate());
    $helper->assert('TryCatch holds the same context it was thrown', $try_catch->GetContext(), $script->GetContext());

    $helper->dump($e->GetTryCatch()->Message()->Get());
    $helper->line();
}

try {
    $file_name = 'garbage.js';
    $script = new v8Tests\TrackingDtors\Script($context, new \V8\StringValue($isolate, 'asd 1221as1 e^'), new \V8\ScriptOrigin($file_name));
} catch (V8\Exceptions\TryCatchException $e) {
    $helper->exception_export($e);
    $helper->line();

    $helper->assert('TryCatchException holds the same context it was thrown', $e->GetContext(), $context);
    $helper->assert('TryCatchException holds the same isolate it was thrown', $e->GetIsolate(), $isolate);

    $helper->dump($e->GetTryCatch()->Message()->Get());
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

V8\Exceptions\TryCatchException: SyntaxError: Unexpected number

TryCatchException holds the same context it was thrown: ok
TryCatchException holds the same isolate it was thrown: ok
string(39) "Uncaught SyntaxError: Unexpected number"

Script dies now!
FunctionTemplate dies now!
Context dies now!
ObjectTemplate dies now!
Isolate dies now!
END
