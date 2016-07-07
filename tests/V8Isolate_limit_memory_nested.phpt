--TEST--
v8\Isolate - nested memory limit exceptions
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.tracking_dtors.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

// Tests:

$isolate = new v8\Isolate();
$extensions = [];
$global_template = new v8\ObjectTemplate($isolate);
$global_template->Set(new \v8\StringValue($isolate, 'print'), $v8_helper->getPrintFunctionTemplate($isolate), \v8\PropertyAttribute::DontDelete);

$context = new v8\Context($isolate, $extensions, $global_template);

$func = new v8\FunctionObject($context, function (\v8\FunctionCallbackInfo $info) use (&$helper) {
    if (!$info->Arguments()) {
        $isolate = $info->GetIsolate();

        $source = '
            var str = " ".repeat(1024); // 1kb
            var blob = [];
            while(true) {
              blob.push(str);
              //print(blob.length, "\n");
            }
        ';

        $script = new v8\Script($info->GetContext(), new \v8\StringValue($isolate, $source), new \v8\ScriptOrigin('wait_for_termination.js'));

        try {
            $script->Run();
        } catch (\v8\Exceptions\MemoryLimitException $e) {
            $helper->exception_export($e);
            echo 'wait loop terminated', PHP_EOL;
            $helper->line();
        }

        return;
    }

    $fnc= $info->Arguments()[0];

    try {
        $fnc->Call($info->GetContext(), $fnc);
    } catch (\v8\Exceptions\MemoryLimitException $e) {
        $helper->exception_export($e);
        echo 'function call terminated', PHP_EOL;
        $helper->line();
    }
});


$func->SetName(new \v8\StringValue($isolate, 'custom_name'));


$context->GlobalObject()->Set($context, new \v8\StringValue($isolate, 'test'), $func);

$source = 'test(test); delete print; "Script done"';
$file_name = 'test.js';


$script = new v8\Script($context, new \v8\StringValue($isolate, $source), new \v8\ScriptOrigin($file_name));

$isolate->SetMemoryLimit(1024 * 1024 * 10);
$helper->dump($isolate);
$helper->line();

$t = microtime(true);
try {
    $script->Run();
} catch(\v8\Exceptions\MemoryLimitException $e) {
    $helper->exception_export($e);
    echo 'script execution terminated', PHP_EOL;
}

$helper->line();
$helper->dump($isolate);
?>
--EXPECT--
object(v8\Isolate)#3 (5) {
  ["snapshot":"v8\Isolate":private]=>
  NULL
  ["time_limit":"v8\Isolate":private]=>
  float(0)
  ["time_limit_hit":"v8\Isolate":private]=>
  bool(false)
  ["memory_limit":"v8\Isolate":private]=>
  int(10485760)
  ["memory_limit_hit":"v8\Isolate":private]=>
  bool(false)
}

v8\Exceptions\MemoryLimitException: Memory limit exceeded
wait loop terminated

v8\Exceptions\MemoryLimitException: Memory limit exceeded
function call terminated

v8\Exceptions\MemoryLimitException: Memory limit exceeded
script execution terminated

object(v8\Isolate)#3 (5) {
  ["snapshot":"v8\Isolate":private]=>
  NULL
  ["time_limit":"v8\Isolate":private]=>
  float(0)
  ["time_limit_hit":"v8\Isolate":private]=>
  bool(false)
  ["memory_limit":"v8\Isolate":private]=>
  int(10485760)
  ["memory_limit_hit":"v8\Isolate":private]=>
  bool(true)
}
