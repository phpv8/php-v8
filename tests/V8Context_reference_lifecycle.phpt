--TEST--
V8\Context reference lifecycle
--SKIPIF--
<?php if (!extension_loaded("v8")) {
    print "skip";
} ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

require '.tracking_dtors.php';

$isolate = new \V8\Isolate();


$obj = $v8_helper->CompileRun(new \v8Tests\TrackingDtors\Context($isolate), 'var obj = {}; obj');

//$helper->dump($obj);
$helper->dump($obj->GetContext());


$context = new \v8Tests\TrackingDtors\Context($isolate);
$context->GlobalObject()->Set($context, new \V8\StringValue($isolate, 'obj'), $obj);

$helper->line();
$obj = null;
$helper->line();

$helper->message('Previous context should be dead, creating zval for object from old context');
$helper->line();

$obj = $v8_helper->CompileRun($context, 'var obj2 = obj; obj2');

//$helper->dump($obj);
$helper->dump($obj->GetContext());
$obj = null;
?>
--EXPECT--
object(v8Tests\TrackingDtors\Context)#4 (1) {
  ["isolate":"V8\Context":private]=>
  object(V8\Isolate)#3 (4) {
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

Context dies now!

Previous context should be dead, creating zval for object from old context

object(v8Tests\TrackingDtors\Context)#6 (1) {
  ["isolate":"V8\Context":private]=>
  object(V8\Isolate)#3 (4) {
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
Context dies now!
