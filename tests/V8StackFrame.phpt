--TEST--
v8\StackFrame
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

$obj = new v8\StackFrame();

$helper->header('Object representation (default)');
$helper->dump($obj);
$helper->space();

$helper->header('Test getters (default)');
$helper->method_matches_with_output($obj, 'GetLineNumber', v8\Message::kNoLineNumberInfo);
$helper->method_matches_with_output($obj, 'GetColumn', v8\Message::kNoColumnInfo);
$helper->method_matches_with_output($obj, 'GetScriptId', v8\Message::kNoScriptIdInfo);
$helper->method_matches_with_output($obj, 'GetScriptName', '');
$helper->method_matches_with_output($obj, 'GetScriptNameOrSourceURL', '');
$helper->method_matches_with_output($obj, 'GetFunctionName', '');
$helper->method_matches_with_output($obj, 'IsEval', false);
$helper->method_matches_with_output($obj, 'IsConstructor', false);
$helper->space();


$obj = new v8\StackFrame(1, 2, 3, 'script_name', 'script_name_or_source_url', 'function_name', true, true);


$helper->header('Object representation');
$helper->dump($obj);
$helper->space();

$helper->header('Test getters');
$helper->method_matches_with_output($obj, 'GetLineNumber', 1);
$helper->method_matches_with_output($obj, 'GetColumn', 2);
$helper->method_matches_with_output($obj, 'GetScriptId', 3);
$helper->method_matches_with_output($obj, 'GetScriptName', 'script_name');
$helper->method_matches_with_output($obj, 'GetScriptNameOrSourceURL', 'script_name_or_source_url');
$helper->method_matches_with_output($obj, 'GetFunctionName', 'function_name');
$helper->method_matches_with_output($obj, 'IsEval', true);
$helper->method_matches_with_output($obj, 'IsConstructor', true);
$helper->space();

?>
--EXPECT--
Object representation (default):
--------------------------------
object(v8\StackFrame)#2 (8) {
  ["line_number":"v8\StackFrame":private]=>
  int(0)
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


Test getters (default):
-----------------------
v8\StackFrame::GetLineNumber() matches expected 0
v8\StackFrame::GetColumn() matches expected 0
v8\StackFrame::GetScriptId() matches expected 0
v8\StackFrame::GetScriptName() matches expected ''
v8\StackFrame::GetScriptNameOrSourceURL() matches expected ''
v8\StackFrame::GetFunctionName() matches expected ''
v8\StackFrame::IsEval() matches expected false
v8\StackFrame::IsConstructor() matches expected false


Object representation:
----------------------
object(v8\StackFrame)#3 (8) {
  ["line_number":"v8\StackFrame":private]=>
  int(1)
  ["column":"v8\StackFrame":private]=>
  int(2)
  ["script_id":"v8\StackFrame":private]=>
  int(3)
  ["script_name":"v8\StackFrame":private]=>
  string(11) "script_name"
  ["script_name_or_source_url":"v8\StackFrame":private]=>
  string(25) "script_name_or_source_url"
  ["function_name":"v8\StackFrame":private]=>
  string(13) "function_name"
  ["is_eval":"v8\StackFrame":private]=>
  bool(true)
  ["is_constructor":"v8\StackFrame":private]=>
  bool(true)
}


Test getters:
-------------
v8\StackFrame::GetLineNumber() matches expected 1
v8\StackFrame::GetColumn() matches expected 2
v8\StackFrame::GetScriptId() matches expected 3
v8\StackFrame::GetScriptName() matches expected 'script_name'
v8\StackFrame::GetScriptNameOrSourceURL() matches expected 'script_name_or_source_url'
v8\StackFrame::GetFunctionName() matches expected 'function_name'
v8\StackFrame::IsEval() matches expected true
v8\StackFrame::IsConstructor() matches expected true
