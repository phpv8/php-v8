--TEST--
JSON
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php
require '.tracking_dtors.php';

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);


$isolate = new V8\Isolate();
$context = new V8\Context($isolate);

$helper->header('Parse');

$res = V8\JSON::parse($context, new V8\StringValue($isolate, '"test"'));
$helper->dump($res);
$helper->line();

$res = V8\JSON::parse($context, new V8\StringValue($isolate, '[]'));
$helper->dump($res);
$helper->line();

$res = V8\JSON::parse($context, new V8\StringValue($isolate, '{}'));
$helper->dump($res);
$helper->line();

try {
    V8\JSON::parse($context, new V8\StringValue($isolate, '[123}'));
} catch (\V8\Exceptions\TryCatchException $e) {
    $helper->exception_export($e);
    $helper->line();
}

$helper->header('Stringify');

$res = V8\JSON::stringify($context, new V8\StringValue($isolate, 'test'));
$helper->dump($res);
$helper->line();

$obj_inner = new \V8\ObjectValue($context);
$obj_inner->set($context, new \V8\StringValue($isolate, 'bar'), new \V8\StringValue($isolate, 'baz'));
$obj = new \V8\ObjectValue($context);
$obj->set($context, new \V8\StringValue($isolate, 'foo'), $obj_inner);


$res = V8\JSON::stringify($context, $obj);
$helper->dump($res);
$helper->line();

$res = V8\JSON::stringify($context, $obj, new \V8\StringValue($isolate, '    '));
$helper->dump($res);
$helper->line();



?>
--EXPECT--
Parse:
------
object(V8\StringValue)#6 (1) {
  ["isolate":"V8\Value":private]=>
  object(V8\Isolate)#3 (0) {
  }
}

object(V8\ArrayObject)#7 (2) {
  ["isolate":"V8\Value":private]=>
  object(V8\Isolate)#3 (0) {
  }
  ["context":"V8\ObjectValue":private]=>
  object(V8\Context)#4 (1) {
    ["isolate":"V8\Context":private]=>
    object(V8\Isolate)#3 (0) {
    }
  }
}

object(V8\ObjectValue)#5 (2) {
  ["isolate":"V8\Value":private]=>
  object(V8\Isolate)#3 (0) {
  }
  ["context":"V8\ObjectValue":private]=>
  object(V8\Context)#4 (1) {
    ["isolate":"V8\Context":private]=>
    object(V8\Isolate)#3 (0) {
    }
  }
}

V8\Exceptions\TryCatchException: SyntaxError: Unexpected token } in JSON at position 4

Stringify:
----------
string(6) ""test""

string(21) "{"foo":{"bar":"baz"}}"

string(43) "{
    "foo": {
        "bar": "baz"
    }
}"
