--TEST--
V8\ObjectTemplate::Set() - FunctionTemplate
--SKIPIF--
<?php if (!extension_loaded("v8")) { print "skip"; } ?>
--FILE--
<?php

$isolate = new \V8\Isolate();
$global_template = new V8\ObjectTemplate($isolate);

$fnc = function (\V8\FunctionCallbackInfo $info) {
   echo 'Should output "test"', PHP_EOL;
};

$test = new \V8\FunctionTemplate($isolate, $fnc);
$test2 = new \V8\FunctionTemplate($isolate, $fnc);


$global_template->Set(new \V8\StringValue($isolate, 'test'), $test);
$global_template->Set(new \V8\StringValue($isolate, 'test2'), $test2);

echo 'Done here for now', PHP_EOL;
?>
--EXPECT--
Done here for now
