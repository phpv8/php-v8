--TEST--
V8\ObjectValue::SetAccessor
--SKIPIF--
<?php if (!extension_loaded("v8")) {
    print "skip";
} ?>
--FILE--
<?php

// TODO: check object with callbacks persistance!

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

$isolate1         = new \V8\Isolate();
$extensions1      = [];
$global_template1 = new V8\ObjectTemplate($isolate1);


$print_func_tpl = new \V8\FunctionTemplate($isolate1, function (\V8\FunctionCallbackInfo $info) {
    $context = $info->GetContext();

    $out = [];

    foreach ($info->Arguments() as $arg) {
        if ($arg->IsUndefined()) {
            $out[] = '<undefined>';
        } elseif ($arg->IsNull()) {
            $out[] = var_export(null, true);
        } elseif ($arg->IsTrue() || $arg->IsFalse()) {
            $out[] = var_export($arg->BooleanValue($context), true);
        } else {
            $out[] = $arg->ToString($context)->Value();
        }
    }

    echo implode('', $out);
});

$global_template1->Set(new \V8\StringValue($isolate1, 'print'), $print_func_tpl, \V8\PropertyAttribute::DontDelete);

$context1 = new V8\Context($isolate1, $extensions1, $global_template1);

$prop_value = 'foo';

$getter = function (\V8\NameValue $property, \V8\PropertyCallbackInfo $info) use (&$prop_value) {
    echo 'Userland getter on property ', $property->ToString($info->GetContext())->Value(), ' called, value is ', $prop_value, PHP_EOL;

    $info->GetReturnValue()->Set(new \V8\StringValue($info->GetIsolate(), $prop_value));
};


$setter = function (\V8\NameValue $property, \V8\Value $value, \V8\PropertyCallbackInfo $info) use (& $prop_value) {
    $val = $value->ToString($info->GetContext())->Value();
    echo 'Userland setter on property ', $property->ToString($info->GetContext())->Value(), ' called with ', $val, ', value is ', $prop_value, PHP_EOL;

    $prop_value = $val;
};


$obj = new \V8\ObjectValue($context1);


$obj->SetAccessor($context1, new \V8\StringValue($isolate1, 'test'), $getter, $setter);

$context1->GlobalObject()->Set($context1, new \V8\StringValue($isolate1, 'obj'), $obj);
$source1    = '
print(obj.test, "\n");
obj.test = "bar";
print(obj.test, "\n");

"Script done";
';
$file_name1 = 'test.js';


$script1 = new V8\Script($context1, new \V8\StringValue($isolate1, $source1), new \V8\ScriptOrigin($file_name1));

$helper->dump($script1->Run()->ToString($context1)->Value());

?>
--EXPECT--
Userland getter on property test called, value is foo
foo
Userland setter on property test called with bar, value is foo
Userland getter on property test called, value is bar
bar
string(11) "Script done"
