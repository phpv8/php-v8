--TEST--
V8\Isolate::within
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';

$isolate = new V8\Isolate();


$fnc = function (V8\Isolate $i) use ($helper, $isolate) {
    $helper->assert('Same isolate passed as first argument', $i, $isolate);
};

$isolate->within($fnc);
$fnc= null;

$res_expected = new stdClass();

$res = $isolate->within(function (V8\Isolate $i) use ($isolate, $helper, $res_expected) {
    return $res_expected;
});

$helper->assert('Enclosed function result returned', $res, $res_expected);


try {
    $isolate->within(function (V8\Isolate $i) {
        throw new RuntimeException('test');
    });
} catch (Throwable $e) {
    $helper->exception_export($e);
}

?>
--EXPECT--
Same isolate passed as first argument: ok
Enclosed function result returned: ok
RuntimeException: test
