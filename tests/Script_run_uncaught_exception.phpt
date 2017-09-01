--TEST--
V8\Script::run() - uncaught exception should not lead to leak
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

$obj = $v8_helper->CompileRun(new \V8\Context(new \V8\Isolate()), 'test');

?>
--EXPECTF--
Fatal error: Uncaught V8\Exceptions\TryCatchException: ReferenceError: test is not defined in %s
Stack trace:
#0 %s: V8\Script->run(Object(V8\Context))
#1 %s: PhpV8Helpers->CompileRun(Object(V8\Context), Object(V8\Script))
#2 {main}
  thrown in %s
