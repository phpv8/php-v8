--TEST--
v8\FunctionTemplate weakness
--SKIPIF--
<?php if (!extension_loaded("v8")) {
    print "skip";
} ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

class Isolate extends \v8\Isolate
{
    public function __destruct()
    {
        echo 'Isolate dies now!', PHP_EOL;
    }
}

class Context extends \v8\Context
{
    public function __destruct()
    {
        echo 'Context dies now!', PHP_EOL;
    }
}

class Script extends \v8\Script
{
    public function __destruct()
    {
        echo 'Script dies now!', PHP_EOL;
    }
}

class MyFunctionTemplate extends \v8\FunctionTemplate
{
    public function __destruct()
    {
        echo 'FunctionTemplate dies now!', PHP_EOL;
    }
}

class MyObjectTemplate extends \v8\ObjectTemplate
{
    public function __destruct()
    {
        echo 'ObjectTemplate dies now!', PHP_EOL;
    }
}

$isolate1 = new Isolate();
$extensions1 = [];


class MyCallaback
{
    public function __destruct()
    {
        echo __METHOD__, PHP_EOL;
    }

    public function __invoke()
    {
        echo 'Should output Hello World string', PHP_EOL;
    }
}

$print_func_tpl = new MyFunctionTemplate($isolate1, new MyCallaback());

$global_template1 = new MyObjectTemplate($isolate1);
$global_template1->Set(new \v8\StringValue($isolate1, 'print'), $print_func_tpl);
$print_func_tpl = null;

$context1 = new Context($isolate1, $extensions1, $global_template1);
$global_template1 = null;

$source1 = 'print("Hello, world\n"); delete print; "Script done"';
$file_name1 = 'test.js';
try {
    $script1 = new Script($context1, new \v8\StringValue($isolate1, $source1), new \v8\ScriptOrigin($file_name1));

    $script1->Run()->Value();
} catch (Exception $e) {
    $helper->exception_export($e);
}

$script1 = null;
$context1 = null;
$isolate1 = null;


echo 'We are done for now', PHP_EOL;

?>
EOF
--EXPECT--
FunctionTemplate dies now!
Should output Hello World string
Script dies now!
Context dies now!
ObjectTemplate dies now!
Isolate dies now!
MyCallaback::__destruct
We are done for now
EOF
