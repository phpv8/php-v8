--TEST--
v8\FunctionTemplate::GetFunction
--SKIPIF--
<?php if (!extension_loaded("v8")) {
    print "skip";
} ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

$isolate1 = new \v8\Isolate();
$extensions1 = [];


$print_func_tpl = new \v8\FunctionTemplate($isolate1, function (\v8\FunctionCallbackInfo $info) {
    echo 'Should output Hello World string', PHP_EOL;
});


$global_template1 = new v8\ObjectTemplate($isolate1);
$context1 = new \v8\Context($isolate1, $extensions1, $global_template1);
$context2 = new \v8\Context($isolate1, $extensions1, $global_template1);

$func_1 = $print_func_tpl->GetFunction($context1);

$helper->object_type($func_1);

$func_2 = $print_func_tpl->GetFunction($context1);

if ($func_1 === $func_2) {
    echo 'Function instance is the same within single context', PHP_EOL;
} else {
    echo 'Function instance is NOT the same within single context', PHP_EOL;
}
$func_3 = $print_func_tpl->GetFunction($context2);

if ($func_1 === $func_3) {
    echo 'Function instance is the same between different contexts', PHP_EOL;
} else {
    echo 'Function instance is NOT the same between different contexts', PHP_EOL;
}

$context1->GlobalObject()->Set($context1, new \v8\StringValue($isolate1, 'print'), $func_1);


$source1 = 'print("Hello, world\n"); "Script done"';
$file_name1 = 'test.js';


$script1 = new \v8\Script($context1, new \v8\StringValue($isolate1, $source1), new \v8\ScriptOrigin($file_name1));

debug_zval_dump($script1->Run()->ToString($context1)->Value());

echo 'We are done for now', PHP_EOL;

?>
EOF
--EXPECT--
v8\FunctionObject
Function instance is the same within single context
Function instance is NOT the same between different contexts
Should output Hello World string
string(11) "Script done" refcount(1)
We are done for now
EOF
