--TEST--
V8\ScriptCompiler\CachedData
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

// Tests:

$value = new \V8\ScriptCompiler\CachedData('');

$helper->header('Object representation');
$helper->dump($value);
$helper->space();

$helper->header('Methods');

$data = [
    '',
    'test foo bar baz',
    random_bytes(16),
    random_bytes(31),
    'Юникод',
    '萬國碼、國際碼、統一碼、單一碼',
    'یوونیکۆد',
];

foreach ($data as $d) {
    $value = new \V8\ScriptCompiler\CachedData($d);
    $helper->method_matches_with_dump($value, 'getData', $d);
}

$helper->method_dump($value, 'isRejected');



?>
--EXPECTF--
Object representation:
----------------------
object(V8\ScriptCompiler\CachedData)#3 (0) {
}


Methods:
--------
V8\ScriptCompiler\CachedData::getData() matches expected string(0) ""
V8\ScriptCompiler\CachedData::getData() matches expected string(16) "test foo bar baz"
V8\ScriptCompiler\CachedData::getData() matches expected string(16) "%r.{16}%r"
V8\ScriptCompiler\CachedData::getData() matches expected string(31) "%r.{31}%r"
V8\ScriptCompiler\CachedData::getData() matches expected string(12) "Юникод"
V8\ScriptCompiler\CachedData::getData() matches expected string(45) "萬國碼、國際碼、統一碼、單一碼"
V8\ScriptCompiler\CachedData::getData() matches expected string(16) "یوونیکۆد"
V8\ScriptCompiler\CachedData::isRejected() false
