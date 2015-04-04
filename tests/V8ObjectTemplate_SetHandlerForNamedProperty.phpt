--TEST--
v8\ObjectTemplate::SetHandlerForNamedProperty()
--SKIPIF--
<?php if (!extension_loaded("v8")) { print "skip"; } ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

$isolate1 = new \v8\Isolate();
$extensions1 = [];
$global_template1 = new v8\ObjectTemplate($isolate1);

$global_template1->Set(new \v8\StringValue($isolate1, 'print'), $v8_helper->getPrintFunctionTemplate($isolate1), \v8\PropertyAttribute::DontDelete);

$foo = 100;

$getter = function (\v8\NameValue $name, \v8\PropertyCallbackInfo $info) use (&$foo) {
    echo 'I am named getter for ', $name->ToString($info->GetContext())->Value(), '!', PHP_EOL;

    if ('bar' === $name) {
        $info->GetReturnValue()->SetUndefined();
        return;
    }

    $info->GetReturnValue()->Set(new \v8\NumberValue($info->GetIsolate(), $foo));
};

$setter = function (\v8\NameValue$name, \v8\Value $value, \v8\PropertyCallbackInfo $info) use (&$foo) {
    echo 'I am named setter for ', $name->ToString($info->GetContext())->Value(), '!', PHP_EOL;

    $foo = $value->ToNumber($info->GetContext())->Value() / 2;
};

$query = function (\v8\NameValue$name, \v8\PropertyCallbackInfo $info) use (&$foo) {
    echo 'I am named query for ', $name->ToString($info->GetContext())->Value(), '!', PHP_EOL;
    $info->GetReturnValue()->SetInteger(\v8\PropertyAttribute::None);
};

$deleter = function (\v8\NameValue$name, \v8\PropertyCallbackInfo $info) use (&$foo) {
    echo 'I am named deleter for ', $name->ToString($info->GetContext())->Value(), '!', PHP_EOL;
//    $info->GetReturnValue()->Set(true);
};

$enumerator = function (\v8\PropertyCallbackInfo $info) use (&$foo) {
    echo 'I am named enumerator!', PHP_EOL;

    $ctxt = $info->GetContext();
    $arr = new \v8\ArrayObject($ctxt);

    for ($i =0, $j = 'test-a'; $i < 10; $i ++, $j++) {
        $arr->Set($ctxt, new \v8\StringValue($info->GetIsolate(), $i), new \v8\StringValue($info->GetIsolate(), $j));
    }
    $info->GetReturnValue()->Set($arr);
};


$test_obj_tpl = new \v8\ObjectTemplate($isolate1);
$test_obj_tpl->SetHandlerForNamedProperty(new \v8\NamedPropertyHandlerConfiguration($getter, $setter, $query, $deleter, $enumerator));

$global_template1->Set(new \v8\StringValue($isolate1, 'test'), $test_obj_tpl);

$context1 = new v8\Context($isolate1, $extensions1, $global_template1);


$source1    = '
print("\"foo\" in test: ", "foo" in test, "\n");
print("\"bar\" in test: ", "bar" in test, "\n");

print("test.foo: ", test.foo, "\n");
print("test.foo = 42: ", test.foo = 42, "\n");
print("test.foo: ", test.foo, "\n");

print("delete test.foo: ", delete test.foo, "\n");
print("\"foo\" in test: ", "foo" in test, "\n");

for (i in test) {
    print("test["+i+"]: ", test[i], "\n");
}
';
$file_name1 = 'test.js';

$script1 = new v8\Script($context1, new \v8\StringValue($isolate1, $source1), new \v8\ScriptOrigin($file_name1));
$res1 = $script1->Run();

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
