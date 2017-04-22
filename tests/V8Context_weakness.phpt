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


$isolate = new \V8\Isolate();

$global_template = new V8\ObjectTemplate($isolate);

$source = 'var obj = {}; obj';
$file_name = 'test.js';

$script = new \V8\Script(
    new \v8Tests\TrackingDtors\Context($isolate, $global_template),
    new \V8\StringValue($isolate, $source),
    new \V8\ScriptOrigin($file_name)
);


$obj = $script->Run($script->GetContext())->ToObject($script->GetContext()); // contest should be stored in object

$script = null;

echo 'We are done for now', PHP_EOL;
?>
EOF
--EXPECT--
We are done for now
EOF
Context dies now!
