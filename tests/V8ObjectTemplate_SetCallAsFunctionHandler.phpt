--TEST--
v8\ObjectTemplate::SetCallAsFunctionHandler
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

$callback = function (\v8\FunctionCallbackInfo $info) {
    $out = [];
    // here we know that only Values with Value() method will be provided

    /** @var \v8\StringValue $arg */
    foreach($info->Arguments() as $arg) {
        $out[] = $arg->Value();
    }

    echo implode(' ', $out), PHP_EOL;

    $info->GetReturnValue()->Set(new \v8\StringValue($info->GetIsolate(), 'done'));
};

$test_obj_tpl = new \v8\ObjectTemplate($isolate1);
$test_obj_tpl->SetCallAsFunctionHandler($callback);

$global_template1->Set(new \v8\StringValue($isolate1, 'func'), new \v8\FunctionTemplate($isolate1));
$global_template1->Set(new \v8\StringValue($isolate1, 'test'), $test_obj_tpl);
$global_template1->Set(new \v8\StringValue($isolate1, 'test2'), new \v8\ObjectTemplate($isolate1));

$context1 = new v8\Context($isolate1, $extensions1, $global_template1);


$source1    = '
print("typeof func: ", typeof func, "\n");
print("func: ", func, "\n");
print("func(): ", func("should", "pass"), "\n");

print("\n");
print("typeof test: ", typeof test, "\n");
print("test: ", test, "\n");
print("test(): ", test("should", "pass"), "\n");

print("\n");
print("typeof test2: ", typeof test2, "\n");
print("test2: ", test2, "\n");

try {
    print("test2(): ", test2("will", "fail"), "\n");
} catch (e) {
    print(e, "\n");
}
';

$file_name1 = 'test.js';

$script1 = new v8\Script($context1, new \v8\StringValue($isolate1, $source1), new \v8\ScriptOrigin($file_name1));
$res1 = $script1->Run();

?>
--EXPECT--
typeof func: function
func: function func() { [native code] }
func(): [object global]

typeof test: function
test: [object Object]
should pass
test(): done

typeof test2: object
test2: [object Object]
TypeError: test2 is not a function
