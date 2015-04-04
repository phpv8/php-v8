--TEST--
v8\ObjectTemplate::Set() - FunctionTemplate
--SKIPIF--
<?php if (!extension_loaded("v8")) { print "skip"; } ?>
--FILE--
<?php

$isolate1 = new \v8\Isolate();
$extensions1 = [];
$global_template1 = new v8\ObjectTemplate($isolate1);

$fnc = function (\v8\FunctionCallbackInfo $info) {
   echo 'Should output "test"', PHP_EOL;
};

$test = new \v8\FunctionTemplate($isolate1, $fnc);
$test2 = new \v8\FunctionTemplate($isolate1, $fnc);


$global_template1->Set(new \v8\StringValue($isolate1, 'test'), $test);
$global_template1->Set(new \v8\StringValue($isolate1, 'test2'), $test2);

echo 'Done here for now', PHP_EOL;
?>
--EXPECT--
Done here for now
