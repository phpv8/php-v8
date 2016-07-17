--TEST--
V8\ArrayObject::Length
--SKIPIF--
<?php if (!extension_loaded("v8")) { print "skip"; } ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

$isolate1 = new \V8\Isolate();
$extensions1 = [];
$global_template1 = new V8\ObjectTemplate($isolate1);

$context1 = new V8\Context($isolate1, $extensions1, $global_template1);

$source1    = '
[1,2,3]
';
$file_name1 = 'test.js';

$script1 = new V8\Script($context1, new \V8\StringValue($isolate1, $source1), new \V8\ScriptOrigin($file_name1));
$res1 = $script1->Run();

echo $res1->Length(), PHP_EOL;


$arr = new \V8\ArrayObject($context1, 5);
echo $arr->Length(), PHP_EOL;

for ($i =0; $i < 7; $i++) {
    $arr->SetIndex($context1, $i, new \V8\StringValue($isolate1, 'test-'.$i));
}

echo $arr->Length(), PHP_EOL;

?>
--EXPECT--
3
5
7
