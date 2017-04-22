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

    echo implode('', $out), PHP_EOL;
});



$function_template->SetClassName(new \V8\StringValue($isolate, 'TestFunction'));


$helper->header('Object representation');
$helper->dump($function_template);
$helper->space();

$helper->header('Accessors');
$helper->method_matches($function_template, 'GetIsolate', $isolate);
$helper->space();


$helper->header('Instance template');
$instance_template = $function_template->InstanceTemplate();
$helper->dump($instance_template);
$helper->method_matches($function_template, 'InstanceTemplate', $instance_template);
$helper->space();

$global_template = new V8\ObjectTemplate($isolate);

$value = new V8\StringValue($isolate, 'TEST VALUE 111');

$global_template->Set(new \V8\StringValue($isolate, 'test'), $value);
$global_template->Set(new \V8\StringValue($isolate, 'func'), $function_template);
$global_template->Set(new \V8\StringValue($isolate, 'print'), $print_func_tpl, \V8\PropertyAttribute::DontDelete);


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

$global = $context->GlobalObject();

$s = new \V8\StringValue($isolate, 'test');
$s2 = new \V8\StringValue($isolate2, 'test 2');

$o = new \V8\ObjectValue($context);
$o2 = new \V8\ObjectValue($context2);

$global->Set($context, new \V8\StringValue($isolate, 's'), $s);
try {
  $global->Set($context, new \V8\StringValue($isolate, 's2'), $s2);
} catch (Exception $e) {
  $helper->exception_export($e);
}

$global->Set($context, new \V8\StringValue($isolate, 'o'), $o);

try {
  $global->Set($context, new \V8\StringValue($isolate, 'o2'), $o2);
} catch (Exception $e) {
  $helper->exception_export($e);
}

$helper->value_matches_with_no_output($isolate, $isolate2, false);
$helper->value_matches_with_no_output($isolate, $isolate2, true);

$res = $v8_helper->CompileRun($context, $source);

$helper->dump($res->IsFunction());

if ($res->IsFunction()) {
    $func = $res->ToObject($context)->GetConstructorName();
    $helper->dump($func);
}

$helper->dump($res->ToString($context)->Value());


?>
--EXPECT--
Object representation:
----------------------
object(V8\FunctionTemplate)#5 (1) {
  ["isolate":"V8\Template":private]=>
  object(V8\Isolate)#3 (5) {
    ["snapshot":"V8\Isolate":private]=>
    NULL
    ["time_limit":"V8\Isolate":private]=>
    float(0)
    ["time_limit_hit":"V8\Isolate":private]=>
    bool(false)
    ["memory_limit":"V8\Isolate":private]=>
    int(0)
    ["memory_limit_hit":"V8\Isolate":private]=>
    bool(false)
  }
}


FunctionTemplate extends Template: ok
FunctionTemplate implements AdjustableExternalMemoryInterface: ok

Object representation:
----------------------
object(V8\FunctionTemplate)#5 (1) {
  ["isolate":"V8\Template":private]=>
  object(V8\Isolate)#3 (5) {
    ["snapshot":"V8\Isolate":private]=>
    NULL
    ["time_limit":"V8\Isolate":private]=>
    float(0)
    ["time_limit_hit":"V8\Isolate":private]=>
    bool(false)
    ["memory_limit":"V8\Isolate":private]=>
    int(0)
    ["memory_limit_hit":"V8\Isolate":private]=>
    bool(false)
  }
}


Accessors:
----------
V8\FunctionTemplate::GetIsolate() matches expected value


Instance template:
------------------
object(V8\ObjectTemplate)#8 (1) {
  ["isolate":"V8\Template":private]=>
  object(V8\Isolate)#3 (5) {
    ["snapshot":"V8\Isolate":private]=>
    NULL
    ["time_limit":"V8\Isolate":private]=>
    float(0)
    ["time_limit_hit":"V8\Isolate":private]=>
    bool(false)
    ["memory_limit":"V8\Isolate":private]=>
    int(0)
    ["memory_limit_hit":"V8\Isolate":private]=>
    bool(false)
  }
}
V8\FunctionTemplate::InstanceTemplate() doesn't match expected value


V8\Exceptions\Exception: Isolates mismatch
V8\Exceptions\Exception: Isolates mismatch
Expected value matches actual value
Expected value is not identical to actual value
Hello, world!
test [object Object]
bool(false)
string(6) "object"
