--TEST--
V8\ObjectTemplate::SetHandlerForIndexedProperty()
--SKIPIF--
<?php if (!extension_loaded("v8")) { print "skip"; } ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

$isolate1 = new \V8\Isolate();
$extensions1 = [];
$global_template1 = new V8\ObjectTemplate($isolate1);

$global_template1->Set(new \V8\StringValue($isolate1, 'print'), $v8_helper->getPrintFunctionTemplate($isolate1), \V8\PropertyAttribute::DontDelete);

$foo = 100;

$getter = function (int $index, \V8\PropertyCallbackInfo $info) use (&$foo) {
    echo 'I am indexed getter for ', $index, '!', PHP_EOL;

    if (1 === $index) {
        $info->GetReturnValue()->SetUndefined();
        return;
    }

    $info->GetReturnValue()->Set(new \V8\NumberValue($info->GetIsolate(), $foo));
};

$setter = function (int $index, \V8\Value $value, \V8\PropertyCallbackInfo $info) use (&$foo) {
    echo 'I am indexed setter for ', $index, '!', PHP_EOL;

    $foo = $value->ToNumber($info->GetContext())->Value() / 2;
};

$query = function ($index, \V8\PropertyCallbackInfo $info) use (&$foo) {
    echo 'I am indexed query for ', $index, '!', PHP_EOL;

    if (1 === $index) {
        return;
    }

    $info->GetReturnValue()->SetInteger(\V8\PropertyAttribute::None);
};

$deleter = function (int $index, \V8\PropertyCallbackInfo $info) use (&$foo) {
    echo 'I am indexed deleter for ', $index, '!', PHP_EOL;
//    $info->GetReturnValue()->Set(true);
};

$enumerator = function (\V8\PropertyCallbackInfo $info) use (&$foo) {
    echo 'I am indexed enumerator!', PHP_EOL;

    $ctxt = $info->GetContext();
    $arr = new \V8\ArrayObject($ctxt);

    for ($i =0; $i < 10; $i ++) {
        $arr->Set($ctxt, new \V8\Uint32Value($info->GetIsolate(), $i), new \V8\NumberValue($info->GetIsolate(), $i));
    }
    $info->GetReturnValue()->Set($arr);
};

$test = function () {
    echo 'I am indexed test!', PHP_EOL;

    foreach (func_get_args() as $arg) {
        echo '    ', is_object($arg) ? get_class($arg) : gettype($arg), PHP_EOL;
    }
};


$test_obj_tpl = new \V8\ObjectTemplate($isolate1);
$test_obj_tpl->SetHandlerForIndexedProperty(new \V8\IndexedPropertyHandlerConfiguration($getter, $setter, $query, $deleter, $enumerator));

$global_template1->Set(new \V8\StringValue($isolate1, 'test'), $test_obj_tpl);

$context1 = new V8\Context($isolate1, $extensions1, $global_template1);


$source1    = '

print("\"0\" in test: ", "0" in test, "\n");
print("0 in test: ", 0 in test, "\n");

print("\"1\" in test: ", "1" in test, "\n");
print("1 in test: ", 1 in test, "\n");

print("test[0] = 42: ", test[0] = 42, "\n");
print("test[0]: ", test[0], "\n");

print("delete test[0]: ", delete test[0], "\n");
print("test[0]: ", test[0], "\n");

for (i in test) {
    print("test["+i+"]: ", test[i], "\n");
}
';
$file_name1 = 'test.js';

$script1 = new V8\Script($context1, new \V8\StringValue($isolate1, $source1), new \V8\ScriptOrigin($file_name1));
$res1 = $script1->Run($context1);

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
