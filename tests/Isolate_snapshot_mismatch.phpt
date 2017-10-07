--TEST--
V8\Isolate - snapshot mismatch
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


$cache = __DIR__ . '/stubs/isolate-snapshot-test.bin';

// $store = true;
$store = false;

if ($store) {
    $data = V8\StartupData::createFromSource($source);

    echo $cache, PHP_EOL;
    // file_put_contents($cache, $data->getData());

    var_dump(substr($res = $data->getData(),0, 50));
    var_dump(md5($res));
}

$blob = file_get_contents($cache);

$data = new V8\StartupData($blob);

if ($store) {
    var_dump(substr($res = $data->getData(),0, 50));
    var_dump(md5($res));
}

$isolate = new \v8Tests\TrackingDtors\Isolate($data);
$helper->assert('Snapshot blob is rejected', $data->isRejected(), true);

$context = new \V8\Context($isolate);

$helper->assert('Context should not have test function', $context->globalObject()->get($context, new \V8\StringValue($isolate, 'test_snapshot'))->isUndefined());

$isolate = null;
$context = null;
$helper->space();

echo 'END', PHP_EOL;
?>
--EXPECT--
Object representation (no snapshot):
------------------------------------
object(v8Tests\TrackingDtors\Isolate)#2 (0) {
}


Context should have no test data: ok

Isolate dies now!


Snapshot blob is rejected: ok
Context should not have test function: ok
Isolate dies now!


END
