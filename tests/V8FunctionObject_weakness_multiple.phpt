--TEST--
V8\FunctionObject (weakness, multiple time)
--SKIPIF--
<?php if (!extension_loaded("v8")) {
    print "skip";
} ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

require '.tracking_dtors.php';

$isolate1 = new v8Tests\TrackingDtors\Isolate();
$global_template1 = new V8\ObjectTemplate($isolate1);
$context1 = new v8Tests\TrackingDtors\Context($isolate1, $global_template1);
$global_template1 = null;

$func = new v8Tests\TrackingDtors\FunctionObject($context1, function (\V8\FunctionCallbackInfo $info) {
    echo 'Should output Hello World string', PHP_EOL;
});

$func->SetAccessor($context1, new \V8\StringValue($isolate1, 'nonexistent'), function () { echo 'get nonexistent 1', PHP_EOL; } );

$context1->GlobalObject()->Set($context1, new \V8\StringValue($isolate1, 'print'), $func);
$func = null;

$v8_helper->CompileRun($context1, 'print("test"); print.nonexistent; ');


$fnc1 = $context1->GlobalObject()->Get($context1, new \V8\StringValue($isolate1, 'print'));
/** @var $fnc1 \V8\FunctionObject  */
$fnc1->SetAccessor($context1, new \V8\StringValue($isolate1, 'nonexistent'), function () { echo 'get nonexistent 2', PHP_EOL; } );
$v8_helper->CompileRun($context1, 'print("test"); print.nonexistent; ');
$fnc1 = null;


$fnc2 = $context1->GlobalObject()->Get($context1, new \V8\StringValue($isolate1, 'print'));
/** @var $fnc1 \V8\FunctionObject  */
$fnc2->SetAccessor($context1, new \V8\StringValue($isolate1, 'nonexistent'), function () { echo 'get nonexistent 3', PHP_EOL; } );
$v8_helper->CompileRun($context1, 'print("test"); print.nonexistent; ');
$fnc2 = null;

echo 'Persistent should be removed', PHP_EOL;
$isolate1->LowMemoryNotification();


// Here newly create object internally linked to specific callback and persistent, but as it has no own callback, free'ing
// it shouldn't affect functionality
$fnc3 = $context1->GlobalObject()->Get($context1, new \V8\StringValue($isolate1, 'print'));
$v8_helper->CompileRun($context1, 'print("test"); print.nonexistent;');
$fnc3 = null;
$v8_helper->CompileRun($context1, 'print("test"); print.nonexistent;');

$context1 = null;
echo 'Context should be removed', PHP_EOL;

$isolate1->LowMemoryNotification();


echo 'We are done for now', PHP_EOL;

?>
--EXPECT--
FunctionObject dies now!
Should output Hello World string
get nonexistent 1
Should output Hello World string
get nonexistent 2
Should output Hello World string
get nonexistent 3
Persistent should be removed
Should output Hello World string
get nonexistent 3
Should output Hello World string
get nonexistent 3
Context dies now!
Context should be removed
We are done for now
Isolate dies now!
