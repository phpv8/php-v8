--TEST--
V8\PromiseObject - object-specific methods
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);

$isolate = new \V8\Isolate();
$context = new V8\Context($isolate);


$then = new \V8\FunctionObject($context, function (\V8\FunctionCallbackInfo $args) {
   echo 'Resolved: ', $args->arguments()[0]->value(), PHP_EOL;
   // var_dump($args->arguments());
   $args->getReturnValue()->set(new V8\StringValue($args->getIsolate(), $args->arguments()[0]->value() . ' +'));
});

$catch = new \V8\FunctionObject($context, function (\V8\FunctionCallbackInfo $args) {
    echo 'Caught: ', $args->arguments()[0]->value(), PHP_EOL;
    // var_dump($args->arguments());
    $args->getIsolate()->throwException($args->getContext(), new V8\StringValue($args->getIsolate(), $args->arguments()[0]->value() . ' +'));
});


$helper->header('Promise::then()');
$value = new V8\PromiseObject($context);

$helper->assert('Promise has no handlers', $value->hasHandler(), false);
$res = $value->then($context, $then);
$helper->assert('Promise has handlers', $value->hasHandler(), true);

$helper->assert('Result of setting then is promise', $res instanceof \V8\PromiseObject);
$helper->assert('Result of setting then is not the same promise', $res !== $value);
$helper->line();

$helper->assert('Promise is pending', $value->state(), \V8\PromiseObject::STATE_PENDING);
$value->resolve($context, new \V8\StringValue($isolate, "resolved 1 "));
$helper->assert('Promise is fulfilled', $value->state(), \V8\PromiseObject::STATE_FULFILLED);
$helper->inline('Promise result:', $value->result()->value());
$res = $value->resolve($context, new \V8\StringValue($isolate, "resolved2 "));
$helper->message('Promise handler should not be invoked on multiple resolve');
$helper->inline('Promise result:', $value->result()->value());
$res = $value->reject($context, new \V8\StringValue($isolate, "rejected"));
$helper->message('Promise handler should not be invoked on reject when resolved');
$helper->inline('Promise result:', $value->result()->value());
$helper->line();

$helper->header('Resolving a chain');

$value  = new V8\PromiseObject($context);

$v2 = $value->then($context, $then);
$v3 = $v2->then($context, $then);
$v3->then($context, $then);

$value->resolve($context, new \V8\StringValue($isolate, "resolved 1"));
$v2->resolve($context, new \V8\StringValue($isolate, "resolved 2"));
$v3->resolve($context, new \V8\StringValue($isolate, "resolved 3"));

$helper->line();

$value  = new V8\PromiseObject($context);
$v2 = $value->then($context, $then);
$v3 = $v2->then($context, $then);
$v3->then($context, $then);

$v2->resolve($context, new \V8\StringValue($isolate, "resolved 2"));
$v3->resolve($context, new \V8\StringValue($isolate, "resolved 3"));
$value->resolve($context, new \V8\StringValue($isolate, "resolved 1"));

$helper->line();

$value  = new V8\PromiseObject($context);
$v2 = $value->then($context, $then);
$v3 = $v2->then($context, $then);
$v3->then($context, $then);

$v3->resolve($context, new \V8\StringValue($isolate, "resolved 3"));
$v2->resolve($context, new \V8\StringValue($isolate, "resolved 2"));
$value->resolve($context, new \V8\StringValue($isolate, "resolved 1"));
$helper->space();



$helper->header('Promise::catch()');
$value = new V8\PromiseObject($context);

$helper->assert('Promise has no handlers', $value->hasHandler(), false);
$res = $value->catch($context, $catch);
$helper->assert('Promise has handlers', $value->hasHandler(), true);

$helper->assert('Result of setting catch is promise', $res instanceof \V8\PromiseObject);
$helper->assert('Result of setting catch is not the same promise', $res !== $value);
$helper->line();

$helper->assert('Promise is pending', $value->state(), \V8\PromiseObject::STATE_PENDING);
$value->reject($context, new \V8\StringValue($isolate, "rejected 1"));
$helper->assert('Promise is rejected', $value->state(), \V8\PromiseObject::STATE_REJECTED);
$helper->inline('Promise result:', $value->result()->value());
$res = $value->resolve($context, new \V8\StringValue($isolate, "rejected 2"));
$helper->message('Promise handler should not be invoked on multiple reject');
$helper->inline('Promise result:', $value->result()->value());
$res = $value->resolve($context, new \V8\StringValue($isolate, "resolved"));
$helper->message('Promise handler should not be invoked on resolve when rejected');
$helper->inline('Promise result:', $value->result()->value());

$helper->line();

$helper->header('Rejecting a chain');

$value  = new V8\PromiseObject($context);
$v2 = $value->catch($context, $catch);
$v3 = $v2->catch($context, $catch);
$v3->catch($context, $catch);

$value->reject($context, new \V8\StringValue($isolate, "rejected 1"));
$v2->reject($context, new \V8\StringValue($isolate, "rejected 2"));
$v3->reject($context, new \V8\StringValue($isolate, "rejected 3"));

$helper->line();

$value  = new V8\PromiseObject($context);
$v2 = $value->catch($context, $catch);
$v3 = $v2->catch($context, $catch);
$v3->catch($context, $catch);

$v2->reject($context, new \V8\StringValue($isolate, "rejected 2"));
$v3->reject($context, new \V8\StringValue($isolate, "rejected 3"));
$value->reject($context, new \V8\StringValue($isolate, "rejected 1"));

$helper->line();

$value  = new V8\PromiseObject($context);
$v2 = $value->catch($context, $catch);
$v3 = $v2->catch($context, $catch);
$v3->catch($context, $catch);

$v3->reject($context, new \V8\StringValue($isolate, "rejected 3"));
$v2->reject($context, new \V8\StringValue($isolate, "rejected 2"));
$value->reject($context, new \V8\StringValue($isolate, "rejected 1"));


?>
--EXPECT--
Promise::then():
----------------
Promise has no handlers: ok
Promise has handlers: ok
Result of setting then is promise: ok
Result of setting then is not the same promise: ok

Promise is pending: ok
Resolved: resolved 1 
Promise is fulfilled: ok
Promise result:: resolved 1 
Promise handler should not be invoked on multiple resolve
Promise result:: resolved2 
Promise handler should not be invoked on reject when resolved
Promise result:: rejected

Resolving a chain:
------------------
Resolved: resolved 1
Resolved: resolved 1 +
Resolved: resolved 1 + +

Resolved: resolved 2
Resolved: resolved 2 +
Resolved: resolved 1

Resolved: resolved 3
Resolved: resolved 2
Resolved: resolved 1


Promise::catch():
-----------------
Promise has no handlers: ok
Promise has handlers: ok
Result of setting catch is promise: ok
Result of setting catch is not the same promise: ok

Promise is pending: ok
Caught: rejected 1
Promise is rejected: ok
Promise result:: rejected 1
Promise handler should not be invoked on multiple reject
Promise result:: rejected 2
Promise handler should not be invoked on resolve when rejected
Promise result:: resolved

Rejecting a chain:
------------------
Caught: rejected 1
Caught: rejected 1 +
Caught: rejected 1 + +

Caught: rejected 2
Caught: rejected 2 +
Caught: rejected 1

Caught: rejected 3
Caught: rejected 2
Caught: rejected 1
