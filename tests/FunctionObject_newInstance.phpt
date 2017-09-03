--TEST--
V8\FunctionObject::newInstance()
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--ENV--
HOME=/tmp/we-need-home-env-var-set-to-load-valgrindrc
--FILE--
<?php
/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);


// Tests:

$isolate = new \V8\Isolate();
$context = new \V8\Context($isolate);

$global=  $context->globalObject();


$tpl = new \V8\FunctionTemplate($isolate, function (\V8\FunctionCallbackInfo $args) {
    echo 'called as ', $args->isConstructCall() ? 'constructor' : 'function', ' ';
    echo 'with ', count($args->arguments()), ' arguments';

    echo PHP_EOL;
});


$tpl->getFunction($context)->newInstance($context);
$tpl->getFunction($context)->newInstance($context, [new \V8\StringValue($isolate, 'argument1')]);
$tpl->getFunction($context)->newInstance($context, [new \V8\ObjectValue($context)]);


?>
--EXPECT--
called as constructor with 0 arguments
called as constructor with 1 arguments
called as constructor with 1 arguments
