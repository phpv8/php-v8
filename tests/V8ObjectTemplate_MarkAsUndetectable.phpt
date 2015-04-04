--TEST--
v8\ObjectTemplate::MarkAsUndetectable
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

$test_obj_tpl = new \v8\ObjectTemplate($isolate1);
$test_obj_tpl->MarkAsUndetectable();

$global_template1->Set(new \v8\StringValue($isolate1, 'test'), $test_obj_tpl);
$global_template1->Set(new \v8\StringValue($isolate1, 'test2'), new \v8\ObjectTemplate($isolate1));

$context1 = new v8\Context($isolate1, $extensions1, $global_template1);


$source1    = '
print("typeof test: ", typeof test, "\n");
print("test: ", test, "\n");
print("!test: ", !test, "\n");


print("\n");
print("typeof test2: ", typeof test2, "\n");
print("test2: ", test2, "\n");
print("!test2: ", !test2, "\n");
';

$file_name1 = 'test.js';

$script1 = new v8\Script($context1, new \v8\StringValue($isolate1, $source1), new \v8\ScriptOrigin($file_name1));
$res1 = $script1->Run();

?>
--EXPECT--
typeof test: undefined
test: [object Object]
!test: true

typeof test2: object
test2: [object Object]
!test2: false
