--TEST--
V8\StackTrace
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

$array = new \V8\ArrayObject($context);

$frame_1 = new \V8\StackFrame(1);
$frame_2 = new \V8\StackFrame(2);
$frames = [$frame_1, $frame_2];

$obj = new \V8\StackTrace($frames, $array);


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
$helper->method_matches_instanceof($obj, 'AsArray', V8\ArrayObject::class);
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
object(V8\StackTrace)#8 (2) {
  ["frames":"V8\StackTrace":private]=>
  array(2) {
    [0]=>
    object(V8\StackFrame)#6 (8) {
      ["line_number":"V8\StackFrame":private]=>
      int(1)
      ["column":"V8\StackFrame":private]=>
      int(0)
      ["script_id":"V8\StackFrame":private]=>
      int(0)
      ["script_name":"V8\StackFrame":private]=>
      string(0) ""
      ["script_name_or_source_url":"V8\StackFrame":private]=>
      string(0) ""
      ["function_name":"V8\StackFrame":private]=>
      string(0) ""
      ["is_eval":"V8\StackFrame":private]=>
      bool(false)
      ["is_constructor":"V8\StackFrame":private]=>
      bool(false)
    }
    [1]=>
    object(V8\StackFrame)#7 (8) {
      ["line_number":"V8\StackFrame":private]=>
      int(2)
      ["column":"V8\StackFrame":private]=>
      int(0)
      ["script_id":"V8\StackFrame":private]=>
      int(0)
      ["script_name":"V8\StackFrame":private]=>
      string(0) ""
      ["script_name_or_source_url":"V8\StackFrame":private]=>
      string(0) ""
      ["function_name":"V8\StackFrame":private]=>
      string(0) ""
      ["is_eval":"V8\StackFrame":private]=>
      bool(false)
      ["is_constructor":"V8\StackFrame":private]=>
      bool(false)
    }
  }
  ["as_array":"V8\StackTrace":private]=>
  object(V8\ArrayObject)#5 (2) {
    ["isolate":"V8\Value":private]=>
    object(v8Tests\TrackingDtors\Isolate)#3 (5) {
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
    object(v8Tests\TrackingDtors\Context)#4 (4) {
      ["isolate":"V8\Context":private]=>
      object(v8Tests\TrackingDtors\Isolate)#3 (5) {
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
      NULL
      ["global_template":"V8\Context":private]=>
      NULL
      ["global_object":"V8\Context":private]=>
      NULL
    }
  }
}


Class constants:
----------------
V8\StackTrace::MIN_FRAME_LIMIT = 0
V8\StackTrace::MAX_FRAME_LIMIT = 1000


Test getters:
-------------
V8\StackTrace::GetFrames() matches expected value
V8\StackTrace::GetFrame() matches expected value
V8\StackTrace::GetFrameCount() matches expected 2
V8\StackTrace::AsArray() result is instance of V8\ArrayObject


Context dies now!
Isolate dies now!
END
