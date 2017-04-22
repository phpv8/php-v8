--TEST--
Built-in enum classes
--SKIPIF--
<?php if (!extension_loaded("v8")) {
    print "skip";
} ?>
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
    new V8\RegExpObject\Flags(),
    new V8\ScriptCompiler\CompileOptions,
];

foreach ($enums as $enum) {
    $helper->header('Object representation');
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
object(V8\AccessControl)#2 (0) {
}


Class constants:
----------------
V8\AccessControl::DEFAULT_ACCESS = 0
V8\AccessControl::ALL_CAN_READ = 1
V8\AccessControl::ALL_CAN_WRITE = 2


Object representation:
----------------------
object(V8\ConstructorBehavior)#3 (0) {
}


Class constants:
----------------
V8\ConstructorBehavior::kThrow = 0
V8\ConstructorBehavior::kAllow = 1


Object representation:
----------------------
object(V8\IntegrityLevel)#4 (0) {
}


Class constants:
----------------
V8\IntegrityLevel::kFrozen = 0
V8\IntegrityLevel::kSealed = 1


Object representation:
----------------------
object(V8\PropertyAttribute)#5 (0) {
}


Class constants:
----------------
V8\PropertyAttribute::None = 0
V8\PropertyAttribute::ReadOnly = 1
V8\PropertyAttribute::DontEnum = 2
V8\PropertyAttribute::DontDelete = 4


Object representation:
----------------------
object(V8\PropertyHandlerFlags)#6 (0) {
}


Class constants:
----------------
V8\PropertyHandlerFlags::kNone = 0
V8\PropertyHandlerFlags::kAllCanRead = 1
V8\PropertyHandlerFlags::kNonMasking = 2
V8\PropertyHandlerFlags::kOnlyInterceptStrings = 4


Object representation:
----------------------
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
object(V8\KeyCollectionMode)#8 (0) {
}


Class constants:
----------------
V8\KeyCollectionMode::kOwnOnly = 0
V8\KeyCollectionMode::kIncludePrototypes = 1


Object representation:
----------------------
object(V8\IndexFilter)#9 (0) {
}


Class constants:
----------------
V8\IndexFilter::kIncludeIndices = 0
V8\IndexFilter::kSkipIndices = 1


Object representation:
----------------------
object(V8\RegExpObject\Flags)#10 (0) {
}


Class constants:
----------------
V8\RegExpObject\Flags::kNone = 0
V8\RegExpObject\Flags::kGlobal = 1
V8\RegExpObject\Flags::kIgnoreCase = 2
V8\RegExpObject\Flags::kMultiline = 4
V8\RegExpObject\Flags::kSticky = 8
V8\RegExpObject\Flags::kUnicode = 16


Object representation:
----------------------
object(V8\ScriptCompiler\CompileOptions)#11 (0) {
}


Class constants:
----------------
V8\ScriptCompiler\CompileOptions::kNoCompileOptions = 0
V8\ScriptCompiler\CompileOptions::kProduceParserCache = 1
V8\ScriptCompiler\CompileOptions::kConsumeParserCache = 2
V8\ScriptCompiler\CompileOptions::kProduceCodeCache = 3
V8\ScriptCompiler\CompileOptions::kConsumeCodeCache = 4
