--TEST--
V8\ArrayObject::length()
--SKIPIF--
<?php if (!extension_loaded("v8")) { print "skip"; } ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';
require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

$isolate = new \V8\Isolate();
$context = new V8\Context($isolate);

$res = $v8_helper->CompileRun($context, '[1,2,3]');

echo $res->length(), PHP_EOL;


$arr = new \V8\ArrayObject($context, 5);
echo $arr->length(), PHP_EOL;

for ($i =0; $i < 7; $i++) {
    $arr->set($context, new \V8\Uint32Value($isolate, $i), new \V8\StringValue($isolate, 'test-'.$i));
}

echo $arr->length(), PHP_EOL;

?>
--EXPECT--
3
5
7
