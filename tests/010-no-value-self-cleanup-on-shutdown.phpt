--TEST--
Built-in enum classes
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
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
