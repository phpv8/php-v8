--TEST--
V8\ProxyObject
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

$target  = new \V8\ObjectValue($context);
$handler = new \V8\ObjectValue($context);
$value   = new V8\ProxyObject($context, $target, $handler);

$helper->assert('Proxy returns valid target', $value->getTarget(), $target);
$helper->assert('Proxy returns valid handler', $value->getHandler(), $handler);
$helper->assert('Proxy is not revoked', $value->isRevoked(), false);
$value->revoke();
$helper->assert('Proxy is now revoked', $value->isRevoked(), true);
$helper->assert('Proxy returns valid target', $value->getTarget(), $target);
$helper->assert('Proxy returns null handler', $value->getHandler() instanceof V8\NullValue);


?>
--EXPECT--
Proxy returns valid target: ok
Proxy returns valid handler: ok
Proxy is not revoked: ok
Proxy is now revoked: ok
Proxy returns valid target: ok
Proxy returns null handler: ok
