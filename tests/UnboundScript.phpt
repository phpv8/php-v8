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

$unbound = $script->getUnboundScript();

$helper->header('UnboundScript representation');
$helper->dump($unbound);
$helper->space();

$helper->header('Accessors');
$helper->method_matches($unbound, 'getIsolate', $isolate);
$helper->method_matches_instanceof($unbound, 'bindToContext', V8\Script::class, [$context]);

$filter = new ArrayListFilter(['getId', 'getScriptName', 'getSourceURL', 'getSourceMappingURL'], false);
$finalizer = new CallChainFinalizer([\V8\StringValue::class => 'value', \V8\UndefinedValue::class => 'isUndefined'], [], false);
$helper->dump_object_methods($unbound, [], $filter, $finalizer);

$helper->space();

$helper->header('Getting line number');
$helper->method_dump($unbound, 'getLineNumber', [-1]);
$helper->method_dump($unbound, 'getLineNumber', [0]);
$helper->method_dump($unbound, 'getLineNumber', [1]);
$helper->method_dump($unbound, 'getLineNumber', [18]);
$helper->method_dump($unbound, 'getLineNumber', [19]);
$helper->method_dump($unbound, 'getLineNumber', [9999]);

$helper->space();


$helper->header('Get script back');
$helper->method_matches_instanceof($unbound, 'bindToContext', V8\Script::class, [$context]);

$new_script = $unbound->bindToContext($context);
$helper->dump($new_script->run($context)->value());
$helper->space();

// EXPECTF: ---/V8\\UnboundScript->getId\(\): int\(\d+\)/
// EXPECTF: +++V8\UnboundScript->getId(): int(%d)
?>
--EXPECTF--
UnboundScript representation:
-----------------------------
object(V8\UnboundScript)#6 (1) {
  ["isolate":"V8\UnboundScript":private]=>
  object(V8\Isolate)#3 (0) {
  }
}


Accessors:
----------
V8\UnboundScript::getIsolate() matches expected value
V8\UnboundScript::bindToContext() result is instance of V8\Script
V8\UnboundScript->getId(): int(%d)
V8\UnboundScript->getScriptName(): V8\StringValue->value(): string(7) "test.js"
V8\UnboundScript->getSourceURL(): V8\UndefinedValue->isUndefined(): bool(true)
V8\UnboundScript->getSourceMappingURL(): V8\StringValue->value(): string(0) ""


Getting line number:
--------------------
V8\UnboundScript::getLineNumber() 0
V8\UnboundScript::getLineNumber() 0
V8\UnboundScript::getLineNumber() 0
V8\UnboundScript::getLineNumber() 0
V8\UnboundScript::getLineNumber() 1
V8\UnboundScript::getLineNumber() -1


Get script back:
----------------
V8\UnboundScript::bindToContext() result is instance of V8\Script
string(11) "test passed"
