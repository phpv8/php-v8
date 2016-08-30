--TEST--
V8\Isolate::IsInUse()
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);


$isolate = new V8\Isolate();

$helper->inline_dump('Isolate in use', $isolate->IsInUse());

$context = new V8\Context($isolate);
$fnc = new \V8\FunctionObject($context, function (\V8\CallbackInfo $info) use ($helper) {
  $helper->inline_dump('Isolate in use', $info->GetIsolate()->IsInUse());
});


$context->GlobalObject()->Set($context, new \V8\StringValue($isolate, 'test'), $fnc);

(new \V8\Script($context, new \V8\StringValue($isolate, 'test()')))->Run($context);

?>
--EXPECT--
Isolate in use: bool(false)
Isolate in use: bool(true)
