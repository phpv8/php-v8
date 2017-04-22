--TEST--
V8\Context weakness
--SKIPIF--
<?php if (!extension_loaded("v8")) {
    print "skip";
} ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.tracking_dtors.php';


$isolate1 = new \V8\Isolate();

$global_template1 = new V8\ObjectTemplate($isolate1);

$source1 = 'var obj = {}; obj';
$file_name1 = 'test.js';

$script1 = new \V8\Script(
    new \v8Tests\TrackingDtors\Context($isolate1, $global_template1),
    new \V8\StringValue($isolate1, $source1),
    new \V8\ScriptOrigin($file_name1)
);


$obj = $script1->Run($script1->GetContext())->ToObject($script1->GetContext()); // contest should be stored in object

$script1 = null;

echo 'We are done for now', PHP_EOL;
?>
EOF
--EXPECT--
We are done for now
EOF
Context dies now!
