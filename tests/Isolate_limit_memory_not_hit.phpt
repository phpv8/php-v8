--TEST--
V8\Isolate - time memory not hit
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

$isolate = new V8\Isolate();
$context = new V8\Context($isolate);
$v8_helper->injectConsoleLog($context);

$source = '
console.log("start"); 
console.log("end");
"Script done"';
$file_name = 'test.js';


$script = new V8\Script($context, new \V8\StringValue($isolate, $source), new \V8\ScriptOrigin($file_name));

$memory_limit = 1024 * 1024 * 10;
$helper->assert('Memory limit accessor report no hit', false === $isolate->isMemoryLimitHit());
$helper->assert('Get memory limit default value is zero', 0 === $isolate->getMemoryLimit());
$isolate->setMemoryLimit($memory_limit);
$helper->assert('Get memory limit returns valid value', $memory_limit === $isolate->getMemoryLimit());

$helper->dump($isolate);
$helper->line();

$t = microtime(true);
try {
    $script->run($context);
} finally {
    $helper->line();
    $t = microtime(true) - $t;
    $helper->dump(round($t, 9));
    $helper->assert('Script execution time is less than 0.5 sec', $t < 0.5);
}

$helper->assert('Get memory limit returns valid value', $memory_limit === $isolate->getMemoryLimit());
$helper->assert('Memory limit accessor report not hit', false === $isolate->isMemoryLimitHit());

$helper->line();
$helper->dump($isolate);

// EXPECTF: ---/float\(0\..+\)/
// EXPECTF: +++float(0.%d)
?>
--EXPECTF--
Memory limit accessor report no hit: ok
Get memory limit default value is zero: ok
Get memory limit returns valid value: ok
object(V8\Isolate)#3 (0) {
}

start
end

float(0.%d)
Script execution time is less than 0.5 sec: ok
Get memory limit returns valid value: ok
Memory limit accessor report not hit: ok

object(V8\Isolate)#3 (0) {
}
