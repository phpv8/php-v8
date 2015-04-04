--TEST--
v8\FunctionTemplate - require() implementation
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

$isolate1 = new \v8\Isolate();


$code = [];
$code['test.js'] = 'var out = {"foo" : "unchanged"}; out';


// *** This is how require implemented in node.js: ***

/** @var \v8\Value[] $loaded */
$loaded_cache = [];

$require_func_tpl_cache = new \v8\FunctionTemplate($isolate1, function (\v8\FunctionCallbackInfo $info) use (&$loaded_cache, &$code) {
  $isolate = $info->GetIsolate();
  $context = $info->GetContext();

  $module = $info->Arguments()[0]->ToString($context)->Value();

  if (!isset($loaded_cache[$module])) {
    $new_context = new \v8\Context($isolate, [], new \v8\ObjectTemplate($isolate));

    $script = new \v8\Script($new_context, new \v8\StringValue($isolate, $code[$module]), new \v8\ScriptOrigin($module));

    $loaded_cache[$module] = $script->Run();
  }

  $info->GetReturnValue()->Set($loaded_cache[$module]);
});
$global_template = new v8\ObjectTemplate($isolate1);
$global_template->Set(new \v8\StringValue($isolate1, 'print'), $v8_helper->getPrintFunctionTemplate($isolate1), \v8\PropertyAttribute::DontDelete);
$global_template->Set(new \v8\StringValue($isolate1, 'require'), $require_func_tpl_cache, \v8\PropertyAttribute::DontDelete);
$context = new v8\Context($isolate1, [], $global_template);

$JS = '
var test = require("test.js");

print(test.foo, "\n");
test.foo = "changed";
print(test.foo, "\n");

var test2 = require("test.js");

print(test2.foo, "\n");
';
$file_name2 = 'experiment.js';

$helper->header('Test require() (with cache)');

$script2 = new v8\Script($context, new \v8\StringValue($isolate1, $JS), new \v8\ScriptOrigin($file_name2));
$res2 = $script2->Run();

$helper->space();



// *** This is how custom require() implementation demo (no caching): ***

/** @var \v8\Script[] $loaded */
$loaded_no_cache = [];

$require_func_tpl_nocache = new \v8\FunctionTemplate($isolate1, function (\v8\FunctionCallbackInfo $info) use (&$loaded_no_cache, &$code) {
  $isolate = $info->GetIsolate();
  $context = $info->GetContext();

  $module = $info->Arguments()[0]->ToString($context)->Value();

  if (!isset($loaded_no_cache[$module])) {
    $new_context = new \v8\Context($isolate, [], new \v8\ObjectTemplate($isolate));

    $script = new \v8\Script($new_context, new \v8\StringValue($isolate, $code[$module]), new \v8\ScriptOrigin($module));

    $loaded_no_cache[$module] = $script;
  }

  $info->GetReturnValue()->Set($loaded_no_cache[$module]->Run());
});


$global_template = new v8\ObjectTemplate($isolate1);
$global_template->Set(new \v8\StringValue($isolate1, 'print'), $v8_helper->getPrintFunctionTemplate($isolate1), \v8\PropertyAttribute::DontDelete);
$global_template->Set(new \v8\StringValue($isolate1, 'require'), $require_func_tpl_nocache, \v8\PropertyAttribute::DontDelete);
$context = new v8\Context($isolate1, [], $global_template);

$JS = '
var test = require("test.js");

print(test.foo, "\n");
test.foo = "changed";
print(test.foo, "\n");

var test2 = require("test.js");

print(test2.foo, "\n");
';
$file_name2 = 'experiment.js';

$helper->header('Test require() (no cache)');

$script2 = new v8\Script($context, new \v8\StringValue($isolate1, $JS), new \v8\ScriptOrigin($file_name2));
$res2 = $script2->Run();

?>
--EXPECT--
Test require() (with cache):
----------------------------
unchanged
changed
changed


Test require() (no cache):
--------------------------
unchanged
changed
unchanged
