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
$trace = new \V8\StackTrace([]);

$obj = new V8\Message('message', 'source_line', $origin, 'resource_name', $trace);

$helper->header('Object representation (default)');
$helper->dump($obj);
$helper->space();

$helper->header('Test getters (default)');
$helper->method_matches_with_output($obj, 'get', 'message');
$helper->method_matches_with_output($obj, 'getSourceLine', 'source_line');
$helper->method_matches_instanceof($obj, 'getScriptOrigin', V8\ScriptOrigin::class);
$helper->method_matches_with_output($obj, 'getScriptResourceName', 'resource_name');
$helper->method_matches_instanceof($obj, 'getStackTrace', V8\StackTrace::class);
$helper->method_matches_with_output($obj, 'getLineNumber', 0);
$helper->method_matches_with_output($obj, 'getStartPosition', -1);
$helper->method_matches_with_output($obj, 'getEndPosition', -1);
$helper->method_matches_with_output($obj, 'getStartColumn', 0);
$helper->method_matches_with_output($obj, 'getEndColumn', 0);
$helper->method_matches_with_output($obj, 'isSharedCrossOrigin', false);
$helper->method_matches_with_output($obj, 'isOpaque', false);
$helper->space();


$obj = new V8\Message('message', 'source_line', $origin, 'resource_name', $trace, 1, 2, 3, 4, 5, true, true);

$helper->header('Object representation');
$helper->dump($obj);
$helper->space();

$helper->header('Test getters');
$helper->method_matches_with_output($obj, 'get', 'message');
$helper->method_matches_with_output($obj, 'getSourceLine', 'source_line');
$helper->method_matches_instanceof($obj, 'getScriptOrigin', V8\ScriptOrigin::class);
$helper->method_matches_with_output($obj, 'getScriptResourceName', 'resource_name');
$helper->method_matches_instanceof($obj, 'getStackTrace', V8\StackTrace::class);
$helper->method_matches_with_output($obj, 'getLineNumber', 1);
$helper->method_matches_with_output($obj, 'getStartPosition', 2);
$helper->method_matches_with_output($obj, 'getEndPosition', 3);
$helper->method_matches_with_output($obj, 'getStartColumn', 4);
$helper->method_matches_with_output($obj, 'getEndColumn', 5);
$helper->method_matches_with_output($obj, 'isSharedCrossOrigin', true);
$helper->method_matches_with_output($obj, 'isOpaque', true);
$helper->space();

?>
--EXPECT--
Object representation (default):
--------------------------------
object(V8\Message)#7 (12) {
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
  object(V8\StackTrace)#6 (1) {
    ["frames":"V8\StackTrace":private]=>
    array(0) {
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
V8\Message::get() matches expected 'message'
V8\Message::getSourceLine() matches expected 'source_line'
V8\Message::getScriptOrigin() result is instance of V8\ScriptOrigin
V8\Message::getScriptResourceName() matches expected 'resource_name'
V8\Message::getStackTrace() result is instance of V8\StackTrace
V8\Message::getLineNumber() matches expected 0
V8\Message::getStartPosition() matches expected -1
V8\Message::getEndPosition() matches expected -1
V8\Message::getStartColumn() matches expected 0
V8\Message::getEndColumn() matches expected 0
V8\Message::isSharedCrossOrigin() matches expected false
V8\Message::isOpaque() matches expected false


Object representation:
----------------------
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
  object(V8\StackTrace)#6 (1) {
    ["frames":"V8\StackTrace":private]=>
    array(0) {
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
V8\Message::get() matches expected 'message'
V8\Message::getSourceLine() matches expected 'source_line'
V8\Message::getScriptOrigin() result is instance of V8\ScriptOrigin
V8\Message::getScriptResourceName() matches expected 'resource_name'
V8\Message::getStackTrace() result is instance of V8\StackTrace
V8\Message::getLineNumber() matches expected 1
V8\Message::getStartPosition() matches expected 2
V8\Message::getEndPosition() matches expected 3
V8\Message::getStartColumn() matches expected 4
V8\Message::getEndColumn() matches expected 5
V8\Message::isSharedCrossOrigin() matches expected true
V8\Message::isOpaque() matches expected true
