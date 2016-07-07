--TEST--
v8\ObjectTemplate::SetAccessCheckCallbacks() - test access check callback function arguments
--SKIPIF--
<?php if (!extension_loaded("v8")) { print "skip"; }
echo 'skip ', 'see https://groups.google.com/forum/?fromgroups#!topic/v8-dev/c7LhW2bNabY';
?>
--FILE--
<?php
/** @var \Phpv8Testsuite $helper */
//namespace test;

$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new \PhpV8Helpers($helper);


$isolate = new \v8\Isolate();
$global_template = new \v8\ObjectTemplate($isolate);

$access_check_callback = function (\v8\Context $context, \v8\ObjectValue $object) use ($isolate, $helper) {
    // NOTE: key is never used in v8 and always is <undefined>
    // NOTE: type is never used and always v8::AccessType::ACCESS_HAS used

    $helper->dump(func_get_args(), 1);
    return true;
};

$g_echo_value = -1;

$EchoGetter = function ($name, \v8\PropertyCallbackInfo $info) use (&$g_echo_value) {
    echo 'EchoGetter for ', $name, PHP_EOL;
    $info->GetReturnValue()->Set(new \v8\NumberValue($info->GetIsolate(), $g_echo_value));
};

$EchoSetter = function ($name, $value, \v8\PropertyCallbackInfo $info) use (&$g_echo_value) {
    echo 'EchoSetter for ', $name, PHP_EOL;
};

$test_obj_tpl = new \v8\ObjectTemplate($isolate);
$test_obj_tpl->SetHandler(new \v8\IndexedPropertyHandlerConfiguration($EchoGetter, $EchoSetter));
$test_obj_tpl->SetHandler(new \v8\NamedPropertyHandlerConfiguration($EchoGetter, $EchoSetter));
$test_obj_tpl->SetAccessCheckCallback($access_check_callback);


$global_template->Set(new \v8\StringValue($isolate, 'test'), $test_obj_tpl);

$context0 = new \v8\Context($isolate, [], $global_template);

$other = $context0->GlobalObject()->Get($context0, new \v8\StringValue($isolate, 'test'));

$context1 = new \v8\Context($isolate);

$global1 = $context1->GlobalObject();

$global1->Set($context1, new \v8\StringValue($isolate, 'other'), $other);


echo 'Property accessor:';
$v8_helper->CompileTryRun($context1, 'other.foo');
echo PHP_EOL;
echo 'Index accessor:';
$v8_helper->CompileTryRun($context1, 'other[1]');
echo PHP_EOL;

?>
--XFAIL--
Waiting for data parameter to be added to AccessCheck callback, https://groups.google.com/d/msg/v8-dev/c7LhW2bNabY/2p8U7KtgDQAJ
TODO: test null-callback
--EXPECT--
Property accessor:
    array(3) refcount(3){
      [0]=>
      object(v8\ObjectValue)#13 (2) refcount(5){
        ["isolate":"v8\Value":private]=>
        object(v8\Isolate)#3 (1) refcount(11){
          ["snapshot":"v8\Isolate":private]=>
          NULL
        }
        ["context":"v8\ObjectValue":private]=>
        object(v8\Context)#10 (4) refcount(2){
          ["isolate":"v8\Context":private]=>
          object(v8\Isolate)#3 (1) refcount(11){
            ["snapshot":"v8\Isolate":private]=>
            NULL
          }
          ["extensions":"v8\Context":private]=>
          array(0) refcount(2){
          }
          ["global_template":"v8\Context":private]=>
          object(v8\ObjectTemplate)#4 (1) refcount(2){
            ["isolate":"v8\Template":private]=>
            object(v8\Isolate)#3 (1) refcount(11){
              ["snapshot":"v8\Isolate":private]=>
              NULL
            }
          }
          ["global_object":"v8\Context":private]=>
          NULL
        }
      }
      [1]=>
      object(v8\Value)#17 (1) refcount(4){
        ["isolate":"v8\Value":private]=>
        object(v8\Isolate)#3 (1) refcount(11){
          ["snapshot":"v8\Isolate":private]=>
          NULL
        }
      }
      [2]=>
      int(2)
    }
EchoGetter for foo

Index accessor:
    array(3) refcount(3){
      [0]=>
      object(v8\ObjectValue)#13 (2) refcount(5){
        ["isolate":"v8\Value":private]=>
        object(v8\Isolate)#3 (1) refcount(11){
          ["snapshot":"v8\Isolate":private]=>
          NULL
        }
        ["context":"v8\ObjectValue":private]=>
        object(v8\Context)#10 (4) refcount(2){
          ["isolate":"v8\Context":private]=>
          object(v8\Isolate)#3 (1) refcount(11){
            ["snapshot":"v8\Isolate":private]=>
            NULL
          }
          ["extensions":"v8\Context":private]=>
          array(0) refcount(2){
          }
          ["global_template":"v8\Context":private]=>
          object(v8\ObjectTemplate)#4 (1) refcount(2){
            ["isolate":"v8\Template":private]=>
            object(v8\Isolate)#3 (1) refcount(11){
              ["snapshot":"v8\Isolate":private]=>
              NULL
            }
          }
          ["global_object":"v8\Context":private]=>
          NULL
        }
      }
      [1]=>
      object(v8\Value)#16 (1) refcount(4){
        ["isolate":"v8\Value":private]=>
        object(v8\Isolate)#3 (1) refcount(11){
          ["snapshot":"v8\Isolate":private]=>
          NULL
        }
      }
      [2]=>
      int(2)
    }
EchoGetter for 1
