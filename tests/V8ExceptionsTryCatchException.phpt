--TEST--
v8\Exceptions\TryCatchException
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';


$isolate = new \v8\Isolate();
$context = new \v8\Context($isolate);
$try_catch = new \v8\TryCatch($isolate, $context);

$value = new \v8\Exceptions\TryCatchException($isolate, $context, $try_catch);


$helper->header('Object representation');
$helper->dump($value);
$helper->space();

$helper->header('Accessors');
$helper->method_matches($value, 'GetIsolate', $isolate);
$helper->method_matches($value, 'GetContext', $context);
$helper->method_matches($value, 'GetTryCatch', $try_catch);
$helper->space();

?>
--EXPECTF--
Object representation:
----------------------
object(v8\Exceptions\TryCatchException)#5 (10) {
  ["message":protected]=>
  string(0) ""
  ["string":"Exception":private]=>
  string(0) ""
  ["code":protected]=>
  int(0)
  ["file":protected]=>
  string(%d) "%s/V8ExceptionsTryCatchException.php"
  ["line":protected]=>
  int(11)
  ["trace":"Exception":private]=>
  array(0) {
  }
  ["previous":"Exception":private]=>
  NULL
  ["isolate":"v8\Exceptions\TryCatchException":private]=>
  object(v8\Isolate)#2 (1) {
    ["snapshot":"v8\Isolate":private]=>
    NULL
  }
  ["context":"v8\Exceptions\TryCatchException":private]=>
  object(v8\Context)#3 (4) {
    ["isolate":"v8\Context":private]=>
    object(v8\Isolate)#2 (1) {
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
  ["try_catch":"v8\Exceptions\TryCatchException":private]=>
  object(v8\TryCatch)#4 (7) {
    ["isolate":"v8\TryCatch":private]=>
    object(v8\Isolate)#2 (1) {
      ["snapshot":"v8\Isolate":private]=>
      NULL
    }
    ["context":"v8\TryCatch":private]=>
    object(v8\Context)#3 (4) {
      ["isolate":"v8\Context":private]=>
      object(v8\Isolate)#2 (1) {
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
}


Accessors:
----------
v8\Exceptions\TryCatchException::GetIsolate() matches expected value
v8\Exceptions\TryCatchException::GetContext() matches expected value
v8\Exceptions\TryCatchException::GetTryCatch() matches expected value
