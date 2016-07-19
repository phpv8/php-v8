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

$isolate1 = new v8Tests\TrackingDtors\Isolate();
$extensions1 = [];

$global_template1 = new V8\ObjectTemplate($isolate1);

//$global_template1->Set(new \V8\StringValue($isolate1, 'print'), $v8_helper->getPrintFunctionTemplate($isolate1), \V8\PropertyAttribute::DontDelete);

$context1 = new V8\Context($isolate1, $extensions1, $global_template1);

$scalar = new \V8\StringValue($isolate1, "test");
$object = new \V8\ObjectValue($context1);


$method = null;
$args = [];

$func = new v8Tests\TrackingDtors\FunctionObject($context1, function (\V8\FunctionCallbackInfo $info) use ($helper, $scalar, $object, $isolate1, $context1, &$method, &$args) {

    $retval = $info->GetReturnValue();

    if (!$method) {
        echo 'Function called', PHP_EOL;

        $helper->assert('Return value holds original isolate object', $retval->GetIsolate(), $isolate1);
        $helper->assert('Return value holds original isolate object', $retval->GetContext(), $context1);
    }

    if ($method) {
        $retval->{$method}(...$args);
    }
});

$context1->GlobalObject()->Set($context1, new \V8\StringValue($isolate1, 'test'), $func);

$source1 = 'test(); "Script done";';
$file_name1 = 'test.js';


$script1 = new V8\Script($context1, new \V8\StringValue($isolate1, $source1), new \V8\ScriptOrigin($file_name1));

$helper->dump($script1->Run($context1)->ToString($context1)->Value());

$helper->space();

$method = 'SetUndefined';
$args = [];
$res = $v8_helper->CompileRun($context1, "test()");
$helper->assert('Returns undefined', $res->IsUndefined());

$method = 'SetNull';
$args = [];
$res = $v8_helper->CompileRun($context1, "test()");
$helper->assert('Returns null', $res->IsNull());

$method = 'SetBool';
$args = [true];
$res = $v8_helper->CompileRun($context1, "test()");
$helper->assert('Returns boolean', $res->IsBoolean() && $res->IsTrue());

$method = 'SetInteger';
$args = [42];
$res = $v8_helper->CompileRun($context1, "test()");
$helper->assert('Returns integer', $res->IsNumber() && $res->IsInt32());

$method = 'SetFloat';
$args = [PHP_INT_MAX + 0.22];
$res = $v8_helper->CompileRun($context1, "test()");
$helper->assert('Returns float', $res->IsNumber() && !$res->IsInt32() && !$res->IsUint32());
$helper->pretty_dump('Returns float', $res->Value());

$helper->line();


echo 'We are done for now', PHP_EOL;

?>
--EXPECT--
Function called
Return value holds original isolate object: ok
Return value holds original isolate object: ok
string(11) "Script done"


Returns undefined: ok
Returns null: ok
Returns boolean: ok
Returns integer: ok
Returns float: ok
Returns float: float(9.2233720368548E+18)

We are done for now
FunctionObject dies now!
Isolate dies now!
