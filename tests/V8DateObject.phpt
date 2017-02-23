--TEST--
V8\DateObject
--SKIPIF--
<?php if (!extension_loaded("v8")) {
    print "skip";
} ?>
--ENV--
TZ=UTC
--INI--
date.timezone = "UTC"
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

$isolate1 = new \V8\Isolate();
$global_template1 = new V8\ObjectTemplate($isolate1);

// TODO: fix it, this cause segfault due to FunctionTemplate object destruction and all it internal structures cleanup
//$global_template1->Set('print', $v8_helper->getPrintFunctionTemplate($isolate1), \V8\PropertyAttribute::DontDelete);
$print_func_tpl = $v8_helper->getPrintFunctionTemplate($isolate1);
$global_template1->Set(new \V8\StringValue($isolate1, 'print'), $print_func_tpl, \V8\PropertyAttribute::DontDelete);

$context1 = new V8\Context($isolate1, $global_template1);

$test_time = 1445444940000.0;
$value = new V8\DateObject($context1, $test_time);

$helper->header('Object representation');
$helper->dump($value);
$helper->space();

$helper->assert('DateObject extends ObjectValue', $value instanceof \V8\ObjectValue);
$helper->line();

$helper->header('Getters');
$helper->method_export($value, 'ValueOf');
$helper->space();

$v8_helper->run_checks($value, 'Checkers');

$context1->GlobalObject()->Set($context1, new \V8\StringValue($isolate1, 'val'), $value);

$source1 = '
var orig = val;
print("val: ", val, "\n");
print("typeof val: ", typeof val, "\n");
orig
';
$file_name1 = 'test.js';

$script1 = new V8\Script($context1, new \V8\StringValue($isolate1, $source1), new \V8\ScriptOrigin($file_name1));
$res1 = $script1->Run($context1);
$helper->space();

$helper->header('Returned value should be the same');
$helper->value_matches_with_no_output($res1, $value);
$helper->space();

$helper->header('Timezone change (with notification to v8)');

// we suppose that tests run within UTC timezone, now let's change that
// ini_set('date.timezone', 'America/Los_Angeles'); // NOTE: this works only for PHP code, for v8 we should touch env TZ variable:
$old_tz = getenv('TZ');

putenv('TZ=America/Los_Angeles'); // UTC offset DST (ISO 8601)‎: ‎−07:00, UTC offset (ISO 8601)‎: ‎−08:00
\V8\DateObject::DateTimeConfigurationChangeNotification($isolate1);
$value = new V8\DateObject($context1, $test_time);

$context1->GlobalObject()->Set($context1, new \V8\StringValue($isolate1, 'val'), $value);

$source1 = '
print("val: ", val, "\n");
print("typeof val: ", typeof val, "\n");
val
';
$file_name1 = 'test.js';


$script1 = new V8\Script($context1, new \V8\StringValue($isolate1, $source1), new \V8\ScriptOrigin($file_name1));
$res1 = $script1->Run($context1);
$helper->value_matches($test_time, $value->ValueOf());
$helper->space();


$helper->header('Timezone change (without notification to v8)');

putenv('TZ=America/New_York'); // UTC offset DST (ISO 8601)‎: ‎−05:00, UTC offset (ISO 8601)‎: ‎−04:00

$value = new V8\DateObject($context1, $test_time);
$context1->GlobalObject()->Set($context1, new \V8\StringValue($isolate1, 'val'), $value);

$source1 = '
print("val: ", val, "\n");
print("typeof val: ", typeof val, "\n");
val
';
$file_name1 = 'test.js';

// TODO: for some reason v8 still be notified about TZ changes, see https://groups.google.com/forum/?fromgroups#!topic/v8-users/f249jR67ANk
// TODO: we temporary set EDT instead of PDT which was before
$script1 = new V8\Script($context1, new \V8\StringValue($isolate1, $source1), new \V8\ScriptOrigin($file_name1));
$res1 = $script1->Run($context1);
$helper->value_matches($test_time, $value->ValueOf());
$helper->space();

putenv("TZ={$old_tz}"); // Go back


?>
--EXPECT--
Object representation:
----------------------
object(V8\DateObject)#8 (2) {
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
  object(V8\Context)#7 (1) {
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
  }
}


DateObject extends ObjectValue: ok

Getters:
--------
V8\DateObject->ValueOf(): float(1445444940000)


Checkers:
---------
V8\DateObject(V8\Value)->TypeOf(): V8\StringValue->Value(): string(6) "object"

V8\DateObject(V8\ObjectValue)->IsCallable(): bool(false)
V8\DateObject(V8\ObjectValue)->IsConstructor(): bool(false)
V8\DateObject(V8\Value)->IsUndefined(): bool(false)
V8\DateObject(V8\Value)->IsNull(): bool(false)
V8\DateObject(V8\Value)->IsNullOrUndefined(): bool(false)
V8\DateObject(V8\Value)->IsTrue(): bool(false)
V8\DateObject(V8\Value)->IsFalse(): bool(false)
V8\DateObject(V8\Value)->IsName(): bool(false)
V8\DateObject(V8\Value)->IsString(): bool(false)
V8\DateObject(V8\Value)->IsSymbol(): bool(false)
V8\DateObject(V8\Value)->IsFunction(): bool(false)
V8\DateObject(V8\Value)->IsArray(): bool(false)
V8\DateObject(V8\Value)->IsObject(): bool(true)
V8\DateObject(V8\Value)->IsBoolean(): bool(false)
V8\DateObject(V8\Value)->IsNumber(): bool(false)
V8\DateObject(V8\Value)->IsInt32(): bool(false)
V8\DateObject(V8\Value)->IsUint32(): bool(false)
V8\DateObject(V8\Value)->IsDate(): bool(true)
V8\DateObject(V8\Value)->IsArgumentsObject(): bool(false)
V8\DateObject(V8\Value)->IsBooleanObject(): bool(false)
V8\DateObject(V8\Value)->IsNumberObject(): bool(false)
V8\DateObject(V8\Value)->IsStringObject(): bool(false)
V8\DateObject(V8\Value)->IsSymbolObject(): bool(false)
V8\DateObject(V8\Value)->IsNativeError(): bool(false)
V8\DateObject(V8\Value)->IsRegExp(): bool(false)
V8\DateObject(V8\Value)->IsAsyncFunction(): bool(false)
V8\DateObject(V8\Value)->IsGeneratorFunction(): bool(false)
V8\DateObject(V8\Value)->IsGeneratorObject(): bool(false)
V8\DateObject(V8\Value)->IsPromise(): bool(false)
V8\DateObject(V8\Value)->IsMap(): bool(false)
V8\DateObject(V8\Value)->IsSet(): bool(false)
V8\DateObject(V8\Value)->IsMapIterator(): bool(false)
V8\DateObject(V8\Value)->IsSetIterator(): bool(false)
V8\DateObject(V8\Value)->IsWeakMap(): bool(false)
V8\DateObject(V8\Value)->IsWeakSet(): bool(false)
V8\DateObject(V8\Value)->IsArrayBuffer(): bool(false)
V8\DateObject(V8\Value)->IsArrayBufferView(): bool(false)
V8\DateObject(V8\Value)->IsTypedArray(): bool(false)
V8\DateObject(V8\Value)->IsUint8Array(): bool(false)
V8\DateObject(V8\Value)->IsUint8ClampedArray(): bool(false)
V8\DateObject(V8\Value)->IsInt8Array(): bool(false)
V8\DateObject(V8\Value)->IsUint16Array(): bool(false)
V8\DateObject(V8\Value)->IsInt16Array(): bool(false)
V8\DateObject(V8\Value)->IsUint32Array(): bool(false)
V8\DateObject(V8\Value)->IsInt32Array(): bool(false)
V8\DateObject(V8\Value)->IsFloat32Array(): bool(false)
V8\DateObject(V8\Value)->IsFloat64Array(): bool(false)
V8\DateObject(V8\Value)->IsDataView(): bool(false)
V8\DateObject(V8\Value)->IsSharedArrayBuffer(): bool(false)
V8\DateObject(V8\Value)->IsProxy(): bool(false)


val: Wed Oct 21 2015 16:29:00 GMT+0000 (UTC)
typeof val: object


Returned value should be the same:
----------------------------------
Expected value is identical to actual value


Timezone change (with notification to v8):
------------------------------------------
val: Wed Oct 21 2015 09:29:00 GMT-0700 (PDT)
typeof val: object
Expected 1445444940000.0 value is identical to actual value 1445444940000.0


Timezone change (without notification to v8):
---------------------------------------------
val: Wed Oct 21 2015 09:29:00 GMT-0700 (EDT)
typeof val: object
Expected 1445444940000.0 value is identical to actual value 1445444940000.0
