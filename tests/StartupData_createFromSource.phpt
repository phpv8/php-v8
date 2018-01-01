--TEST--
V8\StartupData::createFromSource
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

$source = 'function test_snapshot() { return "hello, world";}';
$data = V8\StartupData::createFromSource($source);

$helper->header('Object representation');
$helper->dump($data);
$helper->space();


$helper->assert('Snapshot blob is large binary string', is_string($data->getData()) && strlen($data->getData()) > 400000);
$helper->assert('Snapshot blob is not rejected', $data->isRejected(), false);

$isolate = new \V8\Isolate($data);
$context = new \V8\Context($isolate);

$helper->assert('Snapshot blob is not rejected', $data->isRejected(), false);
$data = null;

$helper->assert('Context global is affected by snapshot blob', $context->globalObject()->get($context, new \V8\StringValue($isolate, 'test_snapshot'))->isFunction());


try {
    V8\StartupData::createFromSource(') bad +/^\\');
    $helper->assert('Unable to create startup data from bad source', false);
} catch (Exception $e) {
    $helper->space();
    $helper->exception_export($e);
}

?>
--EXPECT--
Object representation:
----------------------
object(V8\StartupData)#2 (0) {
}


Snapshot blob is large binary string: ok
Snapshot blob is not rejected: ok
Snapshot blob is not rejected: ok
Context global is affected by snapshot blob: ok


V8\Exceptions\Exception: Failed to create startup blob
