--TEST--
v8\String - RangeError: Invalid string length
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

// Tests:

$isolate = new v8\Isolate();
$extensions = [];
$global_template = new v8\ObjectTemplate($isolate);
$global_template->Set(new \v8\StringValue($isolate, 'print'), $v8_helper->getPrintFunctionTemplate($isolate), \v8\PropertyAttribute::DontDelete);

$context = new v8\Context($isolate, $extensions, $global_template);


$source    = '
    var str = " ".repeat(1024); // 1kb
    var blob = "";
    while(true) {
      blob += str;
      //print(blob.length, "\n");
    }
';
$file_name = 'test.js';

$script = new v8\Script($context, new \v8\StringValue($isolate, $source), new \v8\ScriptOrigin($file_name));

$t = microtime(true);
try {
  $res = $script->Run();
} catch(\v8\Exceptions\TryCatchException $e) {
  $helper->exception_export($e);
}
?>
--EXPECT--
v8\Exceptions\TryCatchException: RangeError: Invalid string length
