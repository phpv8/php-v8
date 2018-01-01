--TEST--
V8\FunctionTemplate weakness
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

class Isolate extends \V8\Isolate
{
    public function __destruct()
    {
        echo 'Isolate dies now!', PHP_EOL;
    }
}

class Context extends \V8\Context
{
    public function __destruct()
    {
        echo 'Context dies now!', PHP_EOL;
    }
}

class Script extends \V8\Script
{
    public function __destruct()
    {
        echo 'Script dies now!', PHP_EOL;
    }
}

class MyFunctionTemplate extends \V8\FunctionTemplate
{
    public function __destruct()
    {
        echo 'FunctionTemplate dies now!', PHP_EOL;
    }
}

class MyObjectTemplate extends \V8\ObjectTemplate
{
    public function __destruct()
    {
        echo 'ObjectTemplate dies now!', PHP_EOL;
    }
}

$isolate = new Isolate();


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

$print_func_tpl = new MyFunctionTemplate($isolate, new MyCallaback());

$global_template = new MyObjectTemplate($isolate);
$global_template->set(new \V8\StringValue($isolate, 'print'), $print_func_tpl);
$print_func_tpl = null;

$context = new Context($isolate, $global_template);
$global_template = null;

$source = 'print("Hello, world"); delete print; "Script done"';
$file_name = 'test.js';
try {
    $script = new Script($context, new \V8\StringValue($isolate, $source), new \V8\ScriptOrigin($file_name));

    $script->run($context)->value();
} catch (Exception $e) {
    $helper->exception_export($e);
}

$script = null;
$context = null;
$isolate = null;


echo 'We are done for now', PHP_EOL;

?>
EOF
--EXPECT--
FunctionTemplate dies now!
ObjectTemplate dies now!
Should output Hello World string
Script dies now!
Context dies now!
Isolate dies now!
MyCallaback::__destruct
We are done for now
EOF
