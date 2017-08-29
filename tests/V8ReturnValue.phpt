--TEST--
V8\ReturnValue
--SKIPIF--
<?php if (!extension_loaded("v8")) {
    print "skip";
} ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

require '.tracking_dtors.php';

$isolate = new v8Tests\TrackingDtors\Isolate();

$global_template = new V8\ObjectTemplate($isolate);

// $global_template->set(new \V8\StringValue($isolate, 'print'), $v8_helper->getPrintFunctionTemplate($isolate), \V8\PropertyAttribute::DontDelete);

$context = new V8\Context($isolate, $global_template);

$scalar = new \V8\StringValue($isolate, "test");
$object = new \V8\ObjectValue($context);


$method = null;
$checker = null;
$args = [];

$func = new v8Tests\TrackingDtors\FunctionObject($context, function (\V8\FunctionCallbackInfo $info) use ($helper, $scalar, $object, $isolate, $context, &$method, &$checker, &$args) {

    $retval = $info->getReturnValue();

    if (!$method) {
        echo 'Function called', PHP_EOL;

        $helper->assert('Return value holds original isolate object', $retval->getIsolate(), $isolate);
        $helper->assert('Return value holds original context object', $retval->getContext(), $context);
    }

    $helper->assert('Return value holds no value', $retval->get()->isUndefined());
    if ($method) {
        $retval->{$method}(...$args);
        if ($checker) {
            $helper->assert('Return value was set and holds proper value', $retval->get()->{$checker}());
        }
    }
});

$context->globalObject()->set($context, new \V8\StringValue($isolate, 'test'), $func);

$source = 'test(); "Script done";';
$file_name = 'test.js';


$script = new V8\Script($context, new \V8\StringValue($isolate, $source), new \V8\ScriptOrigin($file_name));

$helper->dump($script->run($context)->toString($context)->value());

$helper->space();

$method = 'SetUndefined';
$checker = 'IsUndefined';
$args = [];
$res = $v8_helper->CompileRun($context, "test()");
$helper->assert('Returns undefined', $res->isUndefined());

$method = 'SetNull';
$checker = 'IsNull';
$args = [];
$res = $v8_helper->CompileRun($context, "test()");
$helper->assert('Returns null', $res->isNull());

$method = 'SetBool';
$checker = 'IsBoolean';
$args = [true];
$res = $v8_helper->CompileRun($context, "test()");
$helper->assert('Returns boolean', $res->isBoolean() && $res->isTrue());

$method = 'SetInteger';
$checker = 'IsInt32';
$args = [42];
$res = $v8_helper->CompileRun($context, "test()");
$helper->assert('Returns integer', $res->isNumber() && $res->isInt32());

$method = 'SetFloat';
$checker = 'IsNumber';
$args = [PHP_INT_MAX + 0.22];
$res = $v8_helper->CompileRun($context, "test()");
$helper->assert('Returns float', $res->isNumber() && !$res->isInt32() && !$res->isUint32());
$helper->pretty_dump('Returns float', $res->value());

$helper->line();



echo 'We are done for now', PHP_EOL;

?>
--EXPECT--
Function called
Return value holds original isolate object: ok
Return value holds original context object: ok
Return value holds no value: ok
string(11) "Script done"


Return value holds no value: ok
Return value was set and holds proper value: ok
Returns undefined: ok
Return value holds no value: ok
Return value was set and holds proper value: ok
Returns null: ok
Return value holds no value: ok
Return value was set and holds proper value: ok
Returns boolean: ok
Return value holds no value: ok
Return value was set and holds proper value: ok
Returns integer: ok
Return value holds no value: ok
Return value was set and holds proper value: ok
Returns float: ok
Returns float: float(9.2233720368548E+18)

We are done for now
FunctionObject dies now!
Isolate dies now!
