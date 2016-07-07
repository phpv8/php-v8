--TEST--
v8\Script::Run() - out of memory example
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
<?php if (!getenv("DEV_TESTS")) print "skip"; ?>
--FILE--
<?php
/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';
require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);


$isolate = new v8\Isolate();
$extensions = [];
$global_template = new v8\ObjectTemplate($isolate);

$value = new v8\StringValue($isolate, 'TEST VALUE 111');

$global_template->Set(new \v8\StringValue($isolate, 'test'), $value);
$global_template->Set(new \v8\StringValue($isolate, 'print'), $v8_helper->getPrintFunctionTemplate($isolate));
$context = new v8\Context($isolate, $extensions, $global_template);

// This causes segfault
$source = '
x = \'x\';
var multiply = 27;

while (multiply-- > 0){
 x = ""+x+x;
 print(x.length, "\n");
}

var arr = [];

print("\n\n");

while (1) {
     arr.push(x);
     //if (!(arr.length % 10000)) {
     //   print(arr.length, "\n");
     //}
}
';

$file_name = 'test.js';

$script = new v8\Script($context, new \v8\StringValue($isolate, $source), new \v8\ScriptOrigin($file_name));
try {
    $res = $script->Run();
} catch (\Exception $e) {
    $helper->exception_export($e);
}

$v8_helper->run_checks($value);

$helper->dump($res->Value());

$helper->space();

$scalar = new v8\NumberValue($isolate, 123);
$obj    = new v8\ObjectValue($context);
$context->GlobalObject()->Set($context, new \v8\StringValue($isolate, 'scalar'), $scalar);
$context->GlobalObject()->Set($context, new \v8\StringValue($isolate, 'obj'), $obj);

$res = $v8_helper->CompileTryRun($context, 'scalar');

$helper->header('Scalar');
$helper->value_matches($res->Value(), $scalar->Value());
$helper->value_matches_with_no_output($res, $scalar);

$helper->space();


$res = $v8_helper->CompileTryRun($context, 'obj');

$helper->header('Object');
$helper->value_matches_with_no_output($res, $obj);

?>
--EXPECT--
Checks on v8\StringValue:
--------------------
v8\StringValue::IsUndefined(): false
v8\StringValue::IsNull(): false
v8\StringValue::IsTrue(): false
v8\StringValue::IsFalse(): false
v8\StringValue::IsString(): true
v8\StringValue::IsFunction(): false
v8\StringValue::IsArray(): false
v8\StringValue::IsObject(): false
v8\StringValue::IsBoolean(): false
v8\StringValue::IsNumber(): false
v8\StringValue::IsInt32(): false
v8\StringValue::IsUint32(): false
v8\StringValue::IsDate(): false
v8\StringValue::IsArgumentsObject(): false
v8\StringValue::IsBooleanObject(): false
v8\StringValue::IsNumberObject(): false
v8\StringValue::IsStringObject(): false
v8\StringValue::IsNativeError(): false
v8\StringValue::IsRegExp(): false


string(25) "TEST VALUE 111, confirmed" refcount(1)


Scalar:
-------
Expected 123 value is identical to actual value 123
Expected value is not identical to actual value


Object:
-------
Expected value is identical to actual value
