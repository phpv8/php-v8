--TEST--
V8\ExceptionManager::createCreateMessage()
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--ENV--
HOME=/tmp/we-need-home-env-var-set-to-load-valgrindrc
--FILE--
<?php
/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);
$isolate = new \V8\Isolate();
$context = new \V8\Context($isolate);

try {
    $message = V8\ExceptionManager::createMessage($context, new \V8\StringValue($isolate, 'test'));
    $helper->assert('Can create message when out of context', $message instanceof \V8\Message);
} catch (\Exception $e) {
    $helper->exception_export($e);
}

$helper->line();


$func_test_tpl = new \V8\FunctionTemplate($isolate, function (\V8\FunctionCallbackInfo $info) use ($helper, $v8_helper) {

    $helper->assert('Exception passed', count($info->arguments()) == 1);
    $helper->line();

    $exception = $info->arguments()[0];

    $message = V8\ExceptionManager::createMessage($info->getContext(), $exception);
    $helper->header('Message created from thrown value');
    $helper->dump_object_methods($message);
    $helper->line();

    $exception = new \V8\StringValue($info->getIsolate(), 'test');
    $message = V8\ExceptionManager::createMessage($info->getContext(), $exception);
    $helper->header('Message created from created value');
    $helper->dump_object_methods($message);
    $helper->line();
});

$global_tpl = new \V8\ObjectTemplate($isolate);
$global_tpl->set(new \V8\StringValue($isolate, 'test'), $func_test_tpl);

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

// EXPECTF: ---/\["script_id":"V8\\ScriptOrigin":private\]=>\n      int\(\d+\)/
// EXPECTF: +++["script_id":"V8\ScriptOrigin":private]=>\n      int(%d)
?>
EOF
--EXPECTF--
Can create message when out of context: ok

exception: '[object Object]'
exception.stack: <undefined>

Exception passed: ok

Message created from thrown value:
----------------------------------
V8\Message->get(): string(18) "Uncaught #<Object>"
V8\Message->getSourceLine(): string(24) "        test(exception);"
V8\Message->getScriptOrigin():
    object(V8\ScriptOrigin)#15 (6) {
      ["resource_name":"V8\ScriptOrigin":private]=>
      string(7) "test.js"
      ["resource_line_offset":"V8\ScriptOrigin":private]=>
      NULL
      ["resource_column_offset":"V8\ScriptOrigin":private]=>
      NULL
      ["script_id":"V8\ScriptOrigin":private]=>
      int(%d)
      ["source_map_url":"V8\ScriptOrigin":private]=>
      string(0) ""
      ["options":"V8\ScriptOrigin":private]=>
      object(V8\ScriptOriginOptions)#13 (1) {
        ["flags":"V8\ScriptOriginOptions":private]=>
        int(0)
      }
    }
V8\Message->getScriptResourceName(): string(7) "test.js"
V8\Message->getStackTrace(): NULL
V8\Message->getLineNumber(): int(11)
V8\Message->getStartPosition(): int(231)
V8\Message->getEndPosition(): int(232)
V8\Message->getStartColumn(): int(8)
V8\Message->getEndColumn(): int(9)
V8\Message->isSharedCrossOrigin(): bool(false)
V8\Message->isOpaque(): bool(false)

Message created from created value:
-----------------------------------
V8\Message->get(): string(13) "Uncaught test"
V8\Message->getSourceLine(): string(24) "        test(exception);"
V8\Message->getScriptOrigin():
    object(V8\ScriptOrigin)#35 (6) {
      ["resource_name":"V8\ScriptOrigin":private]=>
      string(7) "test.js"
      ["resource_line_offset":"V8\ScriptOrigin":private]=>
      NULL
      ["resource_column_offset":"V8\ScriptOrigin":private]=>
      NULL
      ["script_id":"V8\ScriptOrigin":private]=>
      int(%d)
      ["source_map_url":"V8\ScriptOrigin":private]=>
      string(0) ""
      ["options":"V8\ScriptOrigin":private]=>
      object(V8\ScriptOriginOptions)#34 (1) {
        ["flags":"V8\ScriptOriginOptions":private]=>
        int(0)
      }
    }
V8\Message->getScriptResourceName(): string(7) "test.js"
V8\Message->getStackTrace(): NULL
V8\Message->getLineNumber(): int(11)
V8\Message->getStartPosition(): int(231)
V8\Message->getEndPosition(): int(232)
V8\Message->getStartColumn(): int(8)
V8\Message->getEndColumn(): int(9)
V8\Message->isSharedCrossOrigin(): bool(false)
V8\Message->isOpaque(): bool(false)


Checks on V8\ObjectValue:
-------------------------
V8\ObjectValue(V8\Value)->typeOf(): V8\StringValue->value(): string(6) "object"

V8\ObjectValue->isCallable(): bool(false)
V8\ObjectValue->isConstructor(): bool(false)
V8\ObjectValue(V8\Value)->isUndefined(): bool(false)
V8\ObjectValue(V8\Value)->isNull(): bool(false)
V8\ObjectValue(V8\Value)->isNullOrUndefined(): bool(false)
V8\ObjectValue(V8\Value)->isTrue(): bool(false)
V8\ObjectValue(V8\Value)->isFalse(): bool(false)
V8\ObjectValue(V8\Value)->isName(): bool(false)
V8\ObjectValue(V8\Value)->isString(): bool(false)
V8\ObjectValue(V8\Value)->isSymbol(): bool(false)
V8\ObjectValue(V8\Value)->isFunction(): bool(false)
V8\ObjectValue(V8\Value)->isArray(): bool(false)
V8\ObjectValue(V8\Value)->isObject(): bool(true)
V8\ObjectValue(V8\Value)->isBoolean(): bool(false)
V8\ObjectValue(V8\Value)->isNumber(): bool(false)
V8\ObjectValue(V8\Value)->isInt32(): bool(false)
V8\ObjectValue(V8\Value)->isUint32(): bool(false)
V8\ObjectValue(V8\Value)->isDate(): bool(false)
V8\ObjectValue(V8\Value)->isArgumentsObject(): bool(false)
V8\ObjectValue(V8\Value)->isBooleanObject(): bool(false)
V8\ObjectValue(V8\Value)->isNumberObject(): bool(false)
V8\ObjectValue(V8\Value)->isStringObject(): bool(false)
V8\ObjectValue(V8\Value)->isSymbolObject(): bool(false)
V8\ObjectValue(V8\Value)->isNativeError(): bool(false)
V8\ObjectValue(V8\Value)->isRegExp(): bool(false)
V8\ObjectValue(V8\Value)->isAsyncFunction(): bool(false)
V8\ObjectValue(V8\Value)->isGeneratorFunction(): bool(false)
V8\ObjectValue(V8\Value)->isGeneratorObject(): bool(false)
V8\ObjectValue(V8\Value)->isPromise(): bool(false)
V8\ObjectValue(V8\Value)->isMap(): bool(false)
V8\ObjectValue(V8\Value)->isSet(): bool(false)
V8\ObjectValue(V8\Value)->isMapIterator(): bool(false)
V8\ObjectValue(V8\Value)->isSetIterator(): bool(false)
V8\ObjectValue(V8\Value)->isWeakMap(): bool(false)
V8\ObjectValue(V8\Value)->isWeakSet(): bool(false)
V8\ObjectValue(V8\Value)->isArrayBuffer(): bool(false)
V8\ObjectValue(V8\Value)->isArrayBufferView(): bool(false)
V8\ObjectValue(V8\Value)->isTypedArray(): bool(false)
V8\ObjectValue(V8\Value)->isUint8Array(): bool(false)
V8\ObjectValue(V8\Value)->isUint8ClampedArray(): bool(false)
V8\ObjectValue(V8\Value)->isInt8Array(): bool(false)
V8\ObjectValue(V8\Value)->isUint16Array(): bool(false)
V8\ObjectValue(V8\Value)->isInt16Array(): bool(false)
V8\ObjectValue(V8\Value)->isUint32Array(): bool(false)
V8\ObjectValue(V8\Value)->isInt32Array(): bool(false)
V8\ObjectValue(V8\Value)->isFloat32Array(): bool(false)
V8\ObjectValue(V8\Value)->isFloat64Array(): bool(false)
V8\ObjectValue(V8\Value)->isDataView(): bool(false)
V8\ObjectValue(V8\Value)->isSharedArrayBuffer(): bool(false)
V8\ObjectValue(V8\Value)->isProxy(): bool(false)


EOF
