--TEST--
v8\Exception::CreateMessage()
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
    $message = v8\Exception::CreateMessage($context, new \v8\StringValue($isolate, 'test'));
    $helper->assert('Can create message when out of context', $message instanceof \v8\Message);
} catch (\Exception $e) {
    $helper->exception_export($e);
}

$helper->line();


$func_test_tpl = new \v8\FunctionTemplate($isolate, function (\v8\FunctionCallbackInfo $info) use ($helper, $v8_helper) {

    $helper->assert('Exception passed', $info->Length() == 1);
    $helper->line();

    $exception = $info->Arguments()[0];

    $message = v8\Exception::CreateMessage($info->GetContext(), $exception);
    $helper->header('Message created from thrown value');
    $helper->dump_object_methods($message);
    $helper->line();

    $exception = new \v8\StringValue($info->GetIsolate(), 'test');
    $message = v8\Exception::CreateMessage($info->GetContext(), $exception);
    $helper->header('Message created from created value');
    $helper->dump_object_methods($message);
    $helper->line();
});

$global_tpl = new \v8\ObjectTemplate($isolate);
$global_tpl->Set(new \v8\StringValue($isolate, 'print'), $v8_helper->getPrintFunctionTemplate($isolate));
$global_tpl->Set(new \v8\StringValue($isolate, 'test'), $func_test_tpl);

$context = new \v8\Context($isolate, [], $global_tpl);


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
v8\Message->Get(): string(18) "Uncaught #<Object>"
v8\Message->GetSourceLine(): string(24) "        test(exception);"
v8\Message->GetScriptOrigin():
    object(v8\ScriptOrigin)#22 (6) {
      ["resource_name":"v8\ScriptOrigin":private]=>
      string(7) "test.js"
      ["resource_line_offset":"v8\ScriptOrigin":private]=>
      int(0)
      ["resource_column_offset":"v8\ScriptOrigin":private]=>
      int(0)
      ["options":"v8\ScriptOrigin":private]=>
      object(v8\ScriptOriginOptions)#18 (3) {
        ["is_embedder_debug_script":"v8\ScriptOriginOptions":private]=>
        bool(false)
        ["is_shared_cross_origin":"v8\ScriptOriginOptions":private]=>
        bool(false)
        ["is_opaque":"v8\ScriptOriginOptions":private]=>
        bool(false)
      }
      ["script_id":"v8\ScriptOrigin":private]=>
      int(%d)
      ["source_map_url":"v8\ScriptOrigin":private]=>
      string(0) ""
    }
v8\Message->GetScriptResourceName(): string(7) "test.js"
v8\Message->GetStackTrace(): NULL
v8\Message->GetLineNumber(): int(11)
v8\Message->GetStartPosition(): int(227)
v8\Message->GetEndPosition(): int(228)
v8\Message->GetStartColumn(): int(8)
v8\Message->GetEndColumn(): int(9)
v8\Message->IsSharedCrossOrigin(): bool(false)
v8\Message->IsOpaque(): bool(false)

Message created from created value:
-----------------------------------
v8\Message->Get(): string(13) "Uncaught test"
v8\Message->GetSourceLine(): string(24) "        test(exception);"
v8\Message->GetScriptOrigin():
    object(v8\ScriptOrigin)#36 (6) {
      ["resource_name":"v8\ScriptOrigin":private]=>
      string(7) "test.js"
      ["resource_line_offset":"v8\ScriptOrigin":private]=>
      int(0)
      ["resource_column_offset":"v8\ScriptOrigin":private]=>
      int(0)
      ["options":"v8\ScriptOrigin":private]=>
      object(v8\ScriptOriginOptions)#35 (3) {
        ["is_embedder_debug_script":"v8\ScriptOriginOptions":private]=>
        bool(false)
        ["is_shared_cross_origin":"v8\ScriptOriginOptions":private]=>
        bool(false)
        ["is_opaque":"v8\ScriptOriginOptions":private]=>
        bool(false)
      }
      ["script_id":"v8\ScriptOrigin":private]=>
      int(%d)
      ["source_map_url":"v8\ScriptOrigin":private]=>
      string(0) ""
    }
v8\Message->GetScriptResourceName(): string(7) "test.js"
v8\Message->GetStackTrace(): NULL
v8\Message->GetLineNumber(): int(11)
v8\Message->GetStartPosition(): int(227)
v8\Message->GetEndPosition(): int(228)
v8\Message->GetStartColumn(): int(8)
v8\Message->GetEndColumn(): int(9)
v8\Message->IsSharedCrossOrigin(): bool(false)
v8\Message->IsOpaque(): bool(false)


Checks on v8\ObjectValue:
-------------------------
v8\ObjectValue->IsCallable(): bool(false)
v8\ObjectValue(v8\Value)->IsUndefined(): bool(false)
v8\ObjectValue(v8\Value)->IsNull(): bool(false)
v8\ObjectValue(v8\Value)->IsTrue(): bool(false)
v8\ObjectValue(v8\Value)->IsFalse(): bool(false)
v8\ObjectValue(v8\Value)->IsName(): bool(false)
v8\ObjectValue(v8\Value)->IsString(): bool(false)
v8\ObjectValue(v8\Value)->IsSymbol(): bool(false)
v8\ObjectValue(v8\Value)->IsFunction(): bool(false)
v8\ObjectValue(v8\Value)->IsArray(): bool(false)
v8\ObjectValue(v8\Value)->IsObject(): bool(true)
v8\ObjectValue(v8\Value)->IsBoolean(): bool(false)
v8\ObjectValue(v8\Value)->IsNumber(): bool(false)
v8\ObjectValue(v8\Value)->IsInt32(): bool(false)
v8\ObjectValue(v8\Value)->IsUint32(): bool(false)
v8\ObjectValue(v8\Value)->IsDate(): bool(false)
v8\ObjectValue(v8\Value)->IsArgumentsObject(): bool(false)
v8\ObjectValue(v8\Value)->IsBooleanObject(): bool(false)
v8\ObjectValue(v8\Value)->IsNumberObject(): bool(false)
v8\ObjectValue(v8\Value)->IsStringObject(): bool(false)
v8\ObjectValue(v8\Value)->IsSymbolObject(): bool(false)
v8\ObjectValue(v8\Value)->IsNativeError(): bool(false)
v8\ObjectValue(v8\Value)->IsRegExp(): bool(false)
