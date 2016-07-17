--TEST--
V8\NumberObject
--SKIPIF--
<?php if (!extension_loaded("v8")) { print "skip"; } ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

// Tests:

$isolate1 = new \V8\Isolate();
$extensions1 = [];
$global_template1 = new V8\ObjectTemplate($isolate1);

// TODO: fix it, this cause segfault due to FunctionTemplate object destruction and all it internal structures cleanup
//$global_template1->Set('print', $v8_helper->getPrintFunctionTemplate($isolate1), \V8\PropertyAttribute::DontDelete);
$print_func_tpl = $v8_helper->getPrintFunctionTemplate($isolate1);
$global_template1->Set(new \V8\StringValue($isolate1, 'print'), $print_func_tpl, \V8\PropertyAttribute::DontDelete);

$context1 = new V8\Context($isolate1, $extensions1, $global_template1);

$value = new V8\NumberObject($context1, 42.12);

$helper->header('Object representation');
$helper->dump($value);
$helper->space();

$helper->assert('NumberObject extends ObjectValue', $value instanceof \V8\ObjectValue);
$helper->line();

$helper->header('Getters');
$helper->method_export($value, 'ValueOf');
$helper->space();

$v8_helper->run_checks($value, 'Checkers');

$context1->GlobalObject()->Set($context1, new \V8\StringValue($isolate1, 'val'), $value);

$source1    = '
print("val: ", val, "\n");
print("typeof val: ", typeof val, "\n");

val';
$file_name1 = 'test.js';

$script1 = new V8\Script($context1, new \V8\StringValue($isolate1, $source1), new \V8\ScriptOrigin($file_name1));
$res1 = $script1->Run();
$helper->space();

$helper->header('Returned value should be the same');
$helper->value_matches_with_no_output($res1, $value);
$helper->space();

$source1    = 'new Number(11.22);';
$file_name1 = 'test.js';

$script1 = new V8\Script($context1, new \V8\StringValue($isolate1, $source1), new \V8\ScriptOrigin($file_name1));
$res1 = $script1->Run();

$v8_helper->run_checks($res1, 'Checkers on boxed from script');


?>
--EXPECT--
Object representation:
----------------------
object(V8\NumberObject)#8 (2) {
  ["isolate":"V8\Value":private]=>
  object(V8\Isolate)#3 (5) {
    ["snapshot":"V8\Isolate":private]=>
    NULL
    ["time_limit":"V8\Isolate":private]=>
    float(0)
    ["time_limit_hit":"V8\Isolate":private]=>
    bool(false)
    ["memory_limit":"V8\Isolate":private]=>
    int(0)
    ["memory_limit_hit":"V8\Isolate":private]=>
    bool(false)
  }
  ["context":"V8\ObjectValue":private]=>
  object(V8\Context)#7 (4) {
    ["isolate":"V8\Context":private]=>
    object(V8\Isolate)#3 (5) {
      ["snapshot":"V8\Isolate":private]=>
      NULL
      ["time_limit":"V8\Isolate":private]=>
      float(0)
      ["time_limit_hit":"V8\Isolate":private]=>
      bool(false)
      ["memory_limit":"V8\Isolate":private]=>
      int(0)
      ["memory_limit_hit":"V8\Isolate":private]=>
      bool(false)
    }
    ["extensions":"V8\Context":private]=>
    array(0) {
    }
    ["global_template":"V8\Context":private]=>
    object(V8\ObjectTemplate)#4 (1) {
      ["isolate":"V8\Template":private]=>
      object(V8\Isolate)#3 (5) {
        ["snapshot":"V8\Isolate":private]=>
        NULL
        ["time_limit":"V8\Isolate":private]=>
        float(0)
        ["time_limit_hit":"V8\Isolate":private]=>
        bool(false)
        ["memory_limit":"V8\Isolate":private]=>
        int(0)
        ["memory_limit_hit":"V8\Isolate":private]=>
        bool(false)
      }
    }
    ["global_object":"V8\Context":private]=>
    NULL
  }
}


NumberObject extends ObjectValue: ok

Getters:
--------
V8\NumberObject->ValueOf(): float(42.12)


Checkers:
---------
V8\NumberObject(V8\ObjectValue)->IsCallable(): bool(false)
V8\NumberObject(V8\Value)->IsUndefined(): bool(false)
V8\NumberObject(V8\Value)->IsNull(): bool(false)
V8\NumberObject(V8\Value)->IsTrue(): bool(false)
V8\NumberObject(V8\Value)->IsFalse(): bool(false)
V8\NumberObject(V8\Value)->IsName(): bool(false)
V8\NumberObject(V8\Value)->IsString(): bool(false)
V8\NumberObject(V8\Value)->IsSymbol(): bool(false)
V8\NumberObject(V8\Value)->IsFunction(): bool(false)
V8\NumberObject(V8\Value)->IsArray(): bool(false)
V8\NumberObject(V8\Value)->IsObject(): bool(true)
V8\NumberObject(V8\Value)->IsBoolean(): bool(false)
V8\NumberObject(V8\Value)->IsNumber(): bool(false)
V8\NumberObject(V8\Value)->IsInt32(): bool(false)
V8\NumberObject(V8\Value)->IsUint32(): bool(false)
V8\NumberObject(V8\Value)->IsDate(): bool(false)
V8\NumberObject(V8\Value)->IsArgumentsObject(): bool(false)
V8\NumberObject(V8\Value)->IsBooleanObject(): bool(false)
V8\NumberObject(V8\Value)->IsNumberObject(): bool(true)
V8\NumberObject(V8\Value)->IsStringObject(): bool(false)
V8\NumberObject(V8\Value)->IsSymbolObject(): bool(false)
V8\NumberObject(V8\Value)->IsNativeError(): bool(false)
V8\NumberObject(V8\Value)->IsRegExp(): bool(false)


val: 42.12
typeof val: object


Returned value should be the same:
----------------------------------
Expected value is identical to actual value


Checkers on boxed from script:
------------------------------
V8\NumberObject(V8\ObjectValue)->IsCallable(): bool(false)
V8\NumberObject(V8\Value)->IsUndefined(): bool(false)
V8\NumberObject(V8\Value)->IsNull(): bool(false)
V8\NumberObject(V8\Value)->IsTrue(): bool(false)
V8\NumberObject(V8\Value)->IsFalse(): bool(false)
V8\NumberObject(V8\Value)->IsName(): bool(false)
V8\NumberObject(V8\Value)->IsString(): bool(false)
V8\NumberObject(V8\Value)->IsSymbol(): bool(false)
V8\NumberObject(V8\Value)->IsFunction(): bool(false)
V8\NumberObject(V8\Value)->IsArray(): bool(false)
V8\NumberObject(V8\Value)->IsObject(): bool(true)
V8\NumberObject(V8\Value)->IsBoolean(): bool(false)
V8\NumberObject(V8\Value)->IsNumber(): bool(false)
V8\NumberObject(V8\Value)->IsInt32(): bool(false)
V8\NumberObject(V8\Value)->IsUint32(): bool(false)
V8\NumberObject(V8\Value)->IsDate(): bool(false)
V8\NumberObject(V8\Value)->IsArgumentsObject(): bool(false)
V8\NumberObject(V8\Value)->IsBooleanObject(): bool(false)
V8\NumberObject(V8\Value)->IsNumberObject(): bool(true)
V8\NumberObject(V8\Value)->IsStringObject(): bool(false)
V8\NumberObject(V8\Value)->IsSymbolObject(): bool(false)
V8\NumberObject(V8\Value)->IsNativeError(): bool(false)
V8\NumberObject(V8\Value)->IsRegExp(): bool(false)
