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

    $helper->assert('Exception passed', count($info->Arguments()) == 1);
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
$global_tpl->Set(new \V8\StringValue($isolate, 'test'), $func_test_tpl);

$context = new \V8\Context($isolate, $global_tpl);
$v8_helper->injectConsoleLog($context);


$source = '
    var ex;

    try {
        throw {test: "error"};
    } catch (exception) {
        console.log("exception: ", "\'", exception, "\'");
        console.log("exception.stack: ", exception.stack);
        console.log("");

        test(exception);

        ex = exception;
    }

    ex
';

$res = $v8_helper->CompileRun($context, $source);
$helper->line();

$v8_helper->run_checks($res);
?>
EOF
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
    object(V8\ScriptOrigin)#15 (6) {
      ["resource_name":"V8\ScriptOrigin":private]=>
      string(7) "test.js"
      ["resource_line_offset":"V8\ScriptOrigin":private]=>
      int(0)
      ["resource_column_offset":"V8\ScriptOrigin":private]=>
      int(0)
      ["options":"V8\ScriptOrigin":private]=>
      object(V8\ScriptOriginOptions)#13 (4) {
        ["is_shared_cross_origin":"V8\ScriptOriginOptions":private]=>
        bool(false)
        ["is_opaque":"V8\ScriptOriginOptions":private]=>
        bool(false)
        ["is_wasm":"V8\ScriptOriginOptions":private]=>
        bool(false)
        ["is_module":"V8\ScriptOriginOptions":private]=>
        bool(false)
      }
      ["script_id":"V8\ScriptOrigin":private]=>
      int(19)
      ["source_map_url":"V8\ScriptOrigin":private]=>
      string(0) ""
    }
V8\Message->GetScriptResourceName(): string(7) "test.js"
V8\Message->GetStackTrace(): NULL
V8\Message->GetLineNumber(): int(11)
V8\Message->GetStartPosition(): int(231)
V8\Message->GetEndPosition(): int(232)
V8\Message->GetStartColumn(): int(8)
V8\Message->GetEndColumn(): int(9)
V8\Message->IsSharedCrossOrigin(): bool(false)
V8\Message->IsOpaque(): bool(false)

Message created from created value:
-----------------------------------
V8\Message->Get(): string(13) "Uncaught test"
V8\Message->GetSourceLine(): string(24) "        test(exception);"
V8\Message->GetScriptOrigin():
    object(V8\ScriptOrigin)#35 (6) {
      ["resource_name":"V8\ScriptOrigin":private]=>
      string(7) "test.js"
      ["resource_line_offset":"V8\ScriptOrigin":private]=>
      int(0)
      ["resource_column_offset":"V8\ScriptOrigin":private]=>
      int(0)
      ["options":"V8\ScriptOrigin":private]=>
      object(V8\ScriptOriginOptions)#34 (4) {
        ["is_shared_cross_origin":"V8\ScriptOriginOptions":private]=>
        bool(false)
        ["is_opaque":"V8\ScriptOriginOptions":private]=>
        bool(false)
        ["is_wasm":"V8\ScriptOriginOptions":private]=>
        bool(false)
        ["is_module":"V8\ScriptOriginOptions":private]=>
        bool(false)
      }
      ["script_id":"V8\ScriptOrigin":private]=>
      int(19)
      ["source_map_url":"V8\ScriptOrigin":private]=>
      string(0) ""
    }
V8\Message->GetScriptResourceName(): string(7) "test.js"
V8\Message->GetStackTrace(): NULL
V8\Message->GetLineNumber(): int(11)
V8\Message->GetStartPosition(): int(231)
V8\Message->GetEndPosition(): int(232)
V8\Message->GetStartColumn(): int(8)
V8\Message->GetEndColumn(): int(9)
V8\Message->IsSharedCrossOrigin(): bool(false)
V8\Message->IsOpaque(): bool(false)


Checks on V8\ObjectValue:
-------------------------
V8\ObjectValue(V8\Value)->TypeOf(): V8\StringValue->Value(): string(6) "object"

V8\ObjectValue->IsCallable(): bool(false)
V8\ObjectValue->IsConstructor(): bool(false)
V8\ObjectValue(V8\Value)->IsUndefined(): bool(false)
V8\ObjectValue(V8\Value)->IsNull(): bool(false)
V8\ObjectValue(V8\Value)->IsNullOrUndefined(): bool(false)
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
V8\ObjectValue(V8\Value)->IsAsyncFunction(): bool(false)
V8\ObjectValue(V8\Value)->IsGeneratorFunction(): bool(false)
V8\ObjectValue(V8\Value)->IsGeneratorObject(): bool(false)
V8\ObjectValue(V8\Value)->IsPromise(): bool(false)
V8\ObjectValue(V8\Value)->IsMap(): bool(false)
V8\ObjectValue(V8\Value)->IsSet(): bool(false)
V8\ObjectValue(V8\Value)->IsMapIterator(): bool(false)
V8\ObjectValue(V8\Value)->IsSetIterator(): bool(false)
V8\ObjectValue(V8\Value)->IsWeakMap(): bool(false)
V8\ObjectValue(V8\Value)->IsWeakSet(): bool(false)
V8\ObjectValue(V8\Value)->IsArrayBuffer(): bool(false)
V8\ObjectValue(V8\Value)->IsArrayBufferView(): bool(false)
V8\ObjectValue(V8\Value)->IsTypedArray(): bool(false)
V8\ObjectValue(V8\Value)->IsUint8Array(): bool(false)
V8\ObjectValue(V8\Value)->IsUint8ClampedArray(): bool(false)
V8\ObjectValue(V8\Value)->IsInt8Array(): bool(false)
V8\ObjectValue(V8\Value)->IsUint16Array(): bool(false)
V8\ObjectValue(V8\Value)->IsInt16Array(): bool(false)
V8\ObjectValue(V8\Value)->IsUint32Array(): bool(false)
V8\ObjectValue(V8\Value)->IsInt32Array(): bool(false)
V8\ObjectValue(V8\Value)->IsFloat32Array(): bool(false)
V8\ObjectValue(V8\Value)->IsFloat64Array(): bool(false)
V8\ObjectValue(V8\Value)->IsDataView(): bool(false)
V8\ObjectValue(V8\Value)->IsSharedArrayBuffer(): bool(false)
V8\ObjectValue(V8\Value)->IsProxy(): bool(false)


EOF
