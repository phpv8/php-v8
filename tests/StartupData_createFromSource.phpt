--TEST--
V8\StartupData::createFromSource
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--ENV--
HOME=/tmp/we-need-home-env-var-set-to-load-valgrindrc
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
$helper->assert('Snapshot raw_size is the same as binary_string length', $data->getRawSize(), strlen($data->getData()));
$helper->assert('Snapshot raw_size is the same as binary_string length', $data->getRawSize(), strlen($data->getData()));


$isolate = new \V8\Isolate($data);
$data = null;

$context = new \V8\Context($isolate);

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
Snapshot raw_size is the same as binary_string length: ok
Snapshot raw_size is the same as binary_string length: ok
Context global is affected by snapshot blob: ok


V8\Exceptions\Exception: Failed to create startup blob
