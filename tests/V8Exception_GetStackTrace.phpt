--TEST--
V8\Exception::GetStackTrace()
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


try {
    $stack_trace = V8\Exception::GetStackTrace($context, new \V8\StringValue($isolate, 'test'));
    $helper->assert('Can get stack trace when out of context', true);
} catch (\Exception $e) {
    $helper->exception_export($e);
}

$helper->line();

//$stack_trace_generation_allowed = false;
//$isolate->SetCaptureStackTraceForUncaughtExceptions($stack_trace_generation_allowed); // actually, this is default behavior

$func_test_tpl = new \V8\FunctionTemplate($isolate, function (\V8\FunctionCallbackInfo $info) use ($helper, $v8_helper, &$stack_trace_generation_allowed) {
    $isolate = $info->GetIsolate();

    $helper->assert('Exception passed', count($info->Arguments()) == 1);
    $helper->line();

    $exception = $info->Arguments()[0];


    if (!$stack_trace_generation_allowed) {
        $stack_trace = V8\Exception::GetStackTrace($info->GetContext(), $exception);
        $helper->assert('Stack trace created from thrown value is null when capturing stack trace disabled', $stack_trace === null);
        $helper->line();

        return;
    }

    $stack_trace = V8\Exception::GetStackTrace($info->GetContext(), $exception);
    $helper->header('Stack trace created from thrown value');
    $helper->dump_object_methods($stack_trace, [], new ArrayListFilter(['GetFrame'], true, ReflectionMethod::IS_PUBLIC));
    $helper->line();


    $exception = new \V8\StringValue($info->GetIsolate(), 'test');
    $stack_trace = V8\Exception::GetStackTrace($info->GetContext(), $exception);
    $helper->assert('Stack trace created from manually created value is null', null === $stack_trace);
    $helper->line();
});

$global_tpl = new \V8\ObjectTemplate($isolate);
$global_tpl->Set(new \V8\StringValue($isolate, 'print'), $v8_helper->getPrintFunctionTemplate($isolate));
$global_tpl->Set(new \V8\StringValue($isolate, 'test'), $func_test_tpl);

$context = new \V8\Context($isolate, $global_tpl);


$source = '
    var ex;

    try {
        throw new Error("test");
    } catch (exception) {
        print("exception: ", "\'", exception, "\'", "\n");
        print("exception.stack: ", exception.stack, "\n");
        print("\n");

        test(exception);

        ex = exception;
    }

    ex
';

$res = $v8_helper->CompileRun($context, $source);
$helper->line();

$stack_trace_generation_allowed = true;
$isolate->SetCaptureStackTraceForUncaughtExceptions($stack_trace_generation_allowed);

$res = $v8_helper->CompileRun($context, $source);


?>
--EXPECT--
Can get stack trace when out of context: ok

exception: 'Error: test'
exception.stack: Error: test
    at test.js:5:15

Exception passed: ok

Stack trace created from thrown value is null when capturing stack trace disabled: ok


exception: 'Error: test'
exception.stack: Error: test
    at test.js:5:15

Exception passed: ok

Stack trace created from thrown value:
--------------------------------------
V8\StackTrace->getFrames():
    array(1) {
      [0]=>
      object(V8\StackFrame)#19 (8) {
        ["line_number":"V8\StackFrame":private]=>
        int(5)
        ["column":"V8\StackFrame":private]=>
        int(15)
        ["script_id":"V8\StackFrame":private]=>
        int(0)
        ["script_name":"V8\StackFrame":private]=>
        string(7) "test.js"
        ["script_name_or_source_url":"V8\StackFrame":private]=>
        string(0) ""
        ["function_name":"V8\StackFrame":private]=>
        string(0) ""
        ["is_eval":"V8\StackFrame":private]=>
        int(0)
        ["is_constructor":"V8\StackFrame":private]=>
        int(0)
      }
    }
V8\StackTrace->GetFrameCount(): int(1)
V8\StackTrace->AsArray():
    object(V8\ArrayObject)#15 (2) {
      ["isolate":"V8\Value":private]=>
      object(V8\Isolate)#3 (5) {
        ["snapshot":"V8\Isolate":private]=>
        NULL
        ["time_limit":"V8\Isolate":private]=>
        float(0)
        ["time_limit_hit":"V8\Isolate":private]=>
        bool(false)
        ["memory_limit":"V8\Isolate":private]=>
        int(0)
        ["memory_limit_hit":"V8\Isolate":private]=>
        bool(false)
      }
      ["context":"V8\ObjectValue":private]=>
      object(V8\Context)#8 (3) {
        ["isolate":"V8\Context":private]=>
        object(V8\Isolate)#3 (5) {
          ["snapshot":"V8\Isolate":private]=>
          NULL
          ["time_limit":"V8\Isolate":private]=>
          float(0)
          ["time_limit_hit":"V8\Isolate":private]=>
          bool(false)
          ["memory_limit":"V8\Isolate":private]=>
          int(0)
          ["memory_limit_hit":"V8\Isolate":private]=>
          bool(false)
        }
        ["global_template":"V8\Context":private]=>
        object(V8\ObjectTemplate)#7 (1) {
          ["isolate":"V8\Template":private]=>
          object(V8\Isolate)#3 (5) {
            ["snapshot":"V8\Isolate":private]=>
            NULL
            ["time_limit":"V8\Isolate":private]=>
            float(0)
            ["time_limit_hit":"V8\Isolate":private]=>
            bool(false)
            ["memory_limit":"V8\Isolate":private]=>
            int(0)
            ["memory_limit_hit":"V8\Isolate":private]=>
            bool(false)
          }
        }
        ["global_object":"V8\Context":private]=>
        NULL
      }
    }

Stack trace created from manually created value is null: ok
