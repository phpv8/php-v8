--TEST--
V8\ObjectTemplate::setHandlerForIndexedProperty()
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

$isolate = new \V8\Isolate();
$global_template = new V8\ObjectTemplate($isolate);

$foo = 100;

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

$enumerator = function (\V8\PropertyCallbackInfo $info) use (&$foo) {
    echo 'I am indexed enumerator!', PHP_EOL;

    $ctxt = $info->getContext();
    $arr = new \V8\ArrayObject($ctxt);

    for ($i =0; $i < 10; $i ++) {
        $arr->set($ctxt, new \V8\Uint32Value($info->getIsolate(), $i), new \V8\NumberValue($info->getIsolate(), $i));
    }
    $info->getReturnValue()->set($arr);
};

$test = function () {
    echo 'I am indexed test!', PHP_EOL;

    foreach (func_get_args() as $arg) {
        echo '    ', is_object($arg) ? get_class($arg) : gettype($arg), PHP_EOL;
    }
};


$test_obj_tpl = new \V8\ObjectTemplate($isolate);
$test_obj_tpl->setHandlerForIndexedProperty(new \V8\IndexedPropertyHandlerConfiguration($getter, $setter, $query, $deleter, $enumerator));

$global_template->set(new \V8\StringValue($isolate, 'test'), $test_obj_tpl);

$context = new V8\Context($isolate, $global_template);
$v8_helper->injectConsoleLog($context);


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

$script = new V8\Script($context, new \V8\StringValue($isolate, $source), new \V8\ScriptOrigin($file_name));
$res = $script->run($context);

?>
--EXPECT--
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
I am indexed query for 0!
I am indexed query for 1!
I am indexed query for 2!
I am indexed query for 3!
I am indexed query for 4!
I am indexed query for 5!
I am indexed query for 6!
I am indexed query for 7!
I am indexed query for 8!
I am indexed query for 9!
I am indexed query for 0!
I am indexed getter for 0!
test[0]: 21
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
