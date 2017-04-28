--TEST--
V8\FunctionCallbackInfo
--SKIPIF--
<?php if (!extension_loaded("v8")) {
    print "skip";
} ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.tracking_dtors.php';

$isolate = new v8Tests\TrackingDtors\Isolate();
$global_template = new V8\ObjectTemplate($isolate);
$context = new V8\Context($isolate, $global_template);

// TEST: Pass context instead of isolate to FunctionTemplate

$scalar = new \V8\StringValue($isolate, "test");
$object = new \V8\ObjectValue($context);

/** @var V8\FunctionCallbackInfo $callback_info */
$callback_info = null;

$func = new v8Tests\TrackingDtors\FunctionObject($context, function (V8\FunctionCallbackInfo $info) use ($helper, &$callback_info, $scalar, $object, $isolate, $context) {
    echo 'Function called', PHP_EOL;

    $helper->header('Object representation');
    $helper->dump($info);
    $helper->space();

    $callback_info = $info;
    $helper->assert('Original arguments number passed', count($info->Arguments()) == 2);
    $helper->assert('Arguments number matches Length() method output', count($info->Arguments()) == $info->Length());

    $helper->assert('Callback info holds original isolate object', $info->GetIsolate(), $isolate);
    $helper->assert('Callback info holds original isolate object', $info->GetContext(), $context);

    $helper->assert('Scalars hold no info about their zval, so that their zvals are recreated on each access', $scalar !== $info->Arguments()[0]);
    $helper->assert("Objects can hold info about their zval and keep it until zval's get free() ", $object === $info->Arguments()[1]);
});

$context->GlobalObject()->Set($context, new \V8\StringValue($isolate, 'print'), $func);
$context->GlobalObject()->Set($context, new \V8\StringValue($isolate, 'scalar'), $scalar);
$context->GlobalObject()->Set($context, new \V8\StringValue($isolate, 'obj'), $object);

$source = 'print(scalar, obj); "Script done";';
$file_name = 'test.js';


$script = new V8\Script($context, new \V8\StringValue($isolate, $source), new \V8\ScriptOrigin($file_name));

$helper->dump($script->Run($context)->ToString($context)->Value());

$helper->space();

//try {
$retval = $callback_info->GetReturnValue();
$helper->dump($retval);
//} catch (Exception $e) {
//    $helper->exception_export($e);
//}
$helper->line();

$helper->header('Object representation (outside of context)');
$helper->dump($callback_info);
$helper->space();


echo 'We are done for now', PHP_EOL;

?>
--EXPECT--
Function called
Object representation:
----------------------
object(V8\FunctionCallbackInfo)#10 (8) {
  ["isolate":"V8\CallbackInfo":private]=>
  object(v8Tests\TrackingDtors\Isolate)#2 (4) {
    ["time_limit":"V8\Isolate":private]=>
    float(0)
    ["time_limit_hit":"V8\Isolate":private]=>
    bool(false)
    ["memory_limit":"V8\Isolate":private]=>
    int(0)
    ["memory_limit_hit":"V8\Isolate":private]=>
    bool(false)
  }
  ["context":"V8\CallbackInfo":private]=>
  object(V8\Context)#4 (1) {
    ["isolate":"V8\Context":private]=>
    object(v8Tests\TrackingDtors\Isolate)#2 (4) {
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
  ["this":"V8\CallbackInfo":private]=>
  object(V8\ObjectValue)#11 (2) {
    ["isolate":"V8\Value":private]=>
    object(v8Tests\TrackingDtors\Isolate)#2 (4) {
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
    object(V8\Context)#4 (1) {
      ["isolate":"V8\Context":private]=>
      object(v8Tests\TrackingDtors\Isolate)#2 (4) {
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
  }
  ["holder":"V8\CallbackInfo":private]=>
  object(V8\ObjectValue)#11 (2) {
    ["isolate":"V8\Value":private]=>
    object(v8Tests\TrackingDtors\Isolate)#2 (4) {
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
    object(V8\Context)#4 (1) {
      ["isolate":"V8\Context":private]=>
      object(v8Tests\TrackingDtors\Isolate)#2 (4) {
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
  }
  ["return_value":"V8\CallbackInfo":private]=>
  object(V8\ReturnValue)#12 (2) {
    ["isolate":"V8\ReturnValue":private]=>
    object(v8Tests\TrackingDtors\Isolate)#2 (4) {
      ["time_limit":"V8\Isolate":private]=>
      float(0)
      ["time_limit_hit":"V8\Isolate":private]=>
      bool(false)
      ["memory_limit":"V8\Isolate":private]=>
      int(0)
      ["memory_limit_hit":"V8\Isolate":private]=>
      bool(false)
    }
    ["context":"V8\ReturnValue":private]=>
    object(V8\Context)#4 (1) {
      ["isolate":"V8\Context":private]=>
      object(v8Tests\TrackingDtors\Isolate)#2 (4) {
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
  }
  ["arguments":"V8\FunctionCallbackInfo":private]=>
  array(2) {
    [0]=>
    object(V8\StringValue)#13 (1) {
      ["isolate":"V8\Value":private]=>
      object(v8Tests\TrackingDtors\Isolate)#2 (4) {
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
    [1]=>
    object(V8\ObjectValue)#6 (2) {
      ["isolate":"V8\Value":private]=>
      object(v8Tests\TrackingDtors\Isolate)#2 (4) {
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
      object(V8\Context)#4 (1) {
        ["isolate":"V8\Context":private]=>
        object(v8Tests\TrackingDtors\Isolate)#2 (4) {
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
    }
  }
  ["new_target":"V8\FunctionCallbackInfo":private]=>
  object(V8\UndefinedValue)#14 (1) {
    ["isolate":"V8\Value":private]=>
    object(v8Tests\TrackingDtors\Isolate)#2 (4) {
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
  ["is_constructor_call":"V8\FunctionCallbackInfo":private]=>
  bool(false)
}


Original arguments number passed: ok
Arguments number matches Length() method output: ok
Callback info holds original isolate object: ok
Callback info holds original isolate object: ok
Scalars hold no info about their zval, so that their zvals are recreated on each access: ok
Objects can hold info about their zval and keep it until zval's get free() : ok
string(11) "Script done"


object(V8\ReturnValue)#12 (2) {
  ["isolate":"V8\ReturnValue":private]=>
  object(v8Tests\TrackingDtors\Isolate)#2 (4) {
    ["time_limit":"V8\Isolate":private]=>
    float(0)
    ["time_limit_hit":"V8\Isolate":private]=>
    bool(false)
    ["memory_limit":"V8\Isolate":private]=>
    int(0)
    ["memory_limit_hit":"V8\Isolate":private]=>
    bool(false)
  }
  ["context":"V8\ReturnValue":private]=>
  object(V8\Context)#4 (1) {
    ["isolate":"V8\Context":private]=>
    object(v8Tests\TrackingDtors\Isolate)#2 (4) {
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
}

Object representation (outside of context):
-------------------------------------------
object(V8\FunctionCallbackInfo)#10 (8) {
  ["isolate":"V8\CallbackInfo":private]=>
  object(v8Tests\TrackingDtors\Isolate)#2 (4) {
    ["time_limit":"V8\Isolate":private]=>
    float(0)
    ["time_limit_hit":"V8\Isolate":private]=>
    bool(false)
    ["memory_limit":"V8\Isolate":private]=>
    int(0)
    ["memory_limit_hit":"V8\Isolate":private]=>
    bool(false)
  }
  ["context":"V8\CallbackInfo":private]=>
  object(V8\Context)#4 (1) {
    ["isolate":"V8\Context":private]=>
    object(v8Tests\TrackingDtors\Isolate)#2 (4) {
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
  ["this":"V8\CallbackInfo":private]=>
  object(V8\ObjectValue)#11 (2) {
    ["isolate":"V8\Value":private]=>
    object(v8Tests\TrackingDtors\Isolate)#2 (4) {
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
    object(V8\Context)#4 (1) {
      ["isolate":"V8\Context":private]=>
      object(v8Tests\TrackingDtors\Isolate)#2 (4) {
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
  }
  ["holder":"V8\CallbackInfo":private]=>
  object(V8\ObjectValue)#11 (2) {
    ["isolate":"V8\Value":private]=>
    object(v8Tests\TrackingDtors\Isolate)#2 (4) {
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
    object(V8\Context)#4 (1) {
      ["isolate":"V8\Context":private]=>
      object(v8Tests\TrackingDtors\Isolate)#2 (4) {
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
  }
  ["return_value":"V8\CallbackInfo":private]=>
  object(V8\ReturnValue)#12 (2) {
    ["isolate":"V8\ReturnValue":private]=>
    object(v8Tests\TrackingDtors\Isolate)#2 (4) {
      ["time_limit":"V8\Isolate":private]=>
      float(0)
      ["time_limit_hit":"V8\Isolate":private]=>
      bool(false)
      ["memory_limit":"V8\Isolate":private]=>
      int(0)
      ["memory_limit_hit":"V8\Isolate":private]=>
      bool(false)
    }
    ["context":"V8\ReturnValue":private]=>
    object(V8\Context)#4 (1) {
      ["isolate":"V8\Context":private]=>
      object(v8Tests\TrackingDtors\Isolate)#2 (4) {
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
  }
  ["arguments":"V8\FunctionCallbackInfo":private]=>
  array(2) {
    [0]=>
    object(V8\StringValue)#13 (1) {
      ["isolate":"V8\Value":private]=>
      object(v8Tests\TrackingDtors\Isolate)#2 (4) {
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
    [1]=>
    object(V8\ObjectValue)#6 (2) {
      ["isolate":"V8\Value":private]=>
      object(v8Tests\TrackingDtors\Isolate)#2 (4) {
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
      object(V8\Context)#4 (1) {
        ["isolate":"V8\Context":private]=>
        object(v8Tests\TrackingDtors\Isolate)#2 (4) {
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
    }
  }
  ["new_target":"V8\FunctionCallbackInfo":private]=>
  object(V8\UndefinedValue)#14 (1) {
    ["isolate":"V8\Value":private]=>
    object(v8Tests\TrackingDtors\Isolate)#2 (4) {
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
  ["is_constructor_call":"V8\FunctionCallbackInfo":private]=>
  bool(false)
}


We are done for now
FunctionObject dies now!
Isolate dies now!
