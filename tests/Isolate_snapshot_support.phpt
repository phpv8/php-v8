--TEST--
V8\Isolate - snapshot support
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--ENV--
HOME=/tmp/we-need-home-env-var-set-to-load-valgrindrc
--FILE--
<?php
require '.tracking_dtors.php';

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';


$isolate = new \v8Tests\TrackingDtors\Isolate();

$helper->header('Object representation (no snapshot)');
$helper->dump($isolate);
$helper->space();


$context = new \V8\Context($isolate);

$helper->assert('Context should have no test data', $context->globalObject()->has($context, new \V8\StringValue($isolate, 'test_snapshot')), false);

$helper->line();

$context = null;
$isolate = null;

$helper->space();



$source = 'function test_snapshot() { return "hello, world";}';

$data = V8\StartupData::createFromSource($source);

$isolate = new \v8Tests\TrackingDtors\Isolate($data);


$helper->header('Object representation (with snapshot)');
$helper->dump($isolate);
$helper->space();


$context = new \V8\Context($isolate);

$helper->assert('Context should have test function', $context->globalObject()->get($context, new \V8\StringValue($isolate, 'test_snapshot'))->isFunction());
$context->globalObject()->set($context, new \V8\StringValue($isolate, 'test_snapshot'), new \V8\StringValue($isolate, 'garbage'));

$context = new \V8\Context($isolate);
$helper->assert('Contexts from the same snapshot doesn\'t affected by each other', $context->globalObject()->get($context, new \V8\StringValue($isolate, 'test_snapshot'))->isFunction());

$isolate2 = new \v8Tests\TrackingDtors\Isolate($data);
$context2 = new \V8\Context($isolate2);
$helper->assert('Contexts between different isolates from the same snapshot doesn\'t affected by each other', $context2->globalObject()->get($context2, new \V8\StringValue($isolate2, 'test_snapshot'))->isFunction());

$isolate3 = new \v8Tests\TrackingDtors\Isolate($data);
$data = null;

$context3 = new \V8\Context($isolate3);
$helper->assert('Deleting reference to snapshot is OK after creating Isolate instance', $context3->globalObject()->get($context3, new \V8\StringValue($isolate3, 'test_snapshot'))->isFunction());
$helper->line();

$context = null;
$context = null;
$context2 = null;
$context3 = null;
$isolate = null;
$isolate2 = null;
$isolate3 = null;

echo 'END', PHP_EOL;
?>
--EXPECT--
Object representation (no snapshot):
------------------------------------
object(v8Tests\TrackingDtors\Isolate)#2 (0) {
}


Context should have no test data: ok

Isolate dies now!


Object representation (with snapshot):
--------------------------------------
object(v8Tests\TrackingDtors\Isolate)#3 (0) {
}


Context should have test function: ok
Contexts from the same snapshot doesn't affected by each other: ok
Contexts between different isolates from the same snapshot doesn't affected by each other: ok
Deleting reference to snapshot is OK after creating Isolate instance: ok

Isolate dies now!
Isolate dies now!
Isolate dies now!
END
