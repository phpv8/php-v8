--TEST--
V8\Context reference lifecycle
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

require '.tracking_dtors.php';

$isolate = new \V8\Isolate();


$obj = $v8_helper->CompileRun(new \v8Tests\TrackingDtors\Context($isolate), 'var obj = {}; obj');

//$helper->dump($obj);
$helper->dump($obj->getContext());


$context = new \v8Tests\TrackingDtors\Context($isolate);
$context->globalObject()->set($context, new \V8\StringValue($isolate, 'obj'), $obj);

$helper->line();
$obj = null;
$helper->line();

$helper->message('Previous context should be dead, creating zval for object from old context');
$helper->line();

$obj = $v8_helper->CompileRun($context, 'var obj2 = obj; obj2');

//$helper->dump($obj);
$helper->dump($obj->getContext());
$obj = null;
?>
--EXPECT--
object(v8Tests\TrackingDtors\Context)#4 (1) {
  ["isolate":"V8\Context":private]=>
  object(V8\Isolate)#3 (0) {
  }
}

Context dies now!

Previous context should be dead, creating zval for object from old context

object(v8Tests\TrackingDtors\Context)#6 (1) {
  ["isolate":"V8\Context":private]=>
  object(V8\Isolate)#3 (0) {
  }
}
Context dies now!
