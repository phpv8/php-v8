--TEST--
V8\Script::Run - exit during script execution
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

require '.tracking_dtors.php';

$isolate1 = new v8Tests\TrackingDtors\Isolate();
$extensions1 = [];

register_shutdown_function(function () {
    echo 'Doing shutdown', PHP_EOL;
});

$global_template1 = new v8Tests\TrackingDtors\ObjectTemplate($isolate1);

$exit = new v8Tests\TrackingDtors\FunctionTemplate($isolate1, function () {
    echo 'Going to exit', PHP_EOL;
    exit();
});

$global_template1->Set(new \V8\StringValue($isolate1, 'print'), $v8_helper->getPrintFunctionTemplate($isolate1), \V8\PropertyAttribute::DontDelete);
$global_template1->Set(new \V8\StringValue($isolate1, 'exit'), $exit, \V8\PropertyAttribute::DontDelete);

$context1 = new v8Tests\TrackingDtors\Context($isolate1, $extensions1, $global_template1);


$source1 = '
print("before exit\n");
exit();
print("after exit\n");
';
$file_name1 = 'test.js';

$script = new v8Tests\TrackingDtors\Script($context1, new \V8\StringValue($isolate1, $source1), new \V8\ScriptOrigin($file_name1));

$isolate1 = null;
$context1 = null;
$global_template1 = null;
$exit = null;

$script->Run();

echo 'Done here', PHP_EOL;
?>
EOF
--EXPECT--
FunctionTemplate dies now!
before exit
Going to exit
Doing shutdown
Isolate dies now!
ObjectTemplate dies now!
Context dies now!
Script dies now!
