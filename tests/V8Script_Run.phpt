--TEST--
V8\Script::Run
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
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
$context = new V8\Context($isolate, $extensions, $global_template);


$source    = 'test; test = test + ", confirmed"';
$file_name = 'test.js';

$script = new V8\Script($context, new \V8\StringValue($isolate, $source), new \V8\ScriptOrigin($file_name));
$res = $script->Run();

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
-------------------------
V8\StringValue->IsOneByte(): bool(true)
V8\StringValue(V8\Value)->IsUndefined(): bool(false)
V8\StringValue(V8\Value)->IsNull(): bool(false)
V8\StringValue(V8\Value)->IsTrue(): bool(false)
V8\StringValue(V8\Value)->IsFalse(): bool(false)
V8\StringValue(V8\Value)->IsName(): bool(true)
V8\StringValue(V8\Value)->IsString(): bool(true)
V8\StringValue(V8\Value)->IsSymbol(): bool(false)
V8\StringValue(V8\Value)->IsFunction(): bool(false)
V8\StringValue(V8\Value)->IsArray(): bool(false)
V8\StringValue(V8\Value)->IsObject(): bool(false)
V8\StringValue(V8\Value)->IsBoolean(): bool(false)
V8\StringValue(V8\Value)->IsNumber(): bool(false)
V8\StringValue(V8\Value)->IsInt32(): bool(false)
V8\StringValue(V8\Value)->IsUint32(): bool(false)
V8\StringValue(V8\Value)->IsDate(): bool(false)
V8\StringValue(V8\Value)->IsArgumentsObject(): bool(false)
V8\StringValue(V8\Value)->IsBooleanObject(): bool(false)
V8\StringValue(V8\Value)->IsNumberObject(): bool(false)
V8\StringValue(V8\Value)->IsStringObject(): bool(false)
V8\StringValue(V8\Value)->IsSymbolObject(): bool(false)
V8\StringValue(V8\Value)->IsNativeError(): bool(false)
V8\StringValue(V8\Value)->IsRegExp(): bool(false)


string(25) "TEST VALUE 111, confirmed"


Scalar:
-------
Expected 123.0 value is identical to actual value 123.0
Expected value is not identical to actual value


Object:
-------
Expected value is identical to actual value
