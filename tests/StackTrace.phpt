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

$frame_1 = new \V8\StackFrame(1);
$frame_2 = new \V8\StackFrame(2);
$frames = [$frame_1, $frame_2];

$obj = new \V8\StackTrace($frames);


$helper->header('Object representation');
$helper->dump($obj);
$helper->space();

$helper->header('Class constants');
$helper->dump_object_constants($obj);
$helper->space();

$helper->header('Test getters');

$helper->method_matches($obj, 'getFrames', $frames);
$helper->method_matches($obj, 'getFrame', $frame_1, [0]);
$helper->method_matches_with_output($obj, 'getFrameCount', 2);
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
object(V8\StackTrace)#7 (1) {
  ["frames":"V8\StackTrace":private]=>
  array(2) {
    [0]=>
    object(V8\StackFrame)#5 (9) {
      ["line_number":"V8\StackFrame":private]=>
      int(1)
      ["column":"V8\StackFrame":private]=>
      NULL
      ["script_id":"V8\StackFrame":private]=>
      NULL
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
      ["is_wasm":"V8\StackFrame":private]=>
      bool(false)
    }
    [1]=>
    object(V8\StackFrame)#6 (9) {
      ["line_number":"V8\StackFrame":private]=>
      int(2)
      ["column":"V8\StackFrame":private]=>
      NULL
      ["script_id":"V8\StackFrame":private]=>
      NULL
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
      ["is_wasm":"V8\StackFrame":private]=>
      bool(false)
    }
  }
}


Class constants:
----------------
V8\StackTrace::MIN_FRAME_LIMIT = 0
V8\StackTrace::MAX_FRAME_LIMIT = 1000


Test getters:
-------------
V8\StackTrace::getFrames() matches expected value
V8\StackTrace::getFrame() matches expected value
V8\StackTrace::getFrameCount() matches expected 2


Context dies now!
Isolate dies now!
END
