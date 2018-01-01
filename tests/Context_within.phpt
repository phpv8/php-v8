--TEST--
V8\Context::within
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';

$isolate = new V8\Isolate();
$context = new V8\Context($isolate);


$fnc = function (V8\Isolate $i, V8\Context $c) use ($helper, $isolate, $context) {
    $helper->assert('Same isolate passed as first argument', $i, $isolate);
    $helper->assert('Same context passed as second argument', $c, $context);
};

$context->within($fnc);
$fnc= null;

$res_expected = new stdClass();

$res = $context->within(function (V8\Isolate $i) use ($isolate, $helper, $res_expected) {
    return $res_expected;
});

$helper->assert('Enclosed function result returned', $res, $res_expected);


try {
    $context->within(function (V8\Isolate $i) {
        throw new RuntimeException('test');
    });
} catch (Throwable $e) {
    $helper->exception_export($e);
}

?>
--EXPECT--
Same isolate passed as first argument: ok
Same context passed as second argument: ok
Enclosed function result returned: ok
RuntimeException: test
