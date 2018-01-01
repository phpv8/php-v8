--TEST--
V8\ObjectTemplate::setCallAsFunctionHandler
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

$callback = function (\V8\FunctionCallbackInfo $info) {
    $out = [];
    // here we know that only Values with Value() method will be provided

    /** @var \V8\StringValue $arg */
    foreach($info->arguments() as $arg) {
        $out[] = $arg->value();
    }

    echo implode(' ', $out), PHP_EOL;

    $info->getReturnValue()->set(new \V8\StringValue($info->getIsolate(), 'done'));
};

$test_obj_tpl = new \V8\ObjectTemplate($isolate);
$test_obj_tpl->setCallAsFunctionHandler($callback);

$global_template->set(new \V8\StringValue($isolate, 'func'), new \V8\FunctionTemplate($isolate));
$global_template->set(new \V8\StringValue($isolate, 'test'), $test_obj_tpl);
$global_template->set(new \V8\StringValue($isolate, 'test2'), new \V8\ObjectTemplate($isolate));

$context = new V8\Context($isolate, $global_template);
$v8_helper->injectConsoleLog($context);

$source    = '
console.log("typeof func: ", typeof func);
console.log("func: ", func);
console.log("func(): ", func("should", "pass"));

console.log();
console.log("typeof test: ", typeof test);
console.log("test: ", test);
console.log("test(): ", test("should", "pass"));

console.log();
console.log("typeof test2: ", typeof test2);
console.log("test2: ", test2);

try {
    console.log("test2(): ", test2("will", "fail"));
} catch (e) {
    console.log(e);
}
';

$file_name = 'test.js';

$script = new V8\Script($context, new \V8\StringValue($isolate, $source), new \V8\ScriptOrigin($file_name));
$res = $script->run($context);

?>
--EXPECT--
typeof func: function
func: function func() { [native code] }
func(): [object Object]

typeof test: function
test: [object Object]
should pass
test(): done

typeof test2: object
test2: [object Object]
TypeError: test2 is not a function
