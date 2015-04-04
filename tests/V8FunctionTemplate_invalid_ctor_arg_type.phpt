--TEST--
v8\FunctionTemplate::__construct() - with invalid arg type
--SKIPIF--
<?php if (!extension_loaded("v8")) {
    print "skip";
} ?>
--FILE--
<?php
try {
    $function_template = new \v8\FunctionTemplate(new stdClass());
} catch (TypeError $e) {
    echo get_class($e), ': ', $e->getMessage();
}
?>
--EXPECT--
TypeError: Argument 1 passed to v8\FunctionTemplate::__construct() must be an instance of v8\Isolate, instance of stdClass given