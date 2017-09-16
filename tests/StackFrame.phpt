--TEST--
V8\StackFrame
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--ENV--
HOME=/tmp/we-need-home-env-var-set-to-load-valgrindrc
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

$obj = new V8\StackFrame();

$helper->header('Object representation (default)');
$helper->dump($obj);
$helper->space();

$helper->header('Test getters (default)');
$helper->method_matches_with_output($obj, 'getLineNumber', null);
$helper->method_matches_with_output($obj, 'getColumn', null);
$helper->method_matches_with_output($obj, 'getScriptId', null);
$helper->method_matches_with_output($obj, 'getScriptName', '');
$helper->method_matches_with_output($obj, 'getScriptNameOrSourceURL', '');
$helper->method_matches_with_output($obj, 'getFunctionName', '');
$helper->method_matches_with_output($obj, 'isEval', false);
$helper->method_matches_with_output($obj, 'isConstructor', false);
$helper->method_matches_with_output($obj, 'isWasm', false);
$helper->space();


$obj = new V8\StackFrame(1, 2, 3, 'script_name', 'script_name_or_source_url', 'function_name', true, true, true);


$helper->header('Object representation');
$helper->dump($obj);
$helper->space();

$helper->header('Test getters');
$helper->method_matches_with_output($obj, 'getLineNumber', 1);
$helper->method_matches_with_output($obj, 'getColumn', 2);
$helper->method_matches_with_output($obj, 'getScriptId', 3);
$helper->method_matches_with_output($obj, 'getScriptName', 'script_name');
$helper->method_matches_with_output($obj, 'getScriptNameOrSourceURL', 'script_name_or_source_url');
$helper->method_matches_with_output($obj, 'getFunctionName', 'function_name');
$helper->method_matches_with_output($obj, 'isEval', true);
$helper->method_matches_with_output($obj, 'isConstructor', true);
$helper->method_matches_with_output($obj, 'isWasm', true);
$helper->space();

?>
--EXPECT--
Object representation (default):
--------------------------------
object(V8\StackFrame)#2 (9) {
  ["line_number":"V8\StackFrame":private]=>
  NULL
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


Test getters (default):
-----------------------
V8\StackFrame::getLineNumber() matches expected NULL
V8\StackFrame::getColumn() matches expected NULL
V8\StackFrame::getScriptId() matches expected NULL
V8\StackFrame::getScriptName() matches expected ''
V8\StackFrame::getScriptNameOrSourceURL() matches expected ''
V8\StackFrame::getFunctionName() matches expected ''
V8\StackFrame::isEval() matches expected false
V8\StackFrame::isConstructor() matches expected false
V8\StackFrame::isWasm() matches expected false


Object representation:
----------------------
object(V8\StackFrame)#3 (9) {
  ["line_number":"V8\StackFrame":private]=>
  int(1)
  ["column":"V8\StackFrame":private]=>
  int(2)
  ["script_id":"V8\StackFrame":private]=>
  int(3)
  ["script_name":"V8\StackFrame":private]=>
  string(11) "script_name"
  ["script_name_or_source_url":"V8\StackFrame":private]=>
  string(25) "script_name_or_source_url"
  ["function_name":"V8\StackFrame":private]=>
  string(13) "function_name"
  ["is_eval":"V8\StackFrame":private]=>
  bool(true)
  ["is_constructor":"V8\StackFrame":private]=>
  bool(true)
  ["is_wasm":"V8\StackFrame":private]=>
  bool(true)
}


Test getters:
-------------
V8\StackFrame::getLineNumber() matches expected 1
V8\StackFrame::getColumn() matches expected 2
V8\StackFrame::getScriptId() matches expected 3
V8\StackFrame::getScriptName() matches expected 'script_name'
V8\StackFrame::getScriptNameOrSourceURL() matches expected 'script_name_or_source_url'
V8\StackFrame::getFunctionName() matches expected 'function_name'
V8\StackFrame::isEval() matches expected true
V8\StackFrame::isConstructor() matches expected true
V8\StackFrame::isWasm() matches expected true
