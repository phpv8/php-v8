--TEST--
v8\StringObject
--SKIPIF--
<?php if (!extension_loaded("v8")) { print "skip"; } ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

// Tests:


$isolate1 = new \v8\Isolate();
$extensions1 = [];
$global_template1 = new v8\ObjectTemplate($isolate1);

$global_template1->Set(new \v8\StringValue($isolate1, 'print'), $v8_helper->getPrintFunctionTemplate($isolate1), \v8\PropertyAttribute::DontDelete);

$context1 = new v8\Context($isolate1, $extensions1, $global_template1);

$value = new v8\StringObject($context1, new \v8\StringValue($isolate1, 'test string'));

$helper->header('Object representation');
$helper->dump($value);
$helper->space();

$helper->assert('StringObject extends ObjectValue', $value instanceof \v8\ObjectValue);
$helper->line();

$helper->header('Getters');
$helper->method_export($value, 'ValueOf');
$helper->method_export($value->ValueOf(), 'Value');
$helper->space();

$v8_helper->run_checks($value, 'Checkers');

$context1->GlobalObject()->Set($context1, new \v8\StringValue($isolate1, 'val'), $value);

$source1    = '
print("val: ", val, "\n");
print("typeof val: ", typeof val, "\n");

val
';
$file_name1 = 'test.js';

$script1 = new v8\Script($context1, new \v8\StringValue($isolate1, $source1), new \v8\ScriptOrigin($file_name1));
$res1 = $script1->Run();
$helper->space();

$helper->header('Returned value should be the same');
$helper->value_matches_with_no_output($res1, $value);
$helper->space();

$source1    = 'new String("boxed test string from script");';
$file_name1 = 'test.js';

$script1 = new v8\Script($context1, new \v8\StringValue($isolate1, $source1), new \v8\ScriptOrigin($file_name1));
$res1 = $script1->Run();

$v8_helper->run_checks($res1, 'Checkers on boxed from script')

?>
--EXPECT--
Object representation:
----------------------
object(v8\StringObject)#6 (2) {
  ["isolate":"v8\Value":private]=>
  object(v8\Isolate)#3 (1) {
    ["snapshot":"v8\Isolate":private]=>
    NULL
  }
  ["context":"v8\ObjectValue":private]=>
  object(v8\Context)#5 (4) {
    ["isolate":"v8\Context":private]=>
    object(v8\Isolate)#3 (1) {
      ["snapshot":"v8\Isolate":private]=>
      NULL
    }
    ["extensions":"v8\Context":private]=>
    array(0) {
    }
    ["global_template":"v8\Context":private]=>
    object(v8\ObjectTemplate)#4 (1) {
      ["isolate":"v8\Template":private]=>
      object(v8\Isolate)#3 (1) {
        ["snapshot":"v8\Isolate":private]=>
        NULL
      }
    }
    ["global_object":"v8\Context":private]=>
    NULL
  }
}


StringObject extends ObjectValue: ok

Getters:
--------
v8\StringObject->ValueOf():
    object(v8\StringValue)#91 (1) {
      ["isolate":"v8\Value":private]=>
      object(v8\Isolate)#3 (1) {
        ["snapshot":"v8\Isolate":private]=>
        NULL
      }
    }
v8\StringValue->Value(): string(11) "test string"


Checkers:
---------
v8\StringObject(v8\ObjectValue)->IsCallable(): bool(false)
v8\StringObject(v8\Value)->IsUndefined(): bool(false)
v8\StringObject(v8\Value)->IsNull(): bool(false)
v8\StringObject(v8\Value)->IsTrue(): bool(false)
v8\StringObject(v8\Value)->IsFalse(): bool(false)
v8\StringObject(v8\Value)->IsName(): bool(false)
v8\StringObject(v8\Value)->IsString(): bool(false)
v8\StringObject(v8\Value)->IsSymbol(): bool(false)
v8\StringObject(v8\Value)->IsFunction(): bool(false)
v8\StringObject(v8\Value)->IsArray(): bool(false)
v8\StringObject(v8\Value)->IsObject(): bool(true)
v8\StringObject(v8\Value)->IsBoolean(): bool(false)
v8\StringObject(v8\Value)->IsNumber(): bool(false)
v8\StringObject(v8\Value)->IsInt32(): bool(false)
v8\StringObject(v8\Value)->IsUint32(): bool(false)
v8\StringObject(v8\Value)->IsDate(): bool(false)
v8\StringObject(v8\Value)->IsArgumentsObject(): bool(false)
v8\StringObject(v8\Value)->IsBooleanObject(): bool(false)
v8\StringObject(v8\Value)->IsNumberObject(): bool(false)
v8\StringObject(v8\Value)->IsStringObject(): bool(true)
v8\StringObject(v8\Value)->IsSymbolObject(): bool(false)
v8\StringObject(v8\Value)->IsNativeError(): bool(false)
v8\StringObject(v8\Value)->IsRegExp(): bool(false)


val: test string
typeof val: object


Returned value should be the same:
----------------------------------
Expected value is identical to actual value


Checkers on boxed from script:
------------------------------
v8\StringObject(v8\ObjectValue)->IsCallable(): bool(false)
v8\StringObject(v8\Value)->IsUndefined(): bool(false)
v8\StringObject(v8\Value)->IsNull(): bool(false)
v8\StringObject(v8\Value)->IsTrue(): bool(false)
v8\StringObject(v8\Value)->IsFalse(): bool(false)
v8\StringObject(v8\Value)->IsName(): bool(false)
v8\StringObject(v8\Value)->IsString(): bool(false)
v8\StringObject(v8\Value)->IsSymbol(): bool(false)
v8\StringObject(v8\Value)->IsFunction(): bool(false)
v8\StringObject(v8\Value)->IsArray(): bool(false)
v8\StringObject(v8\Value)->IsObject(): bool(true)
v8\StringObject(v8\Value)->IsBoolean(): bool(false)
v8\StringObject(v8\Value)->IsNumber(): bool(false)
v8\StringObject(v8\Value)->IsInt32(): bool(false)
v8\StringObject(v8\Value)->IsUint32(): bool(false)
v8\StringObject(v8\Value)->IsDate(): bool(false)
v8\StringObject(v8\Value)->IsArgumentsObject(): bool(false)
v8\StringObject(v8\Value)->IsBooleanObject(): bool(false)
v8\StringObject(v8\Value)->IsNumberObject(): bool(false)
v8\StringObject(v8\Value)->IsStringObject(): bool(true)
v8\StringObject(v8\Value)->IsSymbolObject(): bool(false)
v8\StringObject(v8\Value)->IsNativeError(): bool(false)
v8\StringObject(v8\Value)->IsRegExp(): bool(false)
