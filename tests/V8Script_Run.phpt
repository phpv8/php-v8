--TEST--
v8\Script::Run
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
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
$context = new v8\Context($isolate, $extensions, $global_template);


$source    = 'test; test = test + ", confirmed"';
$file_name = 'test.js';

$script = new v8\Script($context, new \v8\StringValue($isolate, $source), new \v8\ScriptOrigin($file_name));
$res = $script->Run();

$v8_helper->run_checks($value);

debug_zval_dump($res->Value());

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
-------------------------
v8\StringValue->IsOneByte(): bool(true)
v8\StringValue(v8\Value)->IsUndefined(): bool(false)
v8\StringValue(v8\Value)->IsNull(): bool(false)
v8\StringValue(v8\Value)->IsTrue(): bool(false)
v8\StringValue(v8\Value)->IsFalse(): bool(false)
v8\StringValue(v8\Value)->IsName(): bool(true)
v8\StringValue(v8\Value)->IsString(): bool(true)
v8\StringValue(v8\Value)->IsSymbol(): bool(false)
v8\StringValue(v8\Value)->IsFunction(): bool(false)
v8\StringValue(v8\Value)->IsArray(): bool(false)
v8\StringValue(v8\Value)->IsObject(): bool(false)
v8\StringValue(v8\Value)->IsBoolean(): bool(false)
v8\StringValue(v8\Value)->IsNumber(): bool(false)
v8\StringValue(v8\Value)->IsInt32(): bool(false)
v8\StringValue(v8\Value)->IsUint32(): bool(false)
v8\StringValue(v8\Value)->IsDate(): bool(false)
v8\StringValue(v8\Value)->IsArgumentsObject(): bool(false)
v8\StringValue(v8\Value)->IsBooleanObject(): bool(false)
v8\StringValue(v8\Value)->IsNumberObject(): bool(false)
v8\StringValue(v8\Value)->IsStringObject(): bool(false)
v8\StringValue(v8\Value)->IsSymbolObject(): bool(false)
v8\StringValue(v8\Value)->IsNativeError(): bool(false)
v8\StringValue(v8\Value)->IsRegExp(): bool(false)


string(25) "TEST VALUE 111, confirmed" refcount(1)


Scalar:
-------
Expected 123.0 value is identical to actual value 123.0
Expected value is not identical to actual value


Object:
-------
Expected value is identical to actual value
