--TEST--
V8\ArrayObject
--SKIPIF--
<?php if (!extension_loaded("v8")) { print "skip"; } ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

$isolate = new \V8\Isolate();
$extensions1 = [];
$global_template1 = new V8\ObjectTemplate($isolate);

$global_template1->Set(new \V8\StringValue($isolate, 'print'), $v8_helper->getPrintFunctionTemplate($isolate), \V8\PropertyAttribute::DontDelete);
$context = new V8\Context($isolate, $extensions1, $global_template1);

$value = new V8\ArrayObject($context);

$helper->header('Object representation');
$helper->dump($value);
$helper->space();

$helper->assert('ArrayObject extends ObjectValue', $value instanceof \V8\ObjectValue);
$helper->line();

$helper->header('Accessors');
$helper->method_matches($value, 'GetIsolate', $isolate);
$helper->method_matches($value, 'GetContext', $context);
$helper->method_matches($value, 'CreationContext', $context);
$helper->space();

$v8_helper->run_checks($value, 'Checkers');


$helper->header('Converters');
$helper->dump_object_methods($value, ['@@default' => [$context]], new RegexpFilter('/^To/'));
$helper->space();


$value->SetIndex($context, 0, new \V8\StringValue($isolate, 'first'));
$value->SetIndex($context, 1, new \V8\StringValue($isolate, 'second'));
$value->Set($context, new \V8\Uint32Value($isolate, 2), new \V8\StringValue($isolate, 'third'));

$value->Set($context, new \V8\StringValue($isolate, 'test'), new \V8\StringValue($isolate, 'property'));

$context->GlobalObject()->Set($context, new \V8\StringValue($isolate, 'arr'), $value);

$source1    = '
print("typeof arr: ", typeof arr, "\n");
print("arr: ", arr, "\n");
print("arr.length: ", arr.length, "\n");
print("arr[0]: ", arr[0], "\n");
print("arr.test: ", arr.test, "\n");
print("arr.slice(1): ", arr.slice(1), "\n");
';
$file_name1 = 'test.js';

$script1 = new V8\Script($context, new \V8\StringValue($isolate, $source1), new \V8\ScriptOrigin($file_name1));
$res1 = $script1->Run($context);

?>
--EXPECT--
Object representation:
----------------------
object(V8\ArrayObject)#6 (2) {
  ["isolate":"V8\Value":private]=>
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
  ["context":"V8\ObjectValue":private]=>
  object(V8\Context)#5 (4) {
    ["isolate":"V8\Context":private]=>
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
    ["extensions":"V8\Context":private]=>
    array(0) {
    }
    ["global_template":"V8\Context":private]=>
    object(V8\ObjectTemplate)#4 (1) {
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
    ["global_object":"V8\Context":private]=>
    NULL
  }
}


ArrayObject extends ObjectValue: ok

Accessors:
----------
V8\ArrayObject::GetIsolate() matches expected value
V8\ArrayObject::GetContext() matches expected value
V8\ArrayObject::CreationContext() matches expected value


Checkers:
---------
V8\ArrayObject(V8\Value)->TypeOf(): V8\StringValue->Value(): string(6) "object"

V8\ArrayObject(V8\ObjectValue)->IsCallable(): bool(false)
V8\ArrayObject(V8\ObjectValue)->IsConstructor(): bool(false)
V8\ArrayObject(V8\Value)->IsUndefined(): bool(false)
V8\ArrayObject(V8\Value)->IsNull(): bool(false)
V8\ArrayObject(V8\Value)->IsNullOrUndefined(): bool(false)
V8\ArrayObject(V8\Value)->IsTrue(): bool(false)
V8\ArrayObject(V8\Value)->IsFalse(): bool(false)
V8\ArrayObject(V8\Value)->IsName(): bool(false)
V8\ArrayObject(V8\Value)->IsString(): bool(false)
V8\ArrayObject(V8\Value)->IsSymbol(): bool(false)
V8\ArrayObject(V8\Value)->IsFunction(): bool(false)
V8\ArrayObject(V8\Value)->IsArray(): bool(true)
V8\ArrayObject(V8\Value)->IsObject(): bool(true)
V8\ArrayObject(V8\Value)->IsBoolean(): bool(false)
V8\ArrayObject(V8\Value)->IsNumber(): bool(false)
V8\ArrayObject(V8\Value)->IsInt32(): bool(false)
V8\ArrayObject(V8\Value)->IsUint32(): bool(false)
V8\ArrayObject(V8\Value)->IsDate(): bool(false)
V8\ArrayObject(V8\Value)->IsArgumentsObject(): bool(false)
V8\ArrayObject(V8\Value)->IsBooleanObject(): bool(false)
V8\ArrayObject(V8\Value)->IsNumberObject(): bool(false)
V8\ArrayObject(V8\Value)->IsStringObject(): bool(false)
V8\ArrayObject(V8\Value)->IsSymbolObject(): bool(false)
V8\ArrayObject(V8\Value)->IsNativeError(): bool(false)
V8\ArrayObject(V8\Value)->IsRegExp(): bool(false)
V8\ArrayObject(V8\Value)->IsAsyncFunction(): bool(false)
V8\ArrayObject(V8\Value)->IsGeneratorFunction(): bool(false)
V8\ArrayObject(V8\Value)->IsGeneratorObject(): bool(false)
V8\ArrayObject(V8\Value)->IsPromise(): bool(false)
V8\ArrayObject(V8\Value)->IsMap(): bool(false)
V8\ArrayObject(V8\Value)->IsSet(): bool(false)
V8\ArrayObject(V8\Value)->IsMapIterator(): bool(false)
V8\ArrayObject(V8\Value)->IsSetIterator(): bool(false)
V8\ArrayObject(V8\Value)->IsWeakMap(): bool(false)
V8\ArrayObject(V8\Value)->IsWeakSet(): bool(false)
V8\ArrayObject(V8\Value)->IsArrayBuffer(): bool(false)
V8\ArrayObject(V8\Value)->IsArrayBufferView(): bool(false)
V8\ArrayObject(V8\Value)->IsTypedArray(): bool(false)
V8\ArrayObject(V8\Value)->IsUint8Array(): bool(false)
V8\ArrayObject(V8\Value)->IsUint8ClampedArray(): bool(false)
V8\ArrayObject(V8\Value)->IsInt8Array(): bool(false)
V8\ArrayObject(V8\Value)->IsUint16Array(): bool(false)
V8\ArrayObject(V8\Value)->IsInt16Array(): bool(false)
V8\ArrayObject(V8\Value)->IsUint32Array(): bool(false)
V8\ArrayObject(V8\Value)->IsInt32Array(): bool(false)
V8\ArrayObject(V8\Value)->IsFloat32Array(): bool(false)
V8\ArrayObject(V8\Value)->IsFloat64Array(): bool(false)
V8\ArrayObject(V8\Value)->IsDataView(): bool(false)
V8\ArrayObject(V8\Value)->IsSharedArrayBuffer(): bool(false)
V8\ArrayObject(V8\Value)->IsProxy(): bool(false)


Converters:
-----------
V8\ArrayObject(V8\Value)->ToBoolean():
    object(V8\BooleanValue)#123 (1) {
      ["isolate":"V8\Value":private]=>
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
V8\ArrayObject(V8\Value)->ToNumber():
    object(V8\NumberValue)#123 (1) {
      ["isolate":"V8\Value":private]=>
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
V8\ArrayObject(V8\Value)->ToString():
    object(V8\StringValue)#123 (1) {
      ["isolate":"V8\Value":private]=>
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
V8\ArrayObject(V8\Value)->ToDetailString():
    object(V8\StringValue)#123 (1) {
      ["isolate":"V8\Value":private]=>
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
V8\ArrayObject(V8\Value)->ToObject():
    object(V8\ArrayObject)#6 (2) {
      ["isolate":"V8\Value":private]=>
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
      ["context":"V8\ObjectValue":private]=>
      object(V8\Context)#5 (4) {
        ["isolate":"V8\Context":private]=>
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
        ["extensions":"V8\Context":private]=>
        array(0) {
        }
        ["global_template":"V8\Context":private]=>
        object(V8\ObjectTemplate)#4 (1) {
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
        ["global_object":"V8\Context":private]=>
        NULL
      }
    }
V8\ArrayObject(V8\Value)->ToInteger():
    object(V8\NumberValue)#123 (1) {
      ["isolate":"V8\Value":private]=>
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
V8\ArrayObject(V8\Value)->ToUint32():
    object(V8\NumberValue)#123 (1) {
      ["isolate":"V8\Value":private]=>
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
V8\ArrayObject(V8\Value)->ToInt32():
    object(V8\NumberValue)#123 (1) {
      ["isolate":"V8\Value":private]=>
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
V8\ArrayObject(V8\Value)->ToArrayIndex(): V8\Exceptions\GenericException: Failed to convert


typeof arr: object
arr: [first, second, third]
arr.length: 3
arr[0]: first
arr.test: property
arr.slice(1): [second, third]
