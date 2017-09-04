--TEST--
V8\ObjectTemplate::setHandlerFor{Named,Indexed}Property()
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

$allow_named = false;
$allow_indexed = false;

$foo = 100;

$getter = function (\V8\NameValue $name, \V8\PropertyCallbackInfo $info) use (&$foo) {
    echo 'I am named getter for ', $name->toString($info->getContext())->value(), '!', PHP_EOL;

    if ('bar' === $name) {
        $info->getReturnValue()->setUndefined();
        return;
    }

    $info->getReturnValue()->set(new \V8\NumberValue($info->getIsolate(), $foo));
};

$setter = function (\V8\NameValue$name, \V8\Value $value, \V8\PropertyCallbackInfo $info) use (&$foo) {
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

$enumerator = function (\V8\PropertyCallbackInfo $info) use (&$foo, &$allow_named) {
    echo 'I am named enumerator!', PHP_EOL;

    $ctxt = $info->getContext();
    $arr = new \V8\ArrayObject($ctxt);

    if ($allow_named) {
        for ($i =0, $j = 'test-a'; $i < 10; $i ++, $j++) {
            $arr->set($ctxt, new \V8\StringValue($info->getIsolate(), $i), new \V8\StringValue($info->getIsolate(), $j));
        }
    }
    $info->getReturnValue()->set($arr);
};


$test_obj_tpl = new \V8\ObjectTemplate($isolate);
$test_obj_tpl->setHandlerForNamedProperty(new \V8\NamedPropertyHandlerConfiguration($getter, $setter, $query, $deleter, $enumerator));



$getter = function (int $index, \V8\PropertyCallbackInfo $info) use (&$foo) {
    echo 'I am indexed getter for ', $index, '!', PHP_EOL;

    if (1 === $index) {
        $info->getReturnValue()->setUndefined();
        return;
    }

    $info->getReturnValue()->set(new \V8\NumberValue($info->getIsolate(), $foo));
};

$setter = function (int $index, \V8\Value $value, \V8\PropertyCallbackInfo $info) use (&$foo) {
    echo 'I am indexed setter for ', $index, '!', PHP_EOL;

    $foo = $value->toNumber($info->getContext())->value() / 2;
};

$query = function ($index, \V8\PropertyCallbackInfo $info) use (&$foo) {
    echo 'I am indexed query for ', $index, '!', PHP_EOL;

    if (1 === $index) {
        return;
    }

    $info->getReturnValue()->setInteger(\V8\PropertyAttribute::NONE);
};

$deleter = function (int $index, \V8\PropertyCallbackInfo $info) use (&$foo) {
    echo 'I am indexed deleter for ', $index, '!', PHP_EOL;
//    $info->getReturnValue()->set(true);
};

$enumerator = function (\V8\PropertyCallbackInfo $info) use (&$foo, &$allow_indexed) {
    echo 'I am indexed enumerator!', PHP_EOL;

    $ctxt = $info->getContext();
    $arr = new \V8\ArrayObject($ctxt);

    if ($allow_indexed) {
        for ($i =0; $i < 10; $i ++) {
            $arr->set($ctxt, new \V8\Uint32Value($info->getIsolate(), $i), new \V8\NumberValue($info->getIsolate(), $i));
        }
    }
    $info->getReturnValue()->set($arr);
};
$test_obj_tpl->setHandlerForIndexedProperty(new \V8\IndexedPropertyHandlerConfiguration($getter, $setter, $query, $deleter, $enumerator));



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

$allow_named = true;
$allow_indexed = false;

$script = new V8\Script($context, new \V8\StringValue($isolate, $source), new \V8\ScriptOrigin($file_name));
$res = $script->run($context);
$helper->space();


$source    = '

console.log("\"0\" in test: ", "0" in test);
console.log("0 in test: ", 0 in test);

console.log("\"1\" in test: ", "1" in test);
console.log("1 in test: ", 1 in test);

console.log("test[0] = 42: ", test[0] = 42);
console.log("test[0]: ", test[0]);

console.log("delete test[0]: ", delete test[0]);
console.log("test[0]: ", test[0]);

for (i in test) {
    console.log("test["+i+"]: ", test[i]);
}
';
$file_name = 'test.js';

$allow_named = false;
$allow_indexed = true;

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
I am indexed enumerator!
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


I am indexed query for 0!
"0" in test: true
I am indexed query for 0!
0 in test: true
I am indexed query for 1!
"1" in test: false
I am indexed query for 1!
1 in test: false
I am indexed setter for 0!
test[0] = 42: 42
I am indexed getter for 0!
test[0]: 21
I am indexed deleter for 0!
delete test[0]: true
I am indexed getter for 0!
test[0]: 21
I am indexed enumerator!
I am named enumerator!
I am indexed query for 0!
I am indexed getter for 0!
test[0]: 21
I am indexed query for 1!
I am indexed query for 2!
I am indexed getter for 2!
test[2]: 21
I am indexed query for 3!
I am indexed getter for 3!
test[3]: 21
I am indexed query for 4!
I am indexed getter for 4!
test[4]: 21
I am indexed query for 5!
I am indexed getter for 5!
test[5]: 21
I am indexed query for 6!
I am indexed getter for 6!
test[6]: 21
I am indexed query for 7!
I am indexed getter for 7!
test[7]: 21
I am indexed query for 8!
I am indexed getter for 8!
test[8]: 21
I am indexed query for 9!
I am indexed getter for 9!
test[9]: 21
