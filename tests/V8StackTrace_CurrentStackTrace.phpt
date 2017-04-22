--TEST--
V8\StackTrace::CurrentStackTrace()
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
$global_template = new v8Tests\TrackingDtors\ObjectTemplate($isolate);

$stack_trace = null;

$current_stack_trace_func_tpl = new \v8Tests\TrackingDtors\FunctionTemplate($isolate, function (\V8\FunctionCallbackInfo $args) use ($v8_helper, &$stack_trace) {
    $isolate = $args->GetIsolate();
    $context = $args->GetContext();

    if (count($args->Arguments())) {
        $frame_limit = $args->Arguments()[0]->NumberValue($context);
    } else {
        $frame_limit = 10;
    }

    $stack_trace = \V8\StackTrace::CurrentStackTrace($isolate, $frame_limit);

    echo 'totally ', $stack_trace->GetFrameCount(), ' frames:', PHP_EOL;

    $arr = $v8_helper->getStackTraceFramesAsArray($context, $stack_trace->GetFrames());

    $args->GetReturnValue()->Set($arr);
});

$global_template->Set(new \V8\StringValue($isolate, 'current_stack_trace'), $current_stack_trace_func_tpl);
$context = new v8Tests\TrackingDtors\Context($isolate, $global_template);
$v8_helper->injectConsoleLog($context);

$source    =
'
"use strict";

function print_trace(trace) {
    console.log("[");
    for (var frame in trace) {
        console.log("    ", JSON.stringify(trace[frame]));
    }
    console.log("]");
}

function get_trace(frame_limit, options) {
    var trace = current_stack_trace(frame_limit, options);
    print_trace(trace);
    console.log();
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

get_trace(0); // zero trace is fine, though, makes no sense
get_trace(1);
get_trace(2);


new TestWithConstructor(1, -1);

recursive_get_trace(100, 10);

get_trace(2, -1); // as option are bit flags, -1 will lead to all options set


// Js implementation:
function stackTrace() {
    var err = new Error();

    return err.stack;
}

console.log("JS-land stack trace:");
console.log(stackTrace());
console.log();
console.log();

';
$file_name = 'test.js';

$script = new v8Tests\TrackingDtors\Script($context, new \V8\StringValue($isolate, $source), new \V8\ScriptOrigin($file_name));
$res = $script->Run($context);



$res = null;
$script = null;
$global_template = null;
$current_stack_trace_func_tpl = null;
$stack_trace = null;
$context = null;
$isolate = null;


echo 'END', PHP_EOL;

// EXPECTF: ---/"scriptId"\:\d+/
// EXPECTF: +++"scriptId":%d
?>
--EXPECTF--
totally 0 frames:
[
]

totally 1 frames:
[
    {"line":13,"column":17,"scriptId":%d,"scriptName":"test.js","scriptNameOrSourceURL":"test.js","functionName":"get_trace","isEval":false,"isConstructor":false}
]

totally 2 frames:
[
    {"line":13,"column":17,"scriptId":%d,"scriptName":"test.js","scriptNameOrSourceURL":"test.js","functionName":"get_trace","isEval":false,"isConstructor":false}
    {"line":32,"column":1,"scriptId":%d,"scriptName":"test.js","scriptNameOrSourceURL":"test.js","functionName":"","isEval":false,"isConstructor":false}
]

totally 1 frames:
[
    {"line":13,"column":17,"scriptId":%d,"scriptName":"test.js","scriptNameOrSourceURL":"test.js","functionName":"get_trace","isEval":false,"isConstructor":false}
]

totally 10 frames:
[
    {"line":13,"column":17,"scriptId":%d,"scriptName":"test.js","scriptNameOrSourceURL":"test.js","functionName":"get_trace","isEval":false,"isConstructor":false}
    {"line":27,"column":12,"scriptId":%d,"scriptName":"test.js","scriptNameOrSourceURL":"test.js","functionName":"recursive_get_trace","isEval":false,"isConstructor":false}
    {"line":24,"column":16,"scriptId":%d,"scriptName":"test.js","scriptNameOrSourceURL":"test.js","functionName":"recursive_get_trace","isEval":false,"isConstructor":false}
    {"line":24,"column":16,"scriptId":%d,"scriptName":"test.js","scriptNameOrSourceURL":"test.js","functionName":"recursive_get_trace","isEval":false,"isConstructor":false}
    {"line":24,"column":16,"scriptId":%d,"scriptName":"test.js","scriptNameOrSourceURL":"test.js","functionName":"recursive_get_trace","isEval":false,"isConstructor":false}
    {"line":24,"column":16,"scriptId":%d,"scriptName":"test.js","scriptNameOrSourceURL":"test.js","functionName":"recursive_get_trace","isEval":false,"isConstructor":false}
    {"line":24,"column":16,"scriptId":%d,"scriptName":"test.js","scriptNameOrSourceURL":"test.js","functionName":"recursive_get_trace","isEval":false,"isConstructor":false}
    {"line":24,"column":16,"scriptId":%d,"scriptName":"test.js","scriptNameOrSourceURL":"test.js","functionName":"recursive_get_trace","isEval":false,"isConstructor":false}
    {"line":24,"column":16,"scriptId":%d,"scriptName":"test.js","scriptNameOrSourceURL":"test.js","functionName":"recursive_get_trace","isEval":false,"isConstructor":false}
    {"line":24,"column":16,"scriptId":%d,"scriptName":"test.js","scriptNameOrSourceURL":"test.js","functionName":"recursive_get_trace","isEval":false,"isConstructor":false}
]

totally 2 frames:
[
    {"line":13,"column":17,"scriptId":%d,"scriptName":"test.js","scriptNameOrSourceURL":"test.js","functionName":"get_trace","isEval":false,"isConstructor":false}
    {"line":39,"column":1,"scriptId":%d,"scriptName":"test.js","scriptNameOrSourceURL":"test.js","functionName":"","isEval":false,"isConstructor":false}
]

JS-land stack trace:
Error
    at stackTrace (test.js:44:15)
    at test.js:50:13


Script dies now!
ObjectTemplate dies now!
FunctionTemplate dies now!
Context dies now!
Isolate dies now!
END
