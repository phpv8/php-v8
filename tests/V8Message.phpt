--TEST--
v8\Message
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

$isolate = new \v8\Isolate();
$context = new \v8\Context($isolate);

$origin = new \v8\ScriptOrigin('resource_name');
$trace = new \v8\StackTrace([], new \v8\ArrayObject($context));

$obj = new v8\Message('message', 'source_line', $origin, 'resource_name', $trace);

$helper->header('Object representation (default)');
debug_zval_dump($obj);
$helper->space();

$helper->header('Test getters (default)');
$helper->method_matches_with_output($obj, 'Get', 'message');
$helper->method_matches_with_output($obj, 'GetSourceLine', 'source_line');
$helper->method_matches_instanceof($obj, 'GetScriptOrigin', v8\ScriptOrigin::class);
$helper->method_matches_with_output($obj, 'GetScriptResourceName', 'resource_name');
$helper->method_matches_instanceof($obj, 'GetStackTrace', v8\StackTrace::class);
$helper->method_matches_with_output($obj, 'GetLineNumber', 0);
$helper->method_matches_with_output($obj, 'GetStartPosition', -1);
$helper->method_matches_with_output($obj, 'GetEndPosition', -1);
$helper->method_matches_with_output($obj, 'GetStartColumn', 0);
$helper->method_matches_with_output($obj, 'GetEndColumn', 0);
$helper->method_matches_with_output($obj, 'IsSharedCrossOrigin', false);
$helper->method_matches_with_output($obj, 'IsOpaque', false);
$helper->space();


$obj = new v8\Message('message', 'source_line', $origin, 'resource_name', $trace, 1, 2, 3, 4, 5, true, true);

$helper->header('Object representation');
debug_zval_dump($obj);
$helper->space();

$helper->header('Test getters');
$helper->method_matches_with_output($obj, 'Get', 'message');
$helper->method_matches_with_output($obj, 'GetSourceLine', 'source_line');
$helper->method_matches_instanceof($obj, 'GetScriptOrigin', v8\ScriptOrigin::class);
$helper->method_matches_with_output($obj, 'GetScriptResourceName', 'resource_name');
$helper->method_matches_instanceof($obj, 'GetStackTrace', v8\StackTrace::class);
$helper->method_matches_with_output($obj, 'GetLineNumber', 1);
$helper->method_matches_with_output($obj, 'GetStartPosition', 2);
$helper->method_matches_with_output($obj, 'GetEndPosition', 3);
$helper->method_matches_with_output($obj, 'GetStartColumn', 4);
$helper->method_matches_with_output($obj, 'GetEndColumn', 5);
$helper->method_matches_with_output($obj, 'IsSharedCrossOrigin', true);
$helper->method_matches_with_output($obj, 'IsOpaque', true);
$helper->space();

?>
--EXPECTF--
Object representation (default):
--------------------------------
object(v8\Message)#8 (12) refcount(2){
  ["message":"v8\Message":private]=>
  string(7) "message" refcount(1)
  ["script_origin":"v8\Message":private]=>
  object(v8\ScriptOrigin)#4 (6) refcount(2){
    ["resource_name":"v8\ScriptOrigin":private]=>
    string(13) "resource_name" refcount(1)
    ["resource_line_offset":"v8\ScriptOrigin":private]=>
    int(0)
    ["resource_column_offset":"v8\ScriptOrigin":private]=>
    int(0)
    ["options":"v8\ScriptOrigin":private]=>
    object(v8\ScriptOriginOptions)#5 (3) refcount(1){
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
    string(0) "" refcount(%d)
  }
  ["source_line":"v8\Message":private]=>
  string(11) "source_line" refcount(1)
  ["resource_name":"v8\Message":private]=>
  string(13) "resource_name" refcount(1)
  ["stack_trace":"v8\Message":private]=>
  object(v8\StackTrace)#6 (2) refcount(2){
    ["frames":"v8\StackTrace":private]=>
    array(0) refcount(1){
    }
    ["as_array":"v8\StackTrace":private]=>
    object(v8\ArrayObject)#7 (2) refcount(1){
      ["isolate":"v8\Value":private]=>
      object(v8\Isolate)#2 (1) refcount(3){
        ["snapshot":"v8\Isolate":private]=>
        NULL
      }
      ["context":"v8\ObjectValue":private]=>
      object(v8\Context)#3 (4) refcount(2){
        ["isolate":"v8\Context":private]=>
        object(v8\Isolate)#2 (1) refcount(3){
          ["snapshot":"v8\Isolate":private]=>
          NULL
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


Test getters (default):
-----------------------
v8\Message::Get() matches expected 'message'
v8\Message::GetSourceLine() matches expected 'source_line'
v8\Message::GetScriptOrigin() result is instance of v8\ScriptOrigin
v8\Message::GetScriptResourceName() matches expected 'resource_name'
v8\Message::GetStackTrace() result is instance of v8\StackTrace
v8\Message::GetLineNumber() matches expected 0
v8\Message::GetStartPosition() matches expected -1
v8\Message::GetEndPosition() matches expected -1
v8\Message::GetStartColumn() matches expected 0
v8\Message::GetEndColumn() matches expected 0
v8\Message::IsSharedCrossOrigin() matches expected false
v8\Message::IsOpaque() matches expected false


Object representation:
----------------------
object(v8\Message)#9 (12) refcount(2){
  ["message":"v8\Message":private]=>
  string(7) "message" refcount(1)
  ["script_origin":"v8\Message":private]=>
  object(v8\ScriptOrigin)#4 (6) refcount(2){
    ["resource_name":"v8\ScriptOrigin":private]=>
    string(13) "resource_name" refcount(1)
    ["resource_line_offset":"v8\ScriptOrigin":private]=>
    int(0)
    ["resource_column_offset":"v8\ScriptOrigin":private]=>
    int(0)
    ["options":"v8\ScriptOrigin":private]=>
    object(v8\ScriptOriginOptions)#5 (3) refcount(1){
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
    string(0) "" refcount(%d)
  }
  ["source_line":"v8\Message":private]=>
  string(11) "source_line" refcount(1)
  ["resource_name":"v8\Message":private]=>
  string(13) "resource_name" refcount(1)
  ["stack_trace":"v8\Message":private]=>
  object(v8\StackTrace)#6 (2) refcount(2){
    ["frames":"v8\StackTrace":private]=>
    array(0) refcount(1){
    }
    ["as_array":"v8\StackTrace":private]=>
    object(v8\ArrayObject)#7 (2) refcount(1){
      ["isolate":"v8\Value":private]=>
      object(v8\Isolate)#2 (1) refcount(3){
        ["snapshot":"v8\Isolate":private]=>
        NULL
      }
      ["context":"v8\ObjectValue":private]=>
      object(v8\Context)#3 (4) refcount(2){
        ["isolate":"v8\Context":private]=>
        object(v8\Isolate)#2 (1) refcount(3){
          ["snapshot":"v8\Isolate":private]=>
          NULL
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
  int(1)
  ["start_position":"v8\Message":private]=>
  int(2)
  ["end_position":"v8\Message":private]=>
  int(3)
  ["start_column":"v8\Message":private]=>
  int(4)
  ["end_column":"v8\Message":private]=>
  int(5)
  ["is_shared_cross_origin":"v8\Message":private]=>
  bool(true)
  ["is_opaque":"v8\Message":private]=>
  bool(true)
}


Test getters:
-------------
v8\Message::Get() matches expected 'message'
v8\Message::GetSourceLine() matches expected 'source_line'
v8\Message::GetScriptOrigin() result is instance of v8\ScriptOrigin
v8\Message::GetScriptResourceName() matches expected 'resource_name'
v8\Message::GetStackTrace() result is instance of v8\StackTrace
v8\Message::GetLineNumber() matches expected 1
v8\Message::GetStartPosition() matches expected 2
v8\Message::GetEndPosition() matches expected 3
v8\Message::GetStartColumn() matches expected 4
v8\Message::GetEndColumn() matches expected 5
v8\Message::IsSharedCrossOrigin() matches expected true
v8\Message::IsOpaque() matches expected true
