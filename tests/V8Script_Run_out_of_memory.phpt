--TEST--
V8\Script::Run() - out of memory example
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
<?php if (!getenv("DEV_TESTS")) print "skip"; ?>
<?php if (getenv("SKIP_SLOW_TESTS")) print "skip slow"; ?>
--FILE--
<?php
/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';
require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);


$isolate = new V8\Isolate();
$global_template = new V8\ObjectTemplate($isolate);

$value = new V8\StringValue($isolate, 'TEST VALUE 111');

$global_template->Set(new \V8\StringValue($isolate, 'test'), $value);
$context = new V8\Context($isolate, $global_template);
$v8_helper->injectConsoleLog($context);

// This causes segfault
$source = '
x = \'x\';
var multiply = 25;

while (multiply-- > 0){
 x = x+x;
 console.log(x.length);
}

var arr = [];

console.log("\n");

while (1) {
     arr.push(x);
     //if (!(arr.length % 10000)) {
     //   console.log(arr.length);
     //}
}
';

$file_name = 'test.js';

$script = new V8\Script($context, new \V8\StringValue($isolate, $source), new \V8\ScriptOrigin($file_name));
try {
    $res = $script->Run($context);
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
--EXPECTF--
2
4
8
16
32
64
128
256
512
1024
2048
4096
8192
16384
32768
65536
131072
262144
524288
1048576
2097152
4194304
8388608
16777216
33554432



Fatal error: V8 OOM hit: location=invalid array length, is_heap_oom=yes
 in %s/V8Script_Run_out_of_memory.php on line 44

<--- Last few GCs --->

%s %d ms: Mark-sweep %f (%f) -> %f (%f) MB, %f / %f ms %s
%s %d ms: Mark-sweep %f (%f) -> %f (%f) MB, %f / %f ms %s
%s %d ms: Mark-sweep %f (%f) -> %f (%f) MB, %f / %f ms %s


<--- JS stacktrace --->

==== JS stack trace =========================================

Security context: 0x%x <JS Object>
    2: /* anonymous */ [test.js:~1] [pc=0x%x](this=0x%x <JS Global Object>)

==== Details ================================================

[2]: /* anonymous */ [test.js:~1] [pc=0x%x](this=0x%x <JS Global Object>) {
// optimized frame
--------- s o u r c e   c o d e ---------
%s
