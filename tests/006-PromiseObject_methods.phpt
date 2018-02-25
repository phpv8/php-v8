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
    // echo 'Returning: ', $args->arguments()[0]->value() . ' +', PHP_EOL;
    // var_dump($args->arguments());
    $args->getReturnValue()->set(new V8\StringValue($args->getIsolate(), $args->arguments()[0]->value() . ' +'));
});

$catch = new \V8\FunctionObject($context, function (\V8\FunctionCallbackInfo $args) {
    echo 'Caught: ', $args->arguments()[0]->value(), PHP_EOL;
    // echo 'Returning: ', $args->arguments()[0]->value() . ' +', PHP_EOL;
    // var_dump($args->arguments());
    $args->getIsolate()->throwException($args->getContext(), new V8\StringValue($args->getIsolate(), $args->arguments()[0]->value() . ' +'));
});


$helper->header('Promise::then()');

$resolver = new V8\PromiseObject\ResolverObject($context);

$helper->assert('Promise has no handlers', $resolver->hasHandler(), false);
$res = $resolver->then($context, $then);
$helper->assert('Promise has handlers', $resolver->hasHandler(), true);

$helper->assert('Result of setting then is promise', $res instanceof \V8\PromiseObject);
$helper->assert('Result of setting then is not the same promise', $res !== $resolver);
$helper->line();

$helper->assert('Promise is pending', $resolver->state(), \V8\PromiseObject::STATE_PENDING);
$resolver->resolve($context, new \V8\StringValue($isolate, "resolved 1 "));
$helper->assert('Promise is fulfilled', $resolver->state(), \V8\PromiseObject::STATE_FULFILLED);
$helper->inline('Promise result:', $resolver->result()->value());
$res = $resolver->resolve($context, new \V8\StringValue($isolate, "resolved2 "));
$helper->message('Promise handler should not be invoked on multiple resolve');
$helper->inline('Promise result:', $resolver->result()->value());
$res = $resolver->reject($context, new \V8\StringValue($isolate, "rejected"));
$helper->message('Promise handler should not be invoked on reject when resolved');
$helper->inline('Promise result:', $resolver->result()->value());
$helper->line();

$helper->header('Resolving a chain');

$resolver = new V8\PromiseObject\ResolverObject($context);

$v2 = $resolver->then($context, $then);
$v3 = $v2->then($context, $then);
$v3->then($context, $then);

$resolver->resolve($context, new \V8\StringValue($isolate, "resolved 1"));

$helper->line();


$helper->header('Promise::catch()');

$resolver = new V8\PromiseObject\ResolverObject($context);

$helper->assert('Promise has no handlers', $resolver->hasHandler(), false);
$res = $resolver->catch($context, $catch);
$helper->assert('Promise has handlers', $resolver->hasHandler(), true);

$helper->assert('Result of setting catch is promise', $res instanceof \V8\PromiseObject);
$helper->assert('Result of setting catch is not the same promise', $res !== $resolver);
$helper->line();

$helper->assert('Promise is pending', $resolver->state(), \V8\PromiseObject::STATE_PENDING);
$resolver->reject($context, new \V8\StringValue($isolate, "rejected 1"));
$helper->assert('Promise is rejected', $resolver->state(), \V8\PromiseObject::STATE_REJECTED);
$helper->inline('Promise result:', $resolver->result()->value());
$res = $resolver->resolve($context, new \V8\StringValue($isolate, "rejected 2"));
$helper->message('Promise handler should not be invoked on multiple reject');
$helper->inline('Promise result:', $resolver->result()->value());
$res = $resolver->resolve($context, new \V8\StringValue($isolate, "resolved"));
$helper->message('Promise handler should not be invoked on resolve when rejected');
$helper->inline('Promise result:', $resolver->result()->value());

$helper->line();

$helper->header('Rejecting a chain');

$resolver = new V8\PromiseObject\ResolverObject($context);

$v2 = $resolver->catch($context, $catch);
$v3 = $v2->catch($context, $catch);
$v3->catch($context, $catch);

$resolver->reject($context, new \V8\StringValue($isolate, "rejected 1"));

$helper->line();

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
Promise result:: resolved 1 
Promise handler should not be invoked on reject when resolved
Promise result:: resolved 1 

Resolving a chain:
------------------
Resolved: resolved 1
Resolved: resolved 1 +
Resolved: resolved 1 + +

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
Promise result:: rejected 1
Promise handler should not be invoked on resolve when rejected
Promise result:: rejected 1

Rejecting a chain:
------------------
Caught: rejected 1
Caught: rejected 1 +
Caught: rejected 1 + +
