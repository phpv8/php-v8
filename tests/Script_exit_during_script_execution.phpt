--TEST--
V8\Script::run() - exit during script execution
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

require '.tracking_dtors.php';

$isolate = new v8Tests\TrackingDtors\Isolate();

register_shutdown_function(function () {
    echo 'Doing shutdown', PHP_EOL;
});

$global_template = new v8Tests\TrackingDtors\ObjectTemplate($isolate);

$exit = new v8Tests\TrackingDtors\FunctionTemplate($isolate, function () {
    echo 'Going to exit', PHP_EOL;
    exit();
});

$global_template->set(new \V8\StringValue($isolate, 'exit'), $exit, \V8\PropertyAttribute::DONT_DELETE);

$context = new v8Tests\TrackingDtors\Context($isolate, $global_template);
$v8_helper->injectConsoleLog($context);

$source = '
console.log("before exit");
exit();
console.log("after exit");
';
$file_name = 'test.js';

$script = new v8Tests\TrackingDtors\Script($context, new \V8\StringValue($isolate, $source), new \V8\ScriptOrigin($file_name));

$isolate = null;
$global_template = null;
$exit = null;
$script->run($context);

$context = null;

echo 'Done here', PHP_EOL;
?>
EOF
--EXPECT--
ObjectTemplate dies now!
FunctionTemplate dies now!
before exit
Going to exit
Doing shutdown
Isolate dies now!
Context dies now!
Script dies now!
