--TEST--
V8\ObjectTemplate::setHandlerForNamedProperty()
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--ENV--
HOME=/tmp/we-need-home-env-var-set-to-load-valgrindrc
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

$isolate = new \V8\Isolate();
$global_template = new V8\ObjectTemplate($isolate);

$foo = 100;

$getter = function (\V8\NameValue $name, \V8\PropertyCallbackInfo $info) use (&$foo) {
    echo 'I am named getter for ', $name->toString($info->getContext())->value(), '!', PHP_EOL;

    if ('bar' === $name) {
        $info->getReturnValue()->setUndefined();
        return;
    }

    $info->getReturnValue()->set(new \V8\NumberValue($info->getIsolate(), $foo));
};

$setter = function (\V8\NameValue $name, \V8\Value $value, \V8\PropertyCallbackInfo $info) use (&$foo) {
    echo 'I am named setter for ', $name->toString($info->getContext())->value(), '!', PHP_EOL;

    $foo = $value->toNumber($info->getContext())->value() / 2;
};

$query = function (\V8\NameValue$name, \V8\PropertyCallbackInfo $info) use (&$foo) {
    echo 'I am named query for ', $name->toString($info->getContext())->value(), '!', PHP_EOL;
    $info->getReturnValue()->setInteger(\V8\PropertyAttribute::NONE);
};

$deleter = function (\V8\NameValue$name, \V8\PropertyCallbackInfo $info) use (&$foo) {
    echo 'I am named deleter for ', $name->toString($info->getContext())->value(), '!', PHP_EOL;
//    $info->getReturnValue()->set(true);
};

$enumerator = function (\V8\PropertyCallbackInfo $info) use (&$foo) {
    echo 'I am named enumerator!', PHP_EOL;

    $ctxt = $info->getContext();
    $arr = new \V8\ArrayObject($ctxt);

    for ($i =0, $j = 'test-a'; $i < 10; $i ++, $j++) {
        $arr->set($ctxt, new \V8\StringValue($info->getIsolate(), $i), new \V8\StringValue($info->getIsolate(), $j));
    }
    $info->getReturnValue()->set($arr);
};


$test_obj_tpl = new \V8\ObjectTemplate($isolate);
$test_obj_tpl->setHandlerForNamedProperty(new \V8\NamedPropertyHandlerConfiguration($getter, $setter, $query, $deleter, $enumerator));

$global_template->set(new \V8\StringValue($isolate, 'test'), $test_obj_tpl);

$context = new V8\Context($isolate, $global_template);
$v8_helper->injectConsoleLog($context);


$source    = '
console.log("\"foo\" in test: ", "foo" in test);
console.log("\"bar\" in test: ", "bar" in test);

console.log("test.foo: ", test.foo);
console.log("test.foo = 42: ", test.foo = 42);
console.log("test.foo: ", test.foo);

console.log("delete test.foo: ", delete test.foo);
console.log("\"foo\" in test: ", "foo" in test);

for (i in test) {
    console.log("test["+i+"]: ", test[i]);
}
';
$file_name = 'test.js';

$script = new V8\Script($context, new \V8\StringValue($isolate, $source), new \V8\ScriptOrigin($file_name));
$res = $script->run($context);

?>
--EXPECT--
I am named query for foo!
"foo" in test: true
I am named query for bar!
"bar" in test: true
I am named getter for foo!
test.foo: 100
I am named setter for foo!
test.foo = 42: 42
I am named getter for foo!
test.foo: 21
I am named deleter for foo!
delete test.foo: true
I am named query for foo!
"foo" in test: true
I am named enumerator!
I am named query for test-a!
I am named getter for test-a!
test[test-a]: 21
I am named query for test-b!
I am named getter for test-b!
test[test-b]: 21
I am named query for test-c!
I am named getter for test-c!
test[test-c]: 21
I am named query for test-d!
I am named getter for test-d!
test[test-d]: 21
I am named query for test-e!
I am named getter for test-e!
test[test-e]: 21
I am named query for test-f!
I am named getter for test-f!
test[test-f]: 21
I am named query for test-g!
I am named getter for test-g!
test[test-g]: 21
I am named query for test-h!
I am named getter for test-h!
test[test-h]: 21
I am named query for test-i!
I am named getter for test-i!
test[test-i]: 21
I am named query for test-j!
I am named getter for test-j!
test[test-j]: 21
