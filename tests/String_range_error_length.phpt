--TEST--
V8\String - RangeError: Invalid string length
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

// Tests:

$isolate = new V8\Isolate();
$context = new V8\Context($isolate);
$v8_helper->injectConsoleLog($context);

$source    = '
    var str = " ".repeat(1024); // 1kb
    var blob = "";
    while(true) {
      blob += str;
      //console.log(blob.length, "\n");
    }
';

$t = microtime(true);
try {
  $res = $v8_helper->CompileRun($context, $source);
} catch(\V8\Exceptions\TryCatchException $e) {
  $helper->exception_export($e);
}
?>
--EXPECT--
V8\Exceptions\TryCatchException: RangeError: Invalid string length
