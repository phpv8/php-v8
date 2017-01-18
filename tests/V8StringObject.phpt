--TEST--
V8\StringObject
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

$global_template1->Set(new \V8\StringValue($isolate1, 'print'), $v8_helper->getPrintFunctionTemplate($isolate1), \V8\PropertyAttribute::DontDelete);

$context1 = new V8\Context($isolate1, $extensions1, $global_template1);

$value = new V8\StringObject($context1, new \V8\StringValue($isolate1, 'test string'));

$helper->header('Object representation');
$helper->dump($value);
$helper->space();

$helper->assert('StringObject extends ObjectValue', $value instanceof \V8\ObjectValue);
$helper->line();

$helper->header('Getters');
$helper->method_export($value, 'ValueOf');
$helper->method_export($value->ValueOf(), 'Value');
$helper->space();

$v8_helper->run_checks($value, 'Checkers');

$context1->GlobalObject()->Set($context1, new \V8\StringValue($isolate1, 'val'), $value);

$source1    = '
print("val: ", val, "\n");
print("typeof val: ", typeof val, "\n");

val
';
$file_name1 = 'test.js';

$script1 = new V8\Script($context1, new \V8\StringValue($isolate1, $source1), new \V8\ScriptOrigin($file_name1));
$res1 = $script1->Run($context1);
$helper->space();

$helper->header('Returned value should be the same');
$helper->value_matches_with_no_output($res1, $value);
$helper->space();

$source1    = 'new String("boxed test string from script");';
$file_name1 = 'test.js';

$script1 = new V8\Script($context1, new \V8\StringValue($isolate1, $source1), new \V8\ScriptOrigin($file_name1));
$res1 = $script1->Run($context1);

$v8_helper->run_checks($res1, 'Checkers on boxed from script')

?>
--EXPECT--
Object representation:
----------------------
object(V8\StringObject)#6 (2) {
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
  object(V8\Context)#5 (4) {
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


StringObject extends ObjectValue: ok

Getters:
--------
V8\StringObject->ValueOf():
    object(V8\StringValue)#99 (1) {
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
    }
V8\StringValue->Value(): string(11) "test string"


Checkers:
---------
V8\StringObject(V8\Value)->TypeOf(): V8\StringValue->Value(): string(6) "object"

V8\StringObject(V8\ObjectValue)->IsCallable(): bool(false)
V8\StringObject(V8\ObjectValue)->IsConstructor(): bool(false)
V8\StringObject(V8\Value)->IsUndefined(): bool(false)
V8\StringObject(V8\Value)->IsNull(): bool(false)
V8\StringObject(V8\Value)->IsNullOrUndefined(): bool(false)
V8\StringObject(V8\Value)->IsTrue(): bool(false)
V8\StringObject(V8\Value)->IsFalse(): bool(false)
V8\StringObject(V8\Value)->IsName(): bool(false)
V8\StringObject(V8\Value)->IsString(): bool(false)
V8\StringObject(V8\Value)->IsSymbol(): bool(false)
V8\StringObject(V8\Value)->IsFunction(): bool(false)
V8\StringObject(V8\Value)->IsArray(): bool(false)
V8\StringObject(V8\Value)->IsObject(): bool(true)
V8\StringObject(V8\Value)->IsBoolean(): bool(false)
V8\StringObject(V8\Value)->IsNumber(): bool(false)
V8\StringObject(V8\Value)->IsInt32(): bool(false)
V8\StringObject(V8\Value)->IsUint32(): bool(false)
V8\StringObject(V8\Value)->IsDate(): bool(false)
V8\StringObject(V8\Value)->IsArgumentsObject(): bool(false)
V8\StringObject(V8\Value)->IsBooleanObject(): bool(false)
V8\StringObject(V8\Value)->IsNumberObject(): bool(false)
V8\StringObject(V8\Value)->IsStringObject(): bool(true)
V8\StringObject(V8\Value)->IsSymbolObject(): bool(false)
V8\StringObject(V8\Value)->IsNativeError(): bool(false)
V8\StringObject(V8\Value)->IsRegExp(): bool(false)


val: test string
typeof val: object


Returned value should be the same:
----------------------------------
Expected value is identical to actual value


Checkers on boxed from script:
------------------------------
V8\StringObject(V8\Value)->TypeOf(): V8\StringValue->Value(): string(6) "object"

V8\StringObject(V8\ObjectValue)->IsCallable(): bool(false)
V8\StringObject(V8\ObjectValue)->IsConstructor(): bool(false)
V8\StringObject(V8\Value)->IsUndefined(): bool(false)
V8\StringObject(V8\Value)->IsNull(): bool(false)
V8\StringObject(V8\Value)->IsNullOrUndefined(): bool(false)
V8\StringObject(V8\Value)->IsTrue(): bool(false)
V8\StringObject(V8\Value)->IsFalse(): bool(false)
V8\StringObject(V8\Value)->IsName(): bool(false)
V8\StringObject(V8\Value)->IsString(): bool(false)
V8\StringObject(V8\Value)->IsSymbol(): bool(false)
V8\StringObject(V8\Value)->IsFunction(): bool(false)
V8\StringObject(V8\Value)->IsArray(): bool(false)
V8\StringObject(V8\Value)->IsObject(): bool(true)
V8\StringObject(V8\Value)->IsBoolean(): bool(false)
V8\StringObject(V8\Value)->IsNumber(): bool(false)
V8\StringObject(V8\Value)->IsInt32(): bool(false)
V8\StringObject(V8\Value)->IsUint32(): bool(false)
V8\StringObject(V8\Value)->IsDate(): bool(false)
V8\StringObject(V8\Value)->IsArgumentsObject(): bool(false)
V8\StringObject(V8\Value)->IsBooleanObject(): bool(false)
V8\StringObject(V8\Value)->IsNumberObject(): bool(false)
V8\StringObject(V8\Value)->IsStringObject(): bool(true)
V8\StringObject(V8\Value)->IsSymbolObject(): bool(false)
V8\StringObject(V8\Value)->IsNativeError(): bool(false)
V8\StringObject(V8\Value)->IsRegExp(): bool(false)
