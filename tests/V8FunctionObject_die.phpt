--TEST--
V8\FunctionObject - test die() during calling
--SKIPIF--
<?php if (!extension_loaded("v8")) {
    print "skip";
} ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.tracking_dtors.php';

$isolate1 = new v8Tests\TrackingDtors\Isolate();
$global_template1 = new V8\ObjectTemplate($isolate1);
$context1 = new V8\Context($isolate1, $global_template1);


$func = new v8Tests\TrackingDtors\FunctionObject($context1, function (\V8\FunctionCallbackInfo $info) {
    echo 'going to die...', PHP_EOL;
    die();
});


$context1->GlobalObject()->Set($context1, new \V8\StringValue($isolate1, 'test'), $func);

$source1 = 'test(); "Script done"';
$file_name1 = 'test.js';


$script1 = new V8\Script($context1, new \V8\StringValue($isolate1, $source1), new \V8\ScriptOrigin($file_name1));

$res = $script1->Run($context1)->ToString($context1)->Value();
$helper->pretty_dump('Script result', $res);

echo 'We are done for now', PHP_EOL;

?>
--EXPECT--
going to die...
FunctionObject dies now!
Isolate dies now!
