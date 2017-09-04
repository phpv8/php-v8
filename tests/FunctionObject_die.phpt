--TEST--
V8\FunctionObject - test die() during calling
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--ENV--
HOME=/tmp/we-need-home-env-var-set-to-load-valgrindrc
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.tracking_dtors.php';

$isolate = new v8Tests\TrackingDtors\Isolate();
$global_template = new V8\ObjectTemplate($isolate);
$context = new V8\Context($isolate, $global_template);


$func = new v8Tests\TrackingDtors\FunctionObject($context, function (\V8\FunctionCallbackInfo $info) {
    echo 'going to die...', PHP_EOL;
    die();
});


$context->globalObject()->set($context, new \V8\StringValue($isolate, 'test'), $func);

$source = 'test(); "Script done"';
$file_name = 'test.js';


$script = new V8\Script($context, new \V8\StringValue($isolate, $source), new \V8\ScriptOrigin($file_name));

$res = $script->run($context)->toString($context)->value();
$helper->pretty_dump('Script result', $res);

echo 'We are done for now', PHP_EOL;

?>
--EXPECT--
going to die...
FunctionObject dies now!
Isolate dies now!
