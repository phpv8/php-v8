--TEST--
V8\UnboundScript
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);


$isolate = new V8\Isolate();
$context = new V8\Context($isolate);

$source    = 'var test = "test";
let status = "passed";
test + " " + status
';

$file_name = 'test.js';

$script = new V8\Script($context, new \V8\StringValue($isolate, $source), new \V8\ScriptOrigin($file_name));

$unbound = $script->GetUnboundScript();

$helper->header('UnboundScript representation');
$helper->dump($unbound);
$helper->space();

$helper->header('Class constants');
$helper->dump_object_constants($unbound);
$helper->space();

$helper->header('Accessors');
$helper->method_matches($unbound, 'GetIsolate', $isolate);
$helper->method_matches_instanceof($unbound, 'BindToContext', V8\Script::class, [$context]);

$filter = new ArrayListFilter(['GetId', 'GetScriptName', 'GetSourceURL', 'GetSourceMappingURL'], false);
$finalizer = new CallChainFinalizer([\V8\StringValue::class => 'Value', \V8\UndefinedValue::class => 'IsUndefined'], [], false);
$helper->dump_object_methods($unbound, [], $filter, $finalizer);

$helper->space();

$helper->header('Getting line number');
$helper->method_dump($unbound, 'GetLineNumber', [-1]);
$helper->method_dump($unbound, 'GetLineNumber', [0]);
$helper->method_dump($unbound, 'GetLineNumber', [1]);
$helper->method_dump($unbound, 'GetLineNumber', [18]);
$helper->method_dump($unbound, 'GetLineNumber', [19]);
$helper->method_dump($unbound, 'GetLineNumber', [9999]);

$helper->space();


$helper->header('Get script back');
$helper->method_matches_instanceof($unbound, 'BindToContext', V8\Script::class, [$context]);

$new_script = $unbound->BindToContext($context);
$helper->dump($new_script->Run($context)->Value());
$helper->space();

?>
--EXPECTF--
UnboundScript representation:
-----------------------------
object(V8\UnboundScript)#6 (1) {
  ["isolate":"V8\UnboundScript":private]=>
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


Class constants:
----------------
V8\UnboundScript::kNoScriptId = 0


Accessors:
----------
V8\UnboundScript::GetIsolate() matches expected value
V8\UnboundScript::BindToContext() result is instance of V8\Script
V8\UnboundScript->GetId(): int(19)
V8\UnboundScript->GetScriptName(): V8\StringValue->Value(): string(7) "test.js"
V8\UnboundScript->GetSourceURL(): V8\UndefinedValue->IsUndefined(): bool(true)
V8\UnboundScript->GetSourceMappingURL(): V8\StringValue->Value(): string(0) ""


Getting line number:
--------------------
V8\UnboundScript::GetLineNumber() 0
V8\UnboundScript::GetLineNumber() 0
V8\UnboundScript::GetLineNumber() 0
V8\UnboundScript::GetLineNumber() 0
V8\UnboundScript::GetLineNumber() 1
V8\UnboundScript::GetLineNumber() -1


Get script back:
----------------
V8\UnboundScript::BindToContext() result is instance of V8\Script
string(11) "test passed"
