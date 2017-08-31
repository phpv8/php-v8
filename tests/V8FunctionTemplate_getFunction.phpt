--TEST--
V8\FunctionTemplate::getFunction
--SKIPIF--
<?php if (!extension_loaded("v8")) {
    print "skip";
} ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

$isolate = new \V8\Isolate();


$print_func_tpl = new \V8\FunctionTemplate($isolate, function (\V8\FunctionCallbackInfo $info) {
    echo 'Should output Hello World string', PHP_EOL;
});


$global_template = new V8\ObjectTemplate($isolate);
$context = new \V8\Context($isolate, $global_template);
$context2 = new \V8\Context($isolate, $global_template);

$func_1 = $print_func_tpl->getFunction($context);

$helper->object_type($func_1);

$func_2 = $print_func_tpl->getFunction($context);

if ($func_1 === $func_2) {
    echo 'Function instance is the same within single context', PHP_EOL;
} else {
    echo 'Function instance is NOT the same within single context', PHP_EOL;
}
$func_3 = $print_func_tpl->getFunction($context2);

if ($func_1 === $func_3) {
    echo 'Function instance is the same between different contexts', PHP_EOL;
} else {
    echo 'Function instance is NOT the same between different contexts', PHP_EOL;
}

$context->globalObject()->set($context, new \V8\StringValue($isolate, 'print'), $func_1);


$source = 'print("Hello, world"); "Script done"';
$file_name = 'test.js';


$script = new \V8\Script($context, new \V8\StringValue($isolate, $source), new \V8\ScriptOrigin($file_name));

$helper->dump($script->run($context)->toString($context)->value());

echo 'We are done for now', PHP_EOL;

?>
EOF
--EXPECT--
V8\FunctionObject
Function instance is the same within single context
Function instance is NOT the same between different contexts
Should output Hello World string
string(11) "Script done"
We are done for now
EOF
