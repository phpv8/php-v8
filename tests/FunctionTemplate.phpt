--TEST--
V8\FunctionTemplate
--SKIPIF--
<?php if (!extension_loaded("v8")) {
    print "skip";
} ?>
--FILE--
<?php
/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

require '.tracking_dtors.php';

// Tests:

$isolate = new \V8\Isolate();

$callback = function () {
    print("hello word to js from PHP\n");
};

$function_template = new \V8\FunctionTemplate($isolate);

$helper->header('Object representation');
$helper->dump($function_template);
$helper->space();

$helper->assert('FunctionTemplate extends Template', $function_template instanceof \V8\Template);
$helper->assert('FunctionTemplate implements AdjustableExternalMemoryInterface', $function_template instanceof \V8\AdjustableExternalMemoryInterface);

$helper->line();

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



$function_template->setClassName(new \V8\StringValue($isolate, 'TestFunction'));


$helper->header('Object representation');
$helper->dump($function_template);
$helper->space();

$helper->header('Accessors');
$helper->method_matches($function_template, 'getIsolate', $isolate);
$helper->space();


$helper->header('Instance template');
$instance_template = $function_template->instanceTemplate();
$helper->dump($instance_template);
$helper->method_matches($function_template, 'instanceTemplate', $instance_template);
$helper->space();

$global_template = new V8\ObjectTemplate($isolate);

$value = new V8\StringValue($isolate, 'TEST VALUE 111');

$global_template->set(new \V8\StringValue($isolate, 'test'), $value);
$global_template->set(new \V8\StringValue($isolate, 'func'), $function_template);
$global_template->set(new \V8\StringValue($isolate, 'print'), $print_func_tpl, \V8\PropertyAttribute::DONT_DELETE);


$context = new V8\Context($isolate, $global_template);


$source    = '
print("Hello, world!");
print(s, " ", o);
typeof func()
';
//$source    = 'func(); func(); func(); func()';
$file_name = 'test.js';

$isolate2 = new \V8\Isolate();
$context2 = new V8\Context($isolate2);

$global = $context->globalObject();

$s = new \V8\StringValue($isolate, 'test');
$s2 = new \V8\StringValue($isolate2, 'test 2');

$o = new \V8\ObjectValue($context);
$o2 = new \V8\ObjectValue($context2);

$global->set($context, new \V8\StringValue($isolate, 's'), $s);
try {
  $global->set($context, new \V8\StringValue($isolate, 's2'), $s2);
} catch (Exception $e) {
  $helper->exception_export($e);
}

$global->set($context, new \V8\StringValue($isolate, 'o'), $o);

try {
  $global->set($context, new \V8\StringValue($isolate, 'o2'), $o2);
} catch (Exception $e) {
  $helper->exception_export($e);
}

$helper->value_matches_with_no_output($isolate, $isolate2, false);
$helper->value_matches_with_no_output($isolate, $isolate2, true);

$res = $v8_helper->CompileRun($context, $source);

$helper->dump($res->isFunction());

if ($res->isFunction()) {
    $func = $res->toObject($context)->getConstructorName();
    $helper->dump($func);
}

$helper->dump($res->toString($context)->value());


?>
--EXPECT--
Object representation:
----------------------
object(V8\FunctionTemplate)#5 (1) {
  ["isolate":"V8\Template":private]=>
  object(V8\Isolate)#3 (0) {
  }
}


FunctionTemplate extends Template: ok
FunctionTemplate implements AdjustableExternalMemoryInterface: ok

Object representation:
----------------------
object(V8\FunctionTemplate)#5 (1) {
  ["isolate":"V8\Template":private]=>
  object(V8\Isolate)#3 (0) {
  }
}


Accessors:
----------
V8\FunctionTemplate::getIsolate() matches expected value


Instance template:
------------------
object(V8\ObjectTemplate)#8 (1) {
  ["isolate":"V8\Template":private]=>
  object(V8\Isolate)#3 (0) {
  }
}
V8\FunctionTemplate::instanceTemplate() doesn't match expected value


V8\Exceptions\Exception: Isolates mismatch
V8\Exceptions\Exception: Isolates mismatch
Expected value matches actual value
Expected value is not identical to actual value
Hello, world!
test [object Object]
bool(false)
string(6) "object"
