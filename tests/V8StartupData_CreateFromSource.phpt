--TEST--
V8\StartupData::CreateFromSource
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

$source = 'function test_snapshot() { return "hello, world";}';
$data = V8\StartupData::CreateFromSource($source);

$helper->header('Object representation');
$helper->dump($data);
$helper->space();


$helper->assert('Snapshot blob is large binary string', is_string($data->GetData()) && strlen($data->GetData()) > 400000);
$helper->assert('Snapshot raw_size is the same as binary_string length', $data->GetRawSize(), strlen($data->GetData()));
$helper->assert('Snapshot raw_size is the same as binary_string length', $data->GetRawSize(), strlen($data->GetData()));


$isolate = new \V8\Isolate($data);
$data = null;

$context = new \V8\Context($isolate);

$helper->assert('Context global is affected by snapshot blob', $context->GlobalObject()->Get($context, new \V8\StringValue($isolate, 'test_snapshot'))->IsFunction());


try {
    V8\StartupData::CreateFromSource(') bad +/^\\');
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


V8\Exceptions\GenericException: Failed to create startup blob
