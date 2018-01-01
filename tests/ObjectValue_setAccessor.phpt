--TEST--
V8\ObjectValue::setAccessor()
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

// TODO: check object with callbacks persistance!

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';
require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

$isolate         = new \V8\Isolate();
$global_template = new V8\ObjectTemplate($isolate);


$print_func_tpl = new \V8\FunctionTemplate($isolate, function (\V8\FunctionCallbackInfo $info) {
    $context = $info->getContext();

    $out = [];

    foreach ($info->arguments() as $arg) {
        if ($arg->isUndefined()) {
            $out[] = '<undefined>';
        } elseif ($arg->isNull()) {
            $out[] = var_export(null, true);
        } elseif ($arg->isTrue() || $arg->isFalse()) {
            $out[] = var_export($arg->booleanValue($context), true);
        } else {
            $out[] = $arg->toString($context)->value();
        }
    }

    echo implode('', $out), PHP_EOL;
});

$global_template->set(new \V8\StringValue($isolate, 'print'), $print_func_tpl, \V8\PropertyAttribute::DONT_DELETE);

$context = new V8\Context($isolate, $global_template);

$prop_value = 'foo';

$getter = function (\V8\NameValue $property, \V8\PropertyCallbackInfo $info) use (&$prop_value) {
    echo 'Userland getter on property ', $property->toString($info->getContext())->value(), ' called, value is ', $prop_value, PHP_EOL;

    $info->getReturnValue()->set(new \V8\StringValue($info->getIsolate(), $prop_value));
};


$setter = function (\V8\NameValue $property, \V8\Value $value, \V8\PropertyCallbackInfo $info) use (& $prop_value) {
    $val = $value->toString($info->getContext())->value();
    echo 'Userland setter on property ', $property->toString($info->getContext())->value(), ' called with ', $val, ', value is ', $prop_value, PHP_EOL;

    $prop_value = $val;
};


$obj = new \V8\ObjectValue($context);


$obj->setAccessor($context, new \V8\StringValue($isolate, 'test'), $getter, $setter);

$context->globalObject()->set($context, new \V8\StringValue($isolate, 'obj'), $obj);
$source    = '
print(obj.test);
obj.test = "bar";
print(obj.test);

"Script done";
';
$file_name = 'test.js';


$script = new V8\Script($context, new \V8\StringValue($isolate, $source), new \V8\ScriptOrigin($file_name));

$helper->dump($script->run($context)->toString($context)->value());

?>
--EXPECT--
Userland getter on property test called, value is foo
foo
Userland setter on property test called with bar, value is foo
Userland getter on property test called, value is bar
bar
string(11) "Script done"
