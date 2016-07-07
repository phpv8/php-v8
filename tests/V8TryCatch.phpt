--TEST--
v8\TryCatch
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

require '.tracking_dtors.php';

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';


$isolate = new \v8Tests\TrackingDtors\Isolate();
$context = new v8Tests\TrackingDtors\Context($isolate);


$obj = new \v8\TryCatch($isolate, $context);

$helper->header('Object representation (default)');
$helper->dump($obj);
$helper->space();

$helper->header('Test getters (default)');
$helper->method_matches($obj, 'GetIsolate', $isolate);
$helper->method_matches($obj, 'GetContext', $context);
$helper->method_matches($obj, 'Exception', null);
$helper->method_matches($obj, 'Message', null);
$helper->method_matches($obj, 'StackTrace', null);

$helper->method_matches($obj, 'CanContinue', false);
$helper->method_matches($obj, 'HasTerminated', false);
$helper->space();



$exception = new \v8\ObjectValue($context);
$message = new \v8\Message('message', 'line', new \v8\ScriptOrigin('resource_name'), 'resource_name', new \v8\StackTrace([], new \v8\ArrayObject($context)));
$trace = new \v8\StringValue($isolate, 'trace');

$obj = new \v8\TryCatch($isolate, $context, $exception, $trace, $message, true, true);

$helper->header('Object representation');
$helper->dump($obj);
$helper->space();

$helper->header('Test getters');
$helper->method_matches($obj, 'GetIsolate', $isolate);
$helper->method_matches($obj, 'GetContext', $context);
$helper->method_matches($obj, 'Exception', $exception);
$helper->method_matches($obj, 'Message', $message);
$helper->method_matches($obj, 'StackTrace', $trace);

$helper->method_matches($obj, 'CanContinue', true);
$helper->method_matches($obj, 'HasTerminated', true);
$helper->space();


$exception = null;
$message = null;
$trace = null;

$obj = null;
$isolate = null;
$context = null;

echo 'END', PHP_EOL;
?>
--EXPECT--
Object representation (default):
--------------------------------
object(v8\TryCatch)#4 (7) {
  ["isolate":"v8\TryCatch":private]=>
  object(v8Tests\TrackingDtors\Isolate)#2 (5) {
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
  ["context":"v8\TryCatch":private]=>
  object(v8Tests\TrackingDtors\Context)#3 (4) {
    ["isolate":"v8\Context":private]=>
    object(v8Tests\TrackingDtors\Isolate)#2 (5) {
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
  ["exception":"v8\TryCatch":private]=>
  NULL
  ["stack_trace":"v8\TryCatch":private]=>
  NULL
  ["message":"v8\TryCatch":private]=>
  NULL
  ["can_continue":"v8\TryCatch":private]=>
  bool(false)
  ["has_terminated":"v8\TryCatch":private]=>
  bool(false)
}


Test getters (default):
-----------------------
v8\TryCatch::GetIsolate() matches expected value
v8\TryCatch::GetContext() matches expected value
v8\TryCatch::Exception() matches expected value
v8\TryCatch::Message() matches expected value
v8\TryCatch::StackTrace() matches expected value
v8\TryCatch::CanContinue() matches expected value
v8\TryCatch::HasTerminated() matches expected value


Object representation:
----------------------
object(v8\TryCatch)#12 (7) {
  ["isolate":"v8\TryCatch":private]=>
  object(v8Tests\TrackingDtors\Isolate)#2 (5) {
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
  ["context":"v8\TryCatch":private]=>
  object(v8Tests\TrackingDtors\Context)#3 (4) {
    ["isolate":"v8\Context":private]=>
    object(v8Tests\TrackingDtors\Isolate)#2 (5) {
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
  ["exception":"v8\TryCatch":private]=>
  object(v8\ObjectValue)#5 (2) {
    ["isolate":"v8\Value":private]=>
    object(v8Tests\TrackingDtors\Isolate)#2 (5) {
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
    object(v8Tests\TrackingDtors\Context)#3 (4) {
      ["isolate":"v8\Context":private]=>
      object(v8Tests\TrackingDtors\Isolate)#2 (5) {
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
  ["stack_trace":"v8\TryCatch":private]=>
  object(v8\StringValue)#11 (1) {
    ["isolate":"v8\Value":private]=>
    object(v8Tests\TrackingDtors\Isolate)#2 (5) {
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
  ["message":"v8\TryCatch":private]=>
  object(v8\Message)#6 (12) {
    ["message":"v8\Message":private]=>
    string(7) "message"
    ["script_origin":"v8\Message":private]=>
    object(v8\ScriptOrigin)#7 (6) {
      ["resource_name":"v8\ScriptOrigin":private]=>
      string(13) "resource_name"
      ["resource_line_offset":"v8\ScriptOrigin":private]=>
      int(0)
      ["resource_column_offset":"v8\ScriptOrigin":private]=>
      int(0)
      ["options":"v8\ScriptOrigin":private]=>
      object(v8\ScriptOriginOptions)#8 (3) {
        ["is_embedder_debug_script":"v8\ScriptOriginOptions":private]=>
        bool(false)
        ["is_shared_cross_origin":"v8\ScriptOriginOptions":private]=>
        bool(false)
        ["is_opaque":"v8\ScriptOriginOptions":private]=>
        bool(false)
      }
      ["script_id":"v8\ScriptOrigin":private]=>
      int(0)
      ["source_map_url":"v8\ScriptOrigin":private]=>
      string(0) ""
    }
    ["source_line":"v8\Message":private]=>
    string(4) "line"
    ["resource_name":"v8\Message":private]=>
    string(13) "resource_name"
    ["stack_trace":"v8\Message":private]=>
    object(v8\StackTrace)#9 (2) {
      ["frames":"v8\StackTrace":private]=>
      array(0) {
      }
      ["as_array":"v8\StackTrace":private]=>
      object(v8\ArrayObject)#10 (2) {
        ["isolate":"v8\Value":private]=>
        object(v8Tests\TrackingDtors\Isolate)#2 (5) {
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
        object(v8Tests\TrackingDtors\Context)#3 (4) {
          ["isolate":"v8\Context":private]=>
          object(v8Tests\TrackingDtors\Isolate)#2 (5) {
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
    ["line_number":"v8\Message":private]=>
    int(0)
    ["start_position":"v8\Message":private]=>
    int(-1)
    ["end_position":"v8\Message":private]=>
    int(-1)
    ["start_column":"v8\Message":private]=>
    int(0)
    ["end_column":"v8\Message":private]=>
    int(0)
    ["is_shared_cross_origin":"v8\Message":private]=>
    bool(false)
    ["is_opaque":"v8\Message":private]=>
    bool(false)
  }
  ["can_continue":"v8\TryCatch":private]=>
  bool(true)
  ["has_terminated":"v8\TryCatch":private]=>
  bool(true)
}


Test getters:
-------------
v8\TryCatch::GetIsolate() matches expected value
v8\TryCatch::GetContext() matches expected value
v8\TryCatch::Exception() matches expected value
v8\TryCatch::Message() matches expected value
v8\TryCatch::StackTrace() matches expected value
v8\TryCatch::CanContinue() matches expected value
v8\TryCatch::HasTerminated() matches expected value


Context dies now!
Isolate dies now!
END
