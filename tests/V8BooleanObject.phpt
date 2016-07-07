--TEST--
v8\BooleanObject
--SKIPIF--
<?php if (!extension_loaded("v8")) { print "skip"; } ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

$isolate1 = new \v8\Isolate();
$extensions1 = [];
$global_template1 = new v8\ObjectTemplate($isolate1);

// TODO: fix it, this cause segfault due to FunctionTemplate object destruction and all it internal structures cleanup
//$global_template1->Set('print', $v8_helper->getPrintFunctionTemplate($isolate1), \v8\PropertyAttribute::DontDelete);
$print_func_tpl = $v8_helper->getPrintFunctionTemplate($isolate1);
$global_template1->Set(new \v8\StringValue($isolate1, 'print'), $print_func_tpl, \v8\PropertyAttribute::DontDelete);

$context1 = new v8\Context($isolate1, $extensions1, $global_template1);

$value = new v8\BooleanObject($context1, true);

$helper->header('Object representation');
$helper->dump($value);
$helper->space();

$helper->assert('BooleanObject extends ObjectValue', $value instanceof \v8\ObjectValue);
$helper->line();


$helper->header('Getters');
$helper->method_export($value, 'ValueOf');
$helper->space();

$v8_helper->run_checks($value, 'Checkers');

$context1->GlobalObject()->Set($context1, new \v8\StringValue($isolate1, 'val'), $value);

$source1    = '
print("val: ", val, "\n");
print("typeof val: ", typeof val, "\n");

new Boolean(false);
';
$file_name1 = 'test.js';

$script1 = new v8\Script($context1, new \v8\StringValue($isolate1, $source1), new \v8\ScriptOrigin($file_name1));
$res1 = $script1->Run();
$helper->space();

$v8_helper->run_checks($res1, 'Checkers on boxed from script');

?>
--EXPECT--
Object representation:
----------------------
object(v8\BooleanObject)#8 (2) {
  ["isolate":"v8\Value":private]=>
  object(v8\Isolate)#3 (5) {
    ["snapshot":"v8\Isolate":private]=>
    NULL
    ["time_limit":"v8\Isolate":private]=>
    float(0)
    ["time_limit_hit":"v8\Isolate":private]=>
    bool(false)
    ["memory_limit":"v8\Isolate":private]=>
    int(0)
    ["memory_limit_hit":"v8\Isolate":private]=>
    bool(false)
  }
  ["context":"v8\ObjectValue":private]=>
  object(v8\Context)#7 (4) {
    ["isolate":"v8\Context":private]=>
    object(v8\Isolate)#3 (5) {
      ["snapshot":"v8\Isolate":private]=>
      NULL
      ["time_limit":"v8\Isolate":private]=>
      float(0)
      ["time_limit_hit":"v8\Isolate":private]=>
      bool(false)
      ["memory_limit":"v8\Isolate":private]=>
      int(0)
      ["memory_limit_hit":"v8\Isolate":private]=>
      bool(false)
    }
    ["extensions":"v8\Context":private]=>
    array(0) {
    }
    ["global_template":"v8\Context":private]=>
    object(v8\ObjectTemplate)#4 (1) {
      ["isolate":"v8\Template":private]=>
      object(v8\Isolate)#3 (5) {
        ["snapshot":"v8\Isolate":private]=>
        NULL
        ["time_limit":"v8\Isolate":private]=>
        float(0)
        ["time_limit_hit":"v8\Isolate":private]=>
        bool(false)
        ["memory_limit":"v8\Isolate":private]=>
        int(0)
        ["memory_limit_hit":"v8\Isolate":private]=>
        bool(false)
      }
    }
    ["global_object":"v8\Context":private]=>
    NULL
  }
}


BooleanObject extends ObjectValue: ok

Getters:
--------
v8\BooleanObject->ValueOf(): bool(true)


Checkers:
---------
v8\BooleanObject(v8\ObjectValue)->IsCallable(): bool(false)
v8\BooleanObject(v8\Value)->IsUndefined(): bool(false)
v8\BooleanObject(v8\Value)->IsNull(): bool(false)
v8\BooleanObject(v8\Value)->IsTrue(): bool(false)
v8\BooleanObject(v8\Value)->IsFalse(): bool(false)
v8\BooleanObject(v8\Value)->IsName(): bool(false)
v8\BooleanObject(v8\Value)->IsString(): bool(false)
v8\BooleanObject(v8\Value)->IsSymbol(): bool(false)
v8\BooleanObject(v8\Value)->IsFunction(): bool(false)
v8\BooleanObject(v8\Value)->IsArray(): bool(false)
v8\BooleanObject(v8\Value)->IsObject(): bool(true)
v8\BooleanObject(v8\Value)->IsBoolean(): bool(false)
v8\BooleanObject(v8\Value)->IsNumber(): bool(false)
v8\BooleanObject(v8\Value)->IsInt32(): bool(false)
v8\BooleanObject(v8\Value)->IsUint32(): bool(false)
v8\BooleanObject(v8\Value)->IsDate(): bool(false)
v8\BooleanObject(v8\Value)->IsArgumentsObject(): bool(false)
v8\BooleanObject(v8\Value)->IsBooleanObject(): bool(true)
v8\BooleanObject(v8\Value)->IsNumberObject(): bool(false)
v8\BooleanObject(v8\Value)->IsStringObject(): bool(false)
v8\BooleanObject(v8\Value)->IsSymbolObject(): bool(false)
v8\BooleanObject(v8\Value)->IsNativeError(): bool(false)
v8\BooleanObject(v8\Value)->IsRegExp(): bool(false)


val: true
typeof val: object


Checkers on boxed from script:
------------------------------
v8\BooleanObject(v8\ObjectValue)->IsCallable(): bool(false)
v8\BooleanObject(v8\Value)->IsUndefined(): bool(false)
v8\BooleanObject(v8\Value)->IsNull(): bool(false)
v8\BooleanObject(v8\Value)->IsTrue(): bool(false)
v8\BooleanObject(v8\Value)->IsFalse(): bool(false)
v8\BooleanObject(v8\Value)->IsName(): bool(false)
v8\BooleanObject(v8\Value)->IsString(): bool(false)
v8\BooleanObject(v8\Value)->IsSymbol(): bool(false)
v8\BooleanObject(v8\Value)->IsFunction(): bool(false)
v8\BooleanObject(v8\Value)->IsArray(): bool(false)
v8\BooleanObject(v8\Value)->IsObject(): bool(true)
v8\BooleanObject(v8\Value)->IsBoolean(): bool(false)
v8\BooleanObject(v8\Value)->IsNumber(): bool(false)
v8\BooleanObject(v8\Value)->IsInt32(): bool(false)
v8\BooleanObject(v8\Value)->IsUint32(): bool(false)
v8\BooleanObject(v8\Value)->IsDate(): bool(false)
v8\BooleanObject(v8\Value)->IsArgumentsObject(): bool(false)
v8\BooleanObject(v8\Value)->IsBooleanObject(): bool(true)
v8\BooleanObject(v8\Value)->IsNumberObject(): bool(false)
v8\BooleanObject(v8\Value)->IsStringObject(): bool(false)
v8\BooleanObject(v8\Value)->IsSymbolObject(): bool(false)
v8\BooleanObject(v8\Value)->IsNativeError(): bool(false)
v8\BooleanObject(v8\Value)->IsRegExp(): bool(false)
