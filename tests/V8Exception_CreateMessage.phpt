--TEST--
V8\Exception::CreateMessage()
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
    $message = V8\Exception::CreateMessage($context, new \V8\StringValue($isolate, 'test'));
    $helper->assert('Can create message when out of context', $message instanceof \V8\Message);
} catch (\Exception $e) {
    $helper->exception_export($e);
}

$helper->line();


$func_test_tpl = new \V8\FunctionTemplate($isolate, function (\V8\FunctionCallbackInfo $info) use ($helper, $v8_helper) {

    $helper->assert('Exception passed', $info->Length() == 1);
    $helper->line();

    $exception = $info->Arguments()[0];

    $message = V8\Exception::CreateMessage($info->GetContext(), $exception);
    $helper->header('Message created from thrown value');
    $helper->dump_object_methods($message);
    $helper->line();

    $exception = new \V8\StringValue($info->GetIsolate(), 'test');
    $message = V8\Exception::CreateMessage($info->GetContext(), $exception);
    $helper->header('Message created from created value');
    $helper->dump_object_methods($message);
    $helper->line();
});

$global_tpl = new \V8\ObjectTemplate($isolate);
$global_tpl->Set(new \V8\StringValue($isolate, 'print'), $v8_helper->getPrintFunctionTemplate($isolate));
$global_tpl->Set(new \V8\StringValue($isolate, 'test'), $func_test_tpl);

$context = new \V8\Context($isolate, [], $global_tpl);


$source = '
    var ex;

    try {
        throw {test: "error"};
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

$v8_helper->run_checks($res);
?>
--EXPECTF--
Can create message when out of context: ok

exception: '[object Object]'
exception.stack: <undefined>

Exception passed: ok

Message created from thrown value:
----------------------------------
V8\Message->Get(): string(18) "Uncaught #<Object>"
V8\Message->GetSourceLine(): string(24) "        test(exception);"
V8\Message->GetScriptOrigin():
    object(V8\ScriptOrigin)#22 (6) {
      ["resource_name":"V8\ScriptOrigin":private]=>
      string(7) "test.js"
      ["resource_line_offset":"V8\ScriptOrigin":private]=>
      int(0)
      ["resource_column_offset":"V8\ScriptOrigin":private]=>
      int(0)
      ["options":"V8\ScriptOrigin":private]=>
      object(V8\ScriptOriginOptions)#18 (3) {
        ["is_embedder_debug_script":"V8\ScriptOriginOptions":private]=>
        bool(false)
        ["is_shared_cross_origin":"V8\ScriptOriginOptions":private]=>
        bool(false)
        ["is_opaque":"V8\ScriptOriginOptions":private]=>
        bool(false)
      }
      ["script_id":"V8\ScriptOrigin":private]=>
      int(%d)
      ["source_map_url":"V8\ScriptOrigin":private]=>
      string(0) ""
    }
V8\Message->GetScriptResourceName(): string(7) "test.js"
V8\Message->GetStackTrace(): NULL
V8\Message->GetLineNumber(): int(11)
V8\Message->GetStartPosition(): int(227)
V8\Message->GetEndPosition(): int(228)
V8\Message->GetStartColumn(): int(8)
V8\Message->GetEndColumn(): int(9)
V8\Message->IsSharedCrossOrigin(): bool(false)
V8\Message->IsOpaque(): bool(false)

Message created from created value:
-----------------------------------
V8\Message->Get(): string(13) "Uncaught test"
V8\Message->GetSourceLine(): string(24) "        test(exception);"
V8\Message->GetScriptOrigin():
    object(V8\ScriptOrigin)#36 (6) {
      ["resource_name":"V8\ScriptOrigin":private]=>
      string(7) "test.js"
      ["resource_line_offset":"V8\ScriptOrigin":private]=>
      int(0)
      ["resource_column_offset":"V8\ScriptOrigin":private]=>
      int(0)
      ["options":"V8\ScriptOrigin":private]=>
      object(V8\ScriptOriginOptions)#35 (3) {
        ["is_embedder_debug_script":"V8\ScriptOriginOptions":private]=>
        bool(false)
        ["is_shared_cross_origin":"V8\ScriptOriginOptions":private]=>
        bool(false)
        ["is_opaque":"V8\ScriptOriginOptions":private]=>
        bool(false)
      }
      ["script_id":"V8\ScriptOrigin":private]=>
      int(%d)
      ["source_map_url":"V8\ScriptOrigin":private]=>
      string(0) ""
    }
V8\Message->GetScriptResourceName(): string(7) "test.js"
V8\Message->GetStackTrace(): NULL
V8\Message->GetLineNumber(): int(11)
V8\Message->GetStartPosition(): int(227)
V8\Message->GetEndPosition(): int(228)
V8\Message->GetStartColumn(): int(8)
V8\Message->GetEndColumn(): int(9)
V8\Message->IsSharedCrossOrigin(): bool(false)
V8\Message->IsOpaque(): bool(false)


Checks on V8\ObjectValue:
-------------------------
V8\ObjectValue->IsCallable(): bool(false)
V8\ObjectValue(V8\Value)->IsUndefined(): bool(false)
V8\ObjectValue(V8\Value)->IsNull(): bool(false)
V8\ObjectValue(V8\Value)->IsTrue(): bool(false)
V8\ObjectValue(V8\Value)->IsFalse(): bool(false)
V8\ObjectValue(V8\Value)->IsName(): bool(false)
V8\ObjectValue(V8\Value)->IsString(): bool(false)
V8\ObjectValue(V8\Value)->IsSymbol(): bool(false)
V8\ObjectValue(V8\Value)->IsFunction(): bool(false)
V8\ObjectValue(V8\Value)->IsArray(): bool(false)
V8\ObjectValue(V8\Value)->IsObject(): bool(true)
V8\ObjectValue(V8\Value)->IsBoolean(): bool(false)
V8\ObjectValue(V8\Value)->IsNumber(): bool(false)
V8\ObjectValue(V8\Value)->IsInt32(): bool(false)
V8\ObjectValue(V8\Value)->IsUint32(): bool(false)
V8\ObjectValue(V8\Value)->IsDate(): bool(false)
V8\ObjectValue(V8\Value)->IsArgumentsObject(): bool(false)
V8\ObjectValue(V8\Value)->IsBooleanObject(): bool(false)
V8\ObjectValue(V8\Value)->IsNumberObject(): bool(false)
V8\ObjectValue(V8\Value)->IsStringObject(): bool(false)
V8\ObjectValue(V8\Value)->IsSymbolObject(): bool(false)
V8\ObjectValue(V8\Value)->IsNativeError(): bool(false)
V8\ObjectValue(V8\Value)->IsRegExp(): bool(false)
