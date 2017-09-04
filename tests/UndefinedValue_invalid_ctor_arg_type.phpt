--TEST--
V8\UndefinedValue::__construct() - with invalid arg type
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--ENV--
HOME=/tmp/we-need-home-env-var-set-to-load-valgrindrc
--FILE--
<?php
try {
    $function_template = new \V8\UndefinedValue(new stdClass());
} catch (TypeError $e) {
    echo get_class($e), ': ', $e->getMessage();
}
?>
--EXPECT--
TypeError: Argument 1 passed to V8\UndefinedValue::__construct() must be an instance of V8\Isolate, instance of stdClass given
