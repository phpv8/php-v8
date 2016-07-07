--TEST--
v8\ArrayObject
--SKIPIF--
<?php if (!extension_loaded("v8")) { print "skip"; } ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

$isolate = new \v8\Isolate();
$extensions1 = [];
$global_template1 = new v8\ObjectTemplate($isolate);

$global_template1->Set(new \v8\StringValue($isolate, 'print'), $v8_helper->getPrintFunctionTemplate($isolate), \v8\PropertyAttribute::DontDelete);

$context = new v8\Context($isolate, $extensions1, $global_template1);

$value = new v8\ArrayObject($context);

$helper->header('Object representation');
$helper->dump($value);
$helper->space();

$helper->assert('ArrayObject extends ObjectValue', $value instanceof \v8\ObjectValue);
$helper->line();

$helper->header('Accessors');
$helper->method_matches($value, 'GetIsolate', $isolate);
$helper->method_matches($value, 'GetContext', $context);
$helper->method_matches($value, 'CreationContext', $context);
$helper->space();

$helper->header('Converters');
$helper->dump_object_methods($value, ['@@default' => [$context]], new RegexpFilter('/^To/'));
$helper->space();


$value->SetIndex($context, 0, new \v8\StringValue($isolate, 'first'));
$value->SetIndex($context, 1, new \v8\StringValue($isolate, 'second'));
$value->Set($context, new \v8\Uint32Value($isolate, 2), new \v8\StringValue($isolate, 'third'));

$value->Set($context, new \v8\StringValue($isolate, 'test'), new \v8\StringValue($isolate, 'property'));

$context->GlobalObject()->Set($context, new \v8\StringValue($isolate, 'arr'), $value);

$source1    = '
print("typeof arr: ", typeof arr, "\n");
print("arr: ", arr, "\n");
print("arr.length: ", arr.length, "\n");
print("arr[0]: ", arr[0], "\n");
print("arr.test: ", arr.test, "\n");
print("arr.slice(1): ", arr.slice(1), "\n");
';
$file_name1 = 'test.js';

$script1 = new v8\Script($context, new \v8\StringValue($isolate, $source1), new \v8\ScriptOrigin($file_name1));
$res1 = $script1->Run();

?>
--EXPECT--
Object representation:
----------------------
object(v8\ArrayObject)#6 (2) {
  ["isolate":"v8\Value":private]=>
  object(v8\Isolate)#3 (1) {
    ["snapshot":"v8\Isolate":private]=>
    NULL
  }
  ["context":"v8\ObjectValue":private]=>
  object(v8\Context)#5 (4) {
    ["isolate":"v8\Context":private]=>
    object(v8\Isolate)#3 (1) {
      ["snapshot":"v8\Isolate":private]=>
      NULL
    }
    ["extensions":"v8\Context":private]=>
    array(0) {
    }
    ["global_template":"v8\Context":private]=>
    object(v8\ObjectTemplate)#4 (1) {
      ["isolate":"v8\Template":private]=>
      object(v8\Isolate)#3 (1) {
        ["snapshot":"v8\Isolate":private]=>
        NULL
      }
    }
    ["global_object":"v8\Context":private]=>
    NULL
  }
}


ArrayObject extends ObjectValue: ok

Accessors:
----------
v8\ArrayObject::GetIsolate() matches expected value
v8\ArrayObject::GetContext() matches expected value
v8\ArrayObject::CreationContext() matches expected value


Converters:
-----------
v8\ArrayObject(v8\Value)->ToBoolean():
    object(v8\BooleanValue)#91 (1) {
      ["isolate":"v8\Value":private]=>
      object(v8\Isolate)#3 (1) {
        ["snapshot":"v8\Isolate":private]=>
        NULL
      }
    }
v8\ArrayObject(v8\Value)->ToNumber():
    object(v8\NumberValue)#91 (1) {
      ["isolate":"v8\Value":private]=>
      object(v8\Isolate)#3 (1) {
        ["snapshot":"v8\Isolate":private]=>
        NULL
      }
    }
v8\ArrayObject(v8\Value)->ToString():
    object(v8\StringValue)#91 (1) {
      ["isolate":"v8\Value":private]=>
      object(v8\Isolate)#3 (1) {
        ["snapshot":"v8\Isolate":private]=>
        NULL
      }
    }
v8\ArrayObject(v8\Value)->ToDetailString():
    object(v8\StringValue)#91 (1) {
      ["isolate":"v8\Value":private]=>
      object(v8\Isolate)#3 (1) {
        ["snapshot":"v8\Isolate":private]=>
        NULL
      }
    }
v8\ArrayObject(v8\Value)->ToObject():
    object(v8\ArrayObject)#6 (2) {
      ["isolate":"v8\Value":private]=>
      object(v8\Isolate)#3 (1) {
        ["snapshot":"v8\Isolate":private]=>
        NULL
      }
      ["context":"v8\ObjectValue":private]=>
      object(v8\Context)#5 (4) {
        ["isolate":"v8\Context":private]=>
        object(v8\Isolate)#3 (1) {
          ["snapshot":"v8\Isolate":private]=>
          NULL
        }
        ["extensions":"v8\Context":private]=>
        array(0) {
        }
        ["global_template":"v8\Context":private]=>
        object(v8\ObjectTemplate)#4 (1) {
          ["isolate":"v8\Template":private]=>
          object(v8\Isolate)#3 (1) {
            ["snapshot":"v8\Isolate":private]=>
            NULL
          }
        }
        ["global_object":"v8\Context":private]=>
        NULL
      }
    }
v8\ArrayObject(v8\Value)->ToInteger():
    object(v8\NumberValue)#91 (1) {
      ["isolate":"v8\Value":private]=>
      object(v8\Isolate)#3 (1) {
        ["snapshot":"v8\Isolate":private]=>
        NULL
      }
    }
v8\ArrayObject(v8\Value)->ToUint32():
    object(v8\NumberValue)#91 (1) {
      ["isolate":"v8\Value":private]=>
      object(v8\Isolate)#3 (1) {
        ["snapshot":"v8\Isolate":private]=>
        NULL
      }
    }
v8\ArrayObject(v8\Value)->ToInt32():
    object(v8\NumberValue)#91 (1) {
      ["isolate":"v8\Value":private]=>
      object(v8\Isolate)#3 (1) {
        ["snapshot":"v8\Isolate":private]=>
        NULL
      }
    }
v8\ArrayObject(v8\Value)->ToArrayIndex(): v8\Exceptions\GenericException: Failed to convert


typeof arr: object
arr: [first, second, third]
arr.length: 3
arr[0]: first
arr.test: property
arr.slice(1): [second, third]
