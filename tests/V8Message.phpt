--TEST--
V8\Message
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

$isolate = new \V8\Isolate();
$context = new \V8\Context($isolate);

$origin = new \V8\ScriptOrigin('resource_name');
$trace = new \V8\StackTrace([], new \V8\ArrayObject($context));

$obj = new V8\Message('message', 'source_line', $origin, 'resource_name', $trace);

$helper->header('Object representation (default)');
$helper->dump($obj);
$helper->space();

$helper->header('Test getters (default)');
$helper->method_matches_with_output($obj, 'Get', 'message');
$helper->method_matches_with_output($obj, 'GetSourceLine', 'source_line');
$helper->method_matches_instanceof($obj, 'GetScriptOrigin', V8\ScriptOrigin::class);
$helper->method_matches_with_output($obj, 'GetScriptResourceName', 'resource_name');
$helper->method_matches_instanceof($obj, 'GetStackTrace', V8\StackTrace::class);
$helper->method_matches_with_output($obj, 'GetLineNumber', 0);
$helper->method_matches_with_output($obj, 'GetStartPosition', -1);
$helper->method_matches_with_output($obj, 'GetEndPosition', -1);
$helper->method_matches_with_output($obj, 'GetStartColumn', 0);
$helper->method_matches_with_output($obj, 'GetEndColumn', 0);
$helper->method_matches_with_output($obj, 'IsSharedCrossOrigin', false);
$helper->method_matches_with_output($obj, 'IsOpaque', false);
$helper->space();


$obj = new V8\Message('message', 'source_line', $origin, 'resource_name', $trace, 1, 2, 3, 4, 5, true, true);

$helper->header('Object representation');
$helper->dump($obj);
$helper->space();

$helper->header('Test getters');
$helper->method_matches_with_output($obj, 'Get', 'message');
$helper->method_matches_with_output($obj, 'GetSourceLine', 'source_line');
$helper->method_matches_instanceof($obj, 'GetScriptOrigin', V8\ScriptOrigin::class);
$helper->method_matches_with_output($obj, 'GetScriptResourceName', 'resource_name');
$helper->method_matches_instanceof($obj, 'GetStackTrace', V8\StackTrace::class);
$helper->method_matches_with_output($obj, 'GetLineNumber', 1);
$helper->method_matches_with_output($obj, 'GetStartPosition', 2);
$helper->method_matches_with_output($obj, 'GetEndPosition', 3);
$helper->method_matches_with_output($obj, 'GetStartColumn', 4);
$helper->method_matches_with_output($obj, 'GetEndColumn', 5);
$helper->method_matches_with_output($obj, 'IsSharedCrossOrigin', true);
$helper->method_matches_with_output($obj, 'IsOpaque', true);
$helper->space();

?>
--EXPECT--
Object representation (default):
--------------------------------
object(V8\Message)#8 (12) {
  ["message":"V8\Message":private]=>
  string(7) "message"
  ["script_origin":"V8\Message":private]=>
  object(V8\ScriptOrigin)#4 (6) {
    ["resource_name":"V8\ScriptOrigin":private]=>
    string(13) "resource_name"
    ["resource_line_offset":"V8\ScriptOrigin":private]=>
    int(0)
    ["resource_column_offset":"V8\ScriptOrigin":private]=>
    int(0)
    ["options":"V8\ScriptOrigin":private]=>
    object(V8\ScriptOriginOptions)#5 (4) {
      ["is_shared_cross_origin":"V8\ScriptOriginOptions":private]=>
      bool(false)
      ["is_opaque":"V8\ScriptOriginOptions":private]=>
      bool(false)
      ["is_wasm":"V8\ScriptOriginOptions":private]=>
      bool(false)
      ["is_module":"V8\ScriptOriginOptions":private]=>
      bool(false)
    }
    ["script_id":"V8\ScriptOrigin":private]=>
    int(0)
    ["source_map_url":"V8\ScriptOrigin":private]=>
    string(0) ""
  }
  ["source_line":"V8\Message":private]=>
  string(11) "source_line"
  ["resource_name":"V8\Message":private]=>
  string(13) "resource_name"
  ["stack_trace":"V8\Message":private]=>
  object(V8\StackTrace)#6 (2) {
    ["frames":"V8\StackTrace":private]=>
    array(0) {
    }
    ["as_array":"V8\StackTrace":private]=>
    object(V8\ArrayObject)#7 (2) {
      ["isolate":"V8\Value":private]=>
      object(V8\Isolate)#2 (5) {
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
      object(V8\Context)#3 (1) {
        ["isolate":"V8\Context":private]=>
        object(V8\Isolate)#2 (5) {
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


Test getters (default):
-----------------------
V8\Message::Get() matches expected 'message'
V8\Message::GetSourceLine() matches expected 'source_line'
V8\Message::GetScriptOrigin() result is instance of V8\ScriptOrigin
V8\Message::GetScriptResourceName() matches expected 'resource_name'
V8\Message::GetStackTrace() result is instance of V8\StackTrace
V8\Message::GetLineNumber() matches expected 0
V8\Message::GetStartPosition() matches expected -1
V8\Message::GetEndPosition() matches expected -1
V8\Message::GetStartColumn() matches expected 0
V8\Message::GetEndColumn() matches expected 0
V8\Message::IsSharedCrossOrigin() matches expected false
V8\Message::IsOpaque() matches expected false


Object representation:
----------------------
object(V8\Message)#9 (12) {
  ["message":"V8\Message":private]=>
  string(7) "message"
  ["script_origin":"V8\Message":private]=>
  object(V8\ScriptOrigin)#4 (6) {
    ["resource_name":"V8\ScriptOrigin":private]=>
    string(13) "resource_name"
    ["resource_line_offset":"V8\ScriptOrigin":private]=>
    int(0)
    ["resource_column_offset":"V8\ScriptOrigin":private]=>
    int(0)
    ["options":"V8\ScriptOrigin":private]=>
    object(V8\ScriptOriginOptions)#5 (4) {
      ["is_shared_cross_origin":"V8\ScriptOriginOptions":private]=>
      bool(false)
      ["is_opaque":"V8\ScriptOriginOptions":private]=>
      bool(false)
      ["is_wasm":"V8\ScriptOriginOptions":private]=>
      bool(false)
      ["is_module":"V8\ScriptOriginOptions":private]=>
      bool(false)
    }
    ["script_id":"V8\ScriptOrigin":private]=>
    int(0)
    ["source_map_url":"V8\ScriptOrigin":private]=>
    string(0) ""
  }
  ["source_line":"V8\Message":private]=>
  string(11) "source_line"
  ["resource_name":"V8\Message":private]=>
  string(13) "resource_name"
  ["stack_trace":"V8\Message":private]=>
  object(V8\StackTrace)#6 (2) {
    ["frames":"V8\StackTrace":private]=>
    array(0) {
    }
    ["as_array":"V8\StackTrace":private]=>
    object(V8\ArrayObject)#7 (2) {
      ["isolate":"V8\Value":private]=>
      object(V8\Isolate)#2 (5) {
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
      object(V8\Context)#3 (1) {
        ["isolate":"V8\Context":private]=>
        object(V8\Isolate)#2 (5) {
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
  }
  ["line_number":"V8\Message":private]=>
  int(1)
  ["start_position":"V8\Message":private]=>
  int(2)
  ["end_position":"V8\Message":private]=>
  int(3)
  ["start_column":"V8\Message":private]=>
  int(4)
  ["end_column":"V8\Message":private]=>
  int(5)
  ["is_shared_cross_origin":"V8\Message":private]=>
  bool(true)
  ["is_opaque":"V8\Message":private]=>
  bool(true)
}


Test getters:
-------------
V8\Message::Get() matches expected 'message'
V8\Message::GetSourceLine() matches expected 'source_line'
V8\Message::GetScriptOrigin() result is instance of V8\ScriptOrigin
V8\Message::GetScriptResourceName() matches expected 'resource_name'
V8\Message::GetStackTrace() result is instance of V8\StackTrace
V8\Message::GetLineNumber() matches expected 1
V8\Message::GetStartPosition() matches expected 2
V8\Message::GetEndPosition() matches expected 3
V8\Message::GetStartColumn() matches expected 4
V8\Message::GetEndColumn() matches expected 5
V8\Message::IsSharedCrossOrigin() matches expected true
V8\Message::IsOpaque() matches expected true
