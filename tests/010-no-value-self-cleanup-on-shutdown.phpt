--TEST--
Built-in enum classes
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--ENV--
HOME=/tmp/we-need-home-env-var-set-to-load-valgrindrc
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

$enums = [
    new V8\StringValue(new V8\Isolate()),
];

foreach ($enums as $enum) {
    $rc = new ReflectionClass($enum);
}

echo 'done', PHP_EOL;

?>
--EXPECT--
done
