--TEST--
V8\StartupData::warmUpSnapshotDataBlob
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--ENV--
HOME=/tmp/we-need-home-env-var-set-to-load-valgrindrc
--FILE--
<?php
require '.tracking_dtors.php';

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);



$source = 'function test_snapshot() { return "hello, world";}';
$data = V8\StartupData::createFromSource($source);

$isolate = new V8\Isolate($data);
$context = new \V8\Context($isolate);

$helper->assert('Context should have test function', $v8_helper->CompileTryRun($context, 'test_snapshot()')->value(), 'hello, world');

$wam_source = 'test_snapshot = function () { return "hello, warm world";}';

$warm_data = V8\StartupData::warmUpSnapshotDataBlob($data, $wam_source);


$isolate = new V8\Isolate($warm_data);
$context = new \V8\Context($isolate);


$helper->assert('Warm data has no side effects', $res = $v8_helper->CompileTryRun($context, 'test_snapshot()')->value(), 'hello, world');


?>
--EXPECT--
Context should have test function: ok
Warm data has no side effects: ok
