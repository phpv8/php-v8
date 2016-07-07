--TEST--
v8\Exception::GetStackTrace()
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php
/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);
$isolate = new \v8\Isolate();
$context = new \v8\Context($isolate);


try {
    $stack_trace = v8\Exception::GetStackTrace($context, new \v8\StringValue($isolate, 'test'));
    $helper->assert('Can get stack trace when out of context', true);
} catch (\Exception $e) {
    $helper->exception_export($e);
}

$helper->line();

//$stack_trace_generation_allowed = false;
//$isolate->SetCaptureStackTraceForUncaughtExceptions($stack_trace_generation_allowed); // actually, this is default behavior

$func_test_tpl = new \v8\FunctionTemplate($isolate, function (\v8\FunctionCallbackInfo $info) use ($helper, $v8_helper, &$stack_trace_generation_allowed) {
    $isolate = $info->GetIsolate();

    $helper->assert('Exception passed', $info->Length() == 1);
    $helper->line();

    $exception = $info->Arguments()[0];


    if (!$stack_trace_generation_allowed) {
        $stack_trace = v8\Exception::GetStackTrace($info->GetContext(), $exception);
        $helper->assert('Stack trace created from thrown value is null when capturing stack trace disabled', $stack_trace === null);
        $helper->line();

        return;
    }

    $stack_trace = v8\Exception::GetStackTrace($info->GetContext(), $exception);
    $helper->header('Stack trace created from thrown value');
    $helper->dump_object_methods($stack_trace, [], new ArrayListFilter(['GetFrame'], true, ReflectionMethod::IS_PUBLIC));
    $helper->line();


    $exception = new \v8\StringValue($info->GetIsolate(), 'test');
    $stack_trace = v8\Exception::GetStackTrace($info->GetContext(), $exception);
    $helper->assert('Stack trace created from manually created value is null', null === $stack_trace);
    $helper->line();
});

$global_tpl = new \v8\ObjectTemplate($isolate);
$global_tpl->Set(new \v8\StringValue($isolate, 'print'), $v8_helper->getPrintFunctionTemplate($isolate));
$global_tpl->Set(new \v8\StringValue($isolate, 'test'), $func_test_tpl);

$context = new \v8\Context($isolate, [], $global_tpl);


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
v8\StackTrace->getFrames():
    array(1) {
      [0]=>
      object(v8\StackFrame)#20 (8) {
        ["line_number":"v8\StackFrame":private]=>
        int(5)
        ["column":"v8\StackFrame":private]=>
        int(15)
        ["script_id":"v8\StackFrame":private]=>
        int(0)
        ["script_name":"v8\StackFrame":private]=>
        string(7) "test.js"
        ["script_name_or_source_url":"v8\StackFrame":private]=>
        string(0) ""
        ["function_name":"v8\StackFrame":private]=>
        string(0) ""
        ["is_eval":"v8\StackFrame":private]=>
        int(0)
        ["is_constructor":"v8\StackFrame":private]=>
        int(0)
      }
    }
v8\StackTrace->GetFrameCount(): int(1)
v8\StackTrace->AsArray():
    object(v8\ArrayObject)#18 (2) {
      ["isolate":"v8\Value":private]=>
      object(v8\Isolate)#3 (1) {
        ["snapshot":"v8\Isolate":private]=>
        NULL
      }
      ["context":"v8\ObjectValue":private]=>
      object(v8\Context)#8 (4) {
        ["isolate":"v8\Context":private]=>
        object(v8\Isolate)#3 (1) {
          ["snapshot":"v8\Isolate":private]=>
          NULL
        }
        ["extensions":"v8\Context":private]=>
        array(0) {
        }
        ["global_template":"v8\Context":private]=>
        object(v8\ObjectTemplate)#7 (1) {
          ["isolate":"v8\Template":private]=>
          object(v8\Isolate)#3 (1) {
            ["snapshot":"v8\Isolate":private]=>
            NULL
          }
        }
        ["global_object":"v8\Context":private]=>
        NULL
      }
    }

Stack trace created from manually created value is null: ok
