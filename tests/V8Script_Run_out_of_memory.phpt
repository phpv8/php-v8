--TEST--
V8\Script::Run() - out of memory example
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
<?php if (!getenv("DEV_TESTS")) print "skip"; ?>
--FILE--
<?php
/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';
require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);


$isolate = new V8\Isolate();
$extensions = [];
$global_template = new V8\ObjectTemplate($isolate);

$value = new V8\StringValue($isolate, 'TEST VALUE 111');

$global_template->Set(new \V8\StringValue($isolate, 'test'), $value);
$global_template->Set(new \V8\StringValue($isolate, 'print'), $v8_helper->getPrintFunctionTemplate($isolate));
$context = new V8\Context($isolate, $extensions, $global_template);

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

$script = new V8\Script($context, new \V8\StringValue($isolate, $source), new \V8\ScriptOrigin($file_name));
try {
    $res = $script->Run();
} catch (\Exception $e) {
    $helper->exception_export($e);
}

$v8_helper->run_checks($value);

$helper->dump($res->Value());

$helper->space();

$scalar = new V8\NumberValue($isolate, 123);
$obj    = new V8\ObjectValue($context);
$context->GlobalObject()->Set($context, new \V8\StringValue($isolate, 'scalar'), $scalar);
$context->GlobalObject()->Set($context, new \V8\StringValue($isolate, 'obj'), $obj);

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
Checks on V8\StringValue:
--------------------
V8\StringValue::IsUndefined(): false
V8\StringValue::IsNull(): false
V8\StringValue::IsTrue(): false
V8\StringValue::IsFalse(): false
V8\StringValue::IsString(): true
V8\StringValue::IsFunction(): false
V8\StringValue::IsArray(): false
V8\StringValue::IsObject(): false
V8\StringValue::IsBoolean(): false
V8\StringValue::IsNumber(): false
V8\StringValue::IsInt32(): false
V8\StringValue::IsUint32(): false
V8\StringValue::IsDate(): false
V8\StringValue::IsArgumentsObject(): false
V8\StringValue::IsBooleanObject(): false
V8\StringValue::IsNumberObject(): false
V8\StringValue::IsStringObject(): false
V8\StringValue::IsNativeError(): false
V8\StringValue::IsRegExp(): false


string(25) "TEST VALUE 111, confirmed" refcount(1)


Scalar:
-------
Expected 123 value is identical to actual value 123
Expected value is not identical to actual value


Object:
-------
Expected value is identical to actual value
