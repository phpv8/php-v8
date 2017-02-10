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
$global_template = new V8\ObjectTemplate($isolate);
$global_template->Set(new \V8\StringValue($isolate, 'print'), $v8_helper->getPrintFunctionTemplate($isolate), \V8\PropertyAttribute::DontDelete);

$context = new V8\Context($isolate, $global_template);


$source    = '
    var str = " ".repeat(1024); // 1kb
    var blob = "";
    while(true) {
      blob += str;
      //print(blob.length, "\n");
    }
';
$file_name = 'test.js';

$script = new V8\Script($context, new \V8\StringValue($isolate, $source), new \V8\ScriptOrigin($file_name));

$t = microtime(true);
try {
  $res = $script->Run($context);
} catch(\V8\Exceptions\TryCatchException $e) {
  $helper->exception_export($e);
}
?>
--EXPECT--
V8\Exceptions\TryCatchException: RangeError: Invalid string length
