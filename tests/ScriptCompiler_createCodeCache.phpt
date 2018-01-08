--TEST--
V8\ScriptCompiler::createCodeCache()
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


$source_string = new V8\StringValue($isolate, "function f() { return 'abc'; }; f() + 'def'");
$source        = new \V8\ScriptCompiler\Source($source_string);
$unbound       = V8\ScriptCompiler::compileUnboundScript($context, $source);

$source_string_other = new V8\StringValue($isolate, "other; bad-'");


$cache_data = V8\ScriptCompiler::createCodeCache($unbound, $source_string);

$helper->header('Testing with the source string is the same as used for original');

$helper->assert("Cache data created", null !== $cache_data);
$helper->assert("Cache data is not empty", strlen($cache_data->getData()) > 1);
$helper->assert("Cache data is not rejected", !$cache_data->isRejected());
$helper->line();

{
    $helper->header('Test consuming code cache');

    $source = new \V8\ScriptCompiler\Source($source_string, null, $cache_data);
    $helper->assert('Source cache data is set', $source->getCachedData() != null);
    $test_unbound = V8\ScriptCompiler::compileUnboundScript($context, $source, V8\ScriptCompiler::OPTION_CONSUME_CODE_CACHE);
    $helper->assert('Source cache data is still set', $source->getCachedData() != null);
    $helper->assert('Source cache data is not rejected', $source->getCachedData()->isRejected() === false);

    $helper->pretty_dump('Script result', $test_unbound->bindToContext($context)->run($context)->toString($context)->value());
}


$helper->space();

$helper->header('Testing with the source string different from original');

$cache_data = V8\ScriptCompiler::createCodeCache($unbound, $source_string_other);

$helper->assert("Cache data created", null !== $cache_data);
$helper->assert("Cache data is not empty", strlen($cache_data->getData()) > 1);
$helper->assert("Cache data is not rejected", !$cache_data->isRejected());
$helper->line();

{
    $helper->header('Test consuming code cache');

    $source = new \V8\ScriptCompiler\Source($source_string, null, $cache_data);
    $helper->assert('Source cache data is set', $source->getCachedData() != null);
    $test_unbound = V8\ScriptCompiler::compileUnboundScript($context, $source, V8\ScriptCompiler::OPTION_CONSUME_CODE_CACHE);
    $helper->assert('Source cache data is still set', $source->getCachedData() != null);
    $helper->assert('Source cache data is not rejected', $source->getCachedData()->isRejected() === false);

    $helper->pretty_dump('Script result', $test_unbound->bindToContext($context)->run($context)->toString($context)->value());
}



?>
--EXPECT--
Testing with the source string is the same as used for original:
----------------------------------------------------------------
Cache data created: ok
Cache data is not empty: ok
Cache data is not rejected: ok

Test consuming code cache:
--------------------------
Source cache data is set: ok
Source cache data is still set: ok
Source cache data is not rejected: ok
Script result: string(6) "abcdef"


Testing with the source string different from original:
-------------------------------------------------------
Cache data created: ok
Cache data is not empty: ok
Cache data is not rejected: ok

Test consuming code cache:
--------------------------
Source cache data is set: ok
Source cache data is still set: ok
Source cache data is not rejected: ok
Script result: string(6) "abcdef"
