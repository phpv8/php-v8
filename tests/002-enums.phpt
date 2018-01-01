--TEST--
Built-in enum classes
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

$enums = [
    new V8\AccessControl(),
    new V8\ConstructorBehavior(),
    new V8\IntegrityLevel(),
    new V8\PropertyAttribute(),
    new V8\PropertyHandlerFlags(),
    new V8\PropertyFilter(),
    new V8\KeyCollectionMode(),
    new V8\IndexFilter(),
];

foreach ($enums as $enum) {
    $rc = new ReflectionClass($enum);

    $helper->header('Object representation');
    $helper->assert('Class is final', $rc->isFinal());
    $helper->dump($enum);
    $helper->space();

    $helper->header('Class constants');
    $helper->dump_object_constants($enum);
    $helper->space();
}


?>
--EXPECT--
Object representation:
----------------------
Class is final: ok
object(V8\AccessControl)#2 (0) {
}


Class constants:
----------------
V8\AccessControl::DEFAULT_ACCESS = 0
V8\AccessControl::ALL_CAN_READ = 1
V8\AccessControl::ALL_CAN_WRITE = 2


Object representation:
----------------------
Class is final: ok
object(V8\ConstructorBehavior)#3 (0) {
}


Class constants:
----------------
V8\ConstructorBehavior::THROW = 0
V8\ConstructorBehavior::ALLOW = 1


Object representation:
----------------------
Class is final: ok
object(V8\IntegrityLevel)#4 (0) {
}


Class constants:
----------------
V8\IntegrityLevel::FROZEN = 0
V8\IntegrityLevel::SEALED = 1


Object representation:
----------------------
Class is final: ok
object(V8\PropertyAttribute)#5 (0) {
}


Class constants:
----------------
V8\PropertyAttribute::NONE = 0
V8\PropertyAttribute::READ_ONLY = 1
V8\PropertyAttribute::DONT_ENUM = 2
V8\PropertyAttribute::DONT_DELETE = 4


Object representation:
----------------------
Class is final: ok
object(V8\PropertyHandlerFlags)#6 (0) {
}


Class constants:
----------------
V8\PropertyHandlerFlags::NONE = 0
V8\PropertyHandlerFlags::ALL_CAN_READ = 1
V8\PropertyHandlerFlags::NON_MASKING = 2
V8\PropertyHandlerFlags::ONLY_INTERCEPT_STRINGS = 4


Object representation:
----------------------
Class is final: ok
object(V8\PropertyFilter)#7 (0) {
}


Class constants:
----------------
V8\PropertyFilter::ALL_PROPERTIES = 0
V8\PropertyFilter::ONLY_WRITABLE = 1
V8\PropertyFilter::ONLY_ENUMERABLE = 2
V8\PropertyFilter::ONLY_CONFIGURABLE = 4
V8\PropertyFilter::SKIP_STRINGS = 8
V8\PropertyFilter::SKIP_SYMBOLS = 16


Object representation:
----------------------
Class is final: ok
object(V8\KeyCollectionMode)#8 (0) {
}


Class constants:
----------------
V8\KeyCollectionMode::OWN_ONLY = 0
V8\KeyCollectionMode::INCLUDE_PROTOTYPES = 1


Object representation:
----------------------
Class is final: ok
object(V8\IndexFilter)#9 (0) {
}


Class constants:
----------------
V8\IndexFilter::INCLUDE_INDICES = 0
V8\IndexFilter::SKIP_INDICES = 1
