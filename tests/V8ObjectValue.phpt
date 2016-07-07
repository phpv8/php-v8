--TEST--
v8\ObjectValue
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

$isolate = new \v8\Isolate();
$extensions = [];
$global_template = new v8\ObjectTemplate($isolate);

$context = new v8\Context($isolate, $extensions, $global_template);

$value = new v8\ObjectValue($context);


$helper->header('Object representation');
$helper->dump($value);
$helper->space();

$helper->assert('ObjectValue extends Value', $value instanceof \v8\Value);
$helper->assert('ObjectValue does not extend PrimitiveValue', !($value instanceof \v8\PrimitiveValue));
$helper->line();

$helper->header('Accessors');
$helper->method_matches($value, 'GetIsolate', $isolate);
$helper->method_matches($value, 'GetContext', $context);
$helper->method_matches($value, 'CreationContext', $context);
$helper->space();

$helper->header('Getters');
$helper->method_export($value, 'GetIdentityHash');
$helper->space();

$helper->header('Converters');
$helper->dump_object_methods($value, ['@@default' => [$context]], new RegexpFilter('/^To/'));

?>
--EXPECTF--
Object representation:
----------------------
object(v8\ObjectValue)#5 (2) {
  ["isolate":"v8\Value":private]=>
  object(v8\Isolate)#2 (1) {
    ["snapshot":"v8\Isolate":private]=>
    NULL
  }
  ["context":"v8\ObjectValue":private]=>
  object(v8\Context)#4 (4) {
    ["isolate":"v8\Context":private]=>
    object(v8\Isolate)#2 (1) {
      ["snapshot":"v8\Isolate":private]=>
      NULL
    }
    ["extensions":"v8\Context":private]=>
    array(0) {
    }
    ["global_template":"v8\Context":private]=>
    object(v8\ObjectTemplate)#3 (1) {
      ["isolate":"v8\Template":private]=>
      object(v8\Isolate)#2 (1) {
        ["snapshot":"v8\Isolate":private]=>
        NULL
      }
    }
    ["global_object":"v8\Context":private]=>
    NULL
  }
}


ObjectValue extends Value: ok
ObjectValue does not extend PrimitiveValue: ok

Accessors:
----------
v8\ObjectValue::GetIsolate() matches expected value
v8\ObjectValue::GetContext() matches expected value
v8\ObjectValue::CreationContext() matches expected value


Getters:
--------
v8\ObjectValue->GetIdentityHash(): int(%d)


Converters:
-----------
v8\ObjectValue(v8\Value)->ToBoolean():
    object(v8\BooleanValue)#88 (1) {
      ["isolate":"v8\Value":private]=>
      object(v8\Isolate)#2 (1) {
        ["snapshot":"v8\Isolate":private]=>
        NULL
      }
    }
v8\ObjectValue(v8\Value)->ToNumber():
    object(v8\NumberValue)#88 (1) {
      ["isolate":"v8\Value":private]=>
      object(v8\Isolate)#2 (1) {
        ["snapshot":"v8\Isolate":private]=>
        NULL
      }
    }
v8\ObjectValue(v8\Value)->ToString():
    object(v8\StringValue)#88 (1) {
      ["isolate":"v8\Value":private]=>
      object(v8\Isolate)#2 (1) {
        ["snapshot":"v8\Isolate":private]=>
        NULL
      }
    }
v8\ObjectValue(v8\Value)->ToDetailString():
    object(v8\StringValue)#88 (1) {
      ["isolate":"v8\Value":private]=>
      object(v8\Isolate)#2 (1) {
        ["snapshot":"v8\Isolate":private]=>
        NULL
      }
    }
v8\ObjectValue(v8\Value)->ToObject():
    object(v8\ObjectValue)#5 (2) {
      ["isolate":"v8\Value":private]=>
      object(v8\Isolate)#2 (1) {
        ["snapshot":"v8\Isolate":private]=>
        NULL
      }
      ["context":"v8\ObjectValue":private]=>
      object(v8\Context)#4 (4) {
        ["isolate":"v8\Context":private]=>
        object(v8\Isolate)#2 (1) {
          ["snapshot":"v8\Isolate":private]=>
          NULL
        }
        ["extensions":"v8\Context":private]=>
        array(0) {
        }
        ["global_template":"v8\Context":private]=>
        object(v8\ObjectTemplate)#3 (1) {
          ["isolate":"v8\Template":private]=>
          object(v8\Isolate)#2 (1) {
            ["snapshot":"v8\Isolate":private]=>
            NULL
          }
        }
        ["global_object":"v8\Context":private]=>
        NULL
      }
    }
v8\ObjectValue(v8\Value)->ToInteger():
    object(v8\NumberValue)#88 (1) {
      ["isolate":"v8\Value":private]=>
      object(v8\Isolate)#2 (1) {
        ["snapshot":"v8\Isolate":private]=>
        NULL
      }
    }
v8\ObjectValue(v8\Value)->ToUint32():
    object(v8\NumberValue)#88 (1) {
      ["isolate":"v8\Value":private]=>
      object(v8\Isolate)#2 (1) {
        ["snapshot":"v8\Isolate":private]=>
        NULL
      }
    }
v8\ObjectValue(v8\Value)->ToInt32():
    object(v8\NumberValue)#88 (1) {
      ["isolate":"v8\Value":private]=>
      object(v8\Isolate)#2 (1) {
        ["snapshot":"v8\Isolate":private]=>
        NULL
      }
    }
v8\ObjectValue(v8\Value)->ToArrayIndex(): v8\Exceptions\GenericException: Failed to convert
