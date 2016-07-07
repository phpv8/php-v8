--TEST--
v8\StackTrace
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

require '.tracking_dtors.php';

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';
require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);


$isolate = new \v8Tests\TrackingDtors\Isolate();
$context = new \v8Tests\TrackingDtors\Context($isolate);

$array = new \v8\ArrayObject($context);

$frame_1 = new \v8\StackFrame(1);
$frame_2 = new \v8\StackFrame(2);
$frames = [$frame_1, $frame_2];

$obj = new \v8\StackTrace($frames, $array);


$helper->header('Object representation');
$helper->dump($obj);
$helper->space();

$helper->header('Class constants');
$helper->dump_object_constants($obj);
$helper->space();

$helper->header('Test getters');

$helper->method_matches($obj, 'GetFrames', $frames);
$helper->method_matches($obj, 'GetFrame', $frame_1, [0]);
$helper->method_matches_with_output($obj, 'GetFrameCount', 2);
$helper->method_matches_instanceof($obj, 'AsArray', v8\ArrayObject::class);
$helper->space();

$obj = null;
$array = null;
$context = null;
$isolate = null;

echo "END", PHP_EOL
?>
--EXPECT--
Object representation:
----------------------
object(v8\StackTrace)#8 (2) {
  ["frames":"v8\StackTrace":private]=>
  array(2) {
    [0]=>
    object(v8\StackFrame)#6 (8) {
      ["line_number":"v8\StackFrame":private]=>
      int(1)
      ["column":"v8\StackFrame":private]=>
      int(0)
      ["script_id":"v8\StackFrame":private]=>
      int(0)
      ["script_name":"v8\StackFrame":private]=>
      string(0) ""
      ["script_name_or_source_url":"v8\StackFrame":private]=>
      string(0) ""
      ["function_name":"v8\StackFrame":private]=>
      string(0) ""
      ["is_eval":"v8\StackFrame":private]=>
      bool(false)
      ["is_constructor":"v8\StackFrame":private]=>
      bool(false)
    }
    [1]=>
    object(v8\StackFrame)#7 (8) {
      ["line_number":"v8\StackFrame":private]=>
      int(2)
      ["column":"v8\StackFrame":private]=>
      int(0)
      ["script_id":"v8\StackFrame":private]=>
      int(0)
      ["script_name":"v8\StackFrame":private]=>
      string(0) ""
      ["script_name_or_source_url":"v8\StackFrame":private]=>
      string(0) ""
      ["function_name":"v8\StackFrame":private]=>
      string(0) ""
      ["is_eval":"v8\StackFrame":private]=>
      bool(false)
      ["is_constructor":"v8\StackFrame":private]=>
      bool(false)
    }
  }
  ["as_array":"v8\StackTrace":private]=>
  object(v8\ArrayObject)#5 (2) {
    ["isolate":"v8\Value":private]=>
    object(v8Tests\TrackingDtors\Isolate)#3 (5) {
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
    object(v8Tests\TrackingDtors\Context)#4 (4) {
      ["isolate":"v8\Context":private]=>
      object(v8Tests\TrackingDtors\Isolate)#3 (5) {
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
      NULL
      ["global_template":"v8\Context":private]=>
      NULL
      ["global_object":"v8\Context":private]=>
      NULL
    }
  }
}


Class constants:
----------------
v8\StackTrace::MIN_FRAME_LIMIT = 0
v8\StackTrace::MAX_FRAME_LIMIT = 1000


Test getters:
-------------
v8\StackTrace::GetFrames() matches expected value
v8\StackTrace::GetFrame() matches expected value
v8\StackTrace::GetFrameCount() matches expected 2
v8\StackTrace::AsArray() result is instance of v8\ArrayObject


Context dies now!
Isolate dies now!
END
