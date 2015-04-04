--TEST--
v8\StackTrace::CurrentStackTrace()
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--FILE--
<?php

require '.tracking_dtors.php';

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';
require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);


$isolate = new \v8Tests\TrackingDtors\Isolate();
$extensions = [];
$global_template = new v8Tests\TrackingDtors\ObjectTemplate($isolate);

$stack_trace = null;

$current_stack_trace_func_tpl = new \v8Tests\TrackingDtors\FunctionTemplate($isolate, function (\v8\FunctionCallbackInfo $args) use (&$stack_trace) {
    $isolate = $args->GetIsolate();
    $context = $args->GetContext();

    if ($args->Length()) {
        $frame_limit = $args->Arguments()[0]->NumberValue($context);
    } else {
        $frame_limit = 10;
    }

    if ($args->Length() > 1) {
        $options = $args->Arguments()[1]->NumberValue($context);
    } else {
        $options = \v8\StackTrace\StackTraceOptions::kOverview;
    }

    $stack_trace = \v8\StackTrace::CurrentStackTrace($isolate, $frame_limit, $options);

    echo 'totally ', $stack_trace->GetFrameCount(), ' frames:', PHP_EOL;

    $args->GetReturnValue()->Set($stack_trace->AsArray());
});

$global_template->Set(new \v8\StringValue($isolate, 'current_stack_trace'), $current_stack_trace_func_tpl);
$global_template->Set(new \v8\StringValue($isolate, 'print'), $v8_helper->getPrintFunctionTemplate($isolate));
$context = new v8Tests\TrackingDtors\Context($isolate, $extensions, $global_template);

$source    = /** @lang JavaScript */
'
"use strict";

function print_trace(trace) {
    print("[\\n");
    for (var frame in trace) {
        print("    ", JSON.stringify(trace[frame]));
        print("\\n");
    }
    print("]\\n");
}

function get_trace(frame_limit, options) {
    var trace = current_stack_trace(frame_limit, options);
    print_trace(trace);
    print("\\n");
}

function TestWithConstructor (frame_limit, options) {
    get_trace(frame_limit, options);
}

function recursive_get_trace(depth, frame_limit, options) {
    if (depth > 0) {
        return recursive_get_trace(depth - 1 , frame_limit, options)
    }

    return get_trace(frame_limit, options);
}


var kLineNumber = 1;
var kColumnOffset = 3;
var kScriptName = 4;
var kFunctionName = 8;
var kIsEval = 16;
var kIsConstructor = 32;
var kScriptNameOrSourceURL = 64;
var kScriptId = 128;
var kExposeFramesAcrossSecurityOrigins = 256;
var kOverview = 15;
var kDetailed = 127;

get_trace(0, kDetailed); // zero trace is fine, though, makes no sense
get_trace(1, kDetailed);
get_trace(2, kColumnOffset | kScriptId);


new TestWithConstructor(1, -1);

recursive_get_trace(100, 10, kOverview);

get_trace(2, -1); // as option are bit flags, -1 will lead to all options set


// Js implementation:
function stackTrace() {
    var err = new Error();

    return err.stack;
}

print("JS-land stack trace:\\n");
print(stackTrace());
print("\\n");
print("\\n");

';
$file_name = 'test.js';

$script = new v8Tests\TrackingDtors\Script($context, new \v8\StringValue($isolate, $source), new \v8\ScriptOrigin($file_name));
$res = $script->Run();



$res = null;
$script = null;
$global_template = null;
$current_stack_trace_func_tpl = null;
$stack_trace = null;
$context = null;
$isolate = null;


echo 'END', PHP_EOL;
?>
--EXPECT--
totally 0 frames:
[
]

totally 1 frames:
[
    {"column":17,"lineNumber":14,"scriptName":"test.js","scriptNameOrSourceURL":"test.js","isEval":false,"functionName":"get_trace","isConstructor":false}
]

totally 2 frames:
[
    {"column":17,"lineNumber":14,"scriptId":33}
    {"column":1,"lineNumber":46,"scriptId":33}
]

totally 1 frames:
[
    {"column":17,"lineNumber":14,"scriptId":33,"scriptName":"test.js","scriptNameOrSourceURL":"test.js","isEval":false,"functionName":"get_trace","isConstructor":false}
]

totally 10 frames:
[
    {"column":17,"lineNumber":14,"scriptName":"test.js","functionName":"get_trace"}
    {"column":12,"lineNumber":28,"scriptName":"test.js","functionName":"recursive_get_trace"}
    {"column":16,"lineNumber":25,"scriptName":"test.js","functionName":"recursive_get_trace"}
    {"column":16,"lineNumber":25,"scriptName":"test.js","functionName":"recursive_get_trace"}
    {"column":16,"lineNumber":25,"scriptName":"test.js","functionName":"recursive_get_trace"}
    {"column":16,"lineNumber":25,"scriptName":"test.js","functionName":"recursive_get_trace"}
    {"column":16,"lineNumber":25,"scriptName":"test.js","functionName":"recursive_get_trace"}
    {"column":16,"lineNumber":25,"scriptName":"test.js","functionName":"recursive_get_trace"}
    {"column":16,"lineNumber":25,"scriptName":"test.js","functionName":"recursive_get_trace"}
    {"column":16,"lineNumber":25,"scriptName":"test.js","functionName":"recursive_get_trace"}
]

totally 2 frames:
[
    {"column":17,"lineNumber":14,"scriptId":33,"scriptName":"test.js","scriptNameOrSourceURL":"test.js","isEval":false,"functionName":"get_trace","isConstructor":false}
    {"column":1,"lineNumber":53,"scriptId":33,"scriptName":"test.js","scriptNameOrSourceURL":"test.js","isEval":false,"functionName":"","isConstructor":false}
]

JS-land stack trace:
Error
    at stackTrace (test.js:58:15)
    at test.js:64:7

Script dies now!
FunctionTemplate dies now!
Context dies now!
ObjectTemplate dies now!
Isolate dies now!
END
