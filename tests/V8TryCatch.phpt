--TEST--
V8\TryCatch
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

require '.tracking_dtors.php';

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';


$isolate = new \v8Tests\TrackingDtors\Isolate();
$context = new v8Tests\TrackingDtors\Context($isolate);


$obj = new \V8\TryCatch($isolate, $context);

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



$exception = new \V8\ObjectValue($context);
$message = new \V8\Message('message', 'line', new \V8\ScriptOrigin('resource_name'), 'resource_name', new \V8\StackTrace([], new \V8\ArrayObject($context)));
$trace = new \V8\StringValue($isolate, 'trace');

$obj = new \V8\TryCatch($isolate, $context, $exception, $trace, $message, true, true);

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
object(V8\TryCatch)#4 (7) {
  ["isolate":"V8\TryCatch":private]=>
  object(v8Tests\TrackingDtors\Isolate)#2 (5) {
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
  ["context":"V8\TryCatch":private]=>
  object(v8Tests\TrackingDtors\Context)#3 (4) {
    ["isolate":"V8\Context":private]=>
    object(v8Tests\TrackingDtors\Isolate)#2 (5) {
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
  ["exception":"V8\TryCatch":private]=>
  NULL
  ["stack_trace":"V8\TryCatch":private]=>
  NULL
  ["message":"V8\TryCatch":private]=>
  NULL
  ["can_continue":"V8\TryCatch":private]=>
  bool(false)
  ["has_terminated":"V8\TryCatch":private]=>
  bool(false)
}


Test getters (default):
-----------------------
V8\TryCatch::GetIsolate() matches expected value
V8\TryCatch::GetContext() matches expected value
V8\TryCatch::Exception() matches expected value
V8\TryCatch::Message() matches expected value
V8\TryCatch::StackTrace() matches expected value
V8\TryCatch::CanContinue() matches expected value
V8\TryCatch::HasTerminated() matches expected value


Object representation:
----------------------
object(V8\TryCatch)#12 (7) {
  ["isolate":"V8\TryCatch":private]=>
  object(v8Tests\TrackingDtors\Isolate)#2 (5) {
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
  ["context":"V8\TryCatch":private]=>
  object(v8Tests\TrackingDtors\Context)#3 (4) {
    ["isolate":"V8\Context":private]=>
    object(v8Tests\TrackingDtors\Isolate)#2 (5) {
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
  ["exception":"V8\TryCatch":private]=>
  object(V8\ObjectValue)#5 (2) {
    ["isolate":"V8\Value":private]=>
    object(v8Tests\TrackingDtors\Isolate)#2 (5) {
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
    object(v8Tests\TrackingDtors\Context)#3 (4) {
      ["isolate":"V8\Context":private]=>
      object(v8Tests\TrackingDtors\Isolate)#2 (5) {
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
  ["stack_trace":"V8\TryCatch":private]=>
  object(V8\StringValue)#11 (1) {
    ["isolate":"V8\Value":private]=>
    object(v8Tests\TrackingDtors\Isolate)#2 (5) {
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
  ["message":"V8\TryCatch":private]=>
  object(V8\Message)#6 (12) {
    ["message":"V8\Message":private]=>
    string(7) "message"
    ["script_origin":"V8\Message":private]=>
    object(V8\ScriptOrigin)#7 (6) {
      ["resource_name":"V8\ScriptOrigin":private]=>
      string(13) "resource_name"
      ["resource_line_offset":"V8\ScriptOrigin":private]=>
      int(0)
      ["resource_column_offset":"V8\ScriptOrigin":private]=>
      int(0)
      ["options":"V8\ScriptOrigin":private]=>
      object(V8\ScriptOriginOptions)#8 (3) {
        ["is_embedder_debug_script":"V8\ScriptOriginOptions":private]=>
        bool(false)
        ["is_shared_cross_origin":"V8\ScriptOriginOptions":private]=>
        bool(false)
        ["is_opaque":"V8\ScriptOriginOptions":private]=>
        bool(false)
      }
      ["script_id":"V8\ScriptOrigin":private]=>
      int(0)
      ["source_map_url":"V8\ScriptOrigin":private]=>
      string(0) ""
    }
    ["source_line":"V8\Message":private]=>
    string(4) "line"
    ["resource_name":"V8\Message":private]=>
    string(13) "resource_name"
    ["stack_trace":"V8\Message":private]=>
    object(V8\StackTrace)#9 (2) {
      ["frames":"V8\StackTrace":private]=>
      array(0) {
      }
      ["as_array":"V8\StackTrace":private]=>
      object(V8\ArrayObject)#10 (2) {
        ["isolate":"V8\Value":private]=>
        object(v8Tests\TrackingDtors\Isolate)#2 (5) {
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
        object(v8Tests\TrackingDtors\Context)#3 (4) {
          ["isolate":"V8\Context":private]=>
          object(v8Tests\TrackingDtors\Isolate)#2 (5) {
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
    ["line_number":"V8\Message":private]=>
    int(0)
    ["start_position":"V8\Message":private]=>
    int(-1)
    ["end_position":"V8\Message":private]=>
    int(-1)
    ["start_column":"V8\Message":private]=>
    int(0)
    ["end_column":"V8\Message":private]=>
    int(0)
    ["is_shared_cross_origin":"V8\Message":private]=>
    bool(false)
    ["is_opaque":"V8\Message":private]=>
    bool(false)
  }
  ["can_continue":"V8\TryCatch":private]=>
  bool(true)
  ["has_terminated":"V8\TryCatch":private]=>
  bool(true)
}


Test getters:
-------------
V8\TryCatch::GetIsolate() matches expected value
V8\TryCatch::GetContext() matches expected value
V8\TryCatch::Exception() matches expected value
V8\TryCatch::Message() matches expected value
V8\TryCatch::StackTrace() matches expected value
V8\TryCatch::CanContinue() matches expected value
V8\TryCatch::HasTerminated() matches expected value


Context dies now!
Isolate dies now!
END
