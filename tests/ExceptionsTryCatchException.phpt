--TEST--
V8\Exceptions\TryCatchException
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';


$isolate = new \V8\Isolate();
$context = new \V8\Context($isolate);
$try_catch = new \V8\TryCatch($isolate, $context);

$value = new \V8\Exceptions\TryCatchException($isolate, $context, $try_catch);


$helper->header('Object representation');
$helper->dump($value);
$helper->space();

$helper->header('Accessors');
$helper->method_matches($value, 'getIsolate', $isolate);
$helper->method_matches($value, 'getContext', $context);
$helper->method_matches($value, 'getTryCatch', $try_catch);
$helper->space();

// EXPECTF: ---/string\(.+\) ".+\/V8ExceptionsTryCatchException\.php"/
// EXPECTF: +++string(%d) "%s/V8ExceptionsTryCatchException.php"
?>
--EXPECTF--
Object representation:
----------------------
object(V8\Exceptions\TryCatchException)#5 (10) {
  ["message":protected]=>
  string(0) ""
  ["string":"Exception":private]=>
  string(0) ""
  ["code":protected]=>
  int(0)
  ["file":protected]=>
  string(%d) "%s/ExceptionsTryCatchException.php"
  ["line":protected]=>
  int(11)
  ["trace":"Exception":private]=>
  array(0) {
  }
  ["previous":"Exception":private]=>
  NULL
  ["isolate":"V8\Exceptions\TryCatchException":private]=>
  object(V8\Isolate)#2 (0) {
  }
  ["context":"V8\Exceptions\TryCatchException":private]=>
  object(V8\Context)#3 (1) {
    ["isolate":"V8\Context":private]=>
    object(V8\Isolate)#2 (0) {
    }
  }
  ["try_catch":"V8\Exceptions\TryCatchException":private]=>
  object(V8\TryCatch)#4 (8) {
    ["isolate":"V8\TryCatch":private]=>
    object(V8\Isolate)#2 (0) {
    }
    ["context":"V8\TryCatch":private]=>
    object(V8\Context)#3 (1) {
      ["isolate":"V8\Context":private]=>
      object(V8\Isolate)#2 (0) {
      }
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
    ["external_exception":"V8\TryCatch":private]=>
    NULL
  }
}


Accessors:
----------
V8\Exceptions\TryCatchException::getIsolate() matches expected value
V8\Exceptions\TryCatchException::getContext() matches expected value
V8\Exceptions\TryCatchException::getTryCatch() matches expected value
