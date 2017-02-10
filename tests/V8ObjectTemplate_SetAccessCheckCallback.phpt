--TEST--
V8\ObjectTemplate::SetAccessCheckCallback()
--SKIPIF--
<?php if (!extension_loaded("v8")) { print "skip"; }
echo 'skip ', 'see https://groups.google.com/forum/?fromgroups#!topic/v8-dev/c7LhW2bNabY';
?>
--FILE--
<?php
// See test case from v8 test suite
// TEST(PrototypeGetterAccessCheck)

/** @var \Phpv8Testsuite $helper */
//namespace test;

$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new PhpV8Helpers($helper);


//v8::Isolate* isolate = CcTest::isolate();
//v8::HandleScope handle_scope(isolate);
$isolate = new \V8\Isolate();

//  v8::Handle<v8::ObjectTemplate> global_template = v8::ObjectTemplate::New(isolate);

$global_template = new \V8\ObjectTemplate($isolate);


$g_echo_value = -1;

$EchoGetter = function ($name, \V8\PropertyCallbackInfo $info) use (&$g_echo_value) {
//    echo 'EchoGetter for ', $name, PHP_EOL;
    $info->GetReturnValue()->Set(new \V8\NumberValue($info->GetIsolate(), $g_echo_value));
};

$EchoSetter = function ($name, \V8\Value $value, \V8\PropertyCallbackInfo $info) use (&$g_echo_value) {
//    echo 'EchoSetter for ', $name, PHP_EOL;
    $g_echo_value = $value->Int32Value($info->GetContext());
};

$UnreachableGetter = function () {throw new \Exception('Unreachable Getter');};
$UnreachableSetter = function () {throw new \Exception('Unreachable Setter');};

$allowed_access = false;

$AccessBlocker = function (\V8\Context $context, \V8\ObjectValue $object) use (&$isolate, &$allowed_access, $v8_helper, $helper) {
    echo 'AccessBlocker called', PHP_EOL;
//    echo '    access ', ($allowed_access ? 'allowed' : 'denied'), PHP_EOL;

    //return $isolate->GetCurrentContext()->GlobalObject()->Equals($isolate->GetCurrentContext(), $object) || $allowed_access;
    return true;
};

$UnreachableFunction = new \V8\FunctionTemplate($isolate, function () {throw new \Exception('Unreachable function');});

//  global_template->SetAccessCheckCallbacks(AccessBlocker, NULL);
$global_template->SetAccessCheckCallback($AccessBlocker);

//  // Add an accessor accessible by cross-domain JS code.
//  global_template->SetAccessor(v8_str("accessible_prop"), EchoGetter, EchoSetter, v8::Handle<Value>(), v8::AccessControl(v8::ALL_CAN_READ | v8::ALL_CAN_WRITE));
$global_template->SetAccessor(new \V8\StringValue($isolate, 'accessible_prop'), $EchoGetter, $EchoSetter, \V8\AccessControl::ALL_CAN_READ | \V8\AccessControl::ALL_CAN_WRITE);


//  // Add an accessor that is not accessible by cross-domain JS code.
//  global_template->SetAccessor(v8_str("blocked_prop"), UnreachableGetter, UnreachableSetter, v8::Handle<Value>(), v8::DEFAULT);
$global_template->SetAccessor(new \V8\StringValue($isolate, 'blocked_prop'), $UnreachableGetter, $UnreachableSetter, \V8\AccessControl::DEFAULT_ACCESS);


//  global_template->SetAccessorProperty(v8_str("blocked_js_prop"), v8::FunctionTemplate::New(isolate, UnreachableFunction), v8::FunctionTemplate::New(isolate, UnreachableFunction), v8::None, v8::DEFAULT);
$global_template->SetAccessorProperty(new \V8\StringValue($isolate, 'blocked_js_prop'), $UnreachableFunction, $UnreachableFunction, \V8\PropertyAttribute::None, \V8\AccessControl::DEFAULT_ACCESS);


//  // Create an environment
//  v8::Local<Context> context0 = Context::New(isolate, NULL, global_template);
//  context0->Enter();
$context0 = new \V8\Context($isolate, $global_template);

//  v8::Handle<v8::Object> global0 = context0->Global();
$global0 = $context0->GlobalObject();


//  // Define a property with JS getter and setter.
//  CompileRun(
//      "function getter() { return 'getter'; };\n"
//      "function setter() { return 'setter'; }\n"
//      "Object.defineProperty(this, 'js_accessor_p', {get:getter, set:setter})");

$v8_helper->CompileRun($context0,
      "function getter() { return 'getter'; };\n" .
      "function setter() { return 'setter'; }\n" .
      "Object.defineProperty(this, 'js_accessor_p', {get:getter, set:setter})");

$getter = $global0->Get($context0, new \V8\StringValue($isolate, 'getter'));
$setter = $global0->Get($context0, new \V8\StringValue($isolate, 'setter'));

//  // And define normal element.
//  global0->Set(239, v8_str("239"));
$global0->Set($context0, 239, new \V8\StringValue($isolate, '239'));

//  // Define an element with JS getter and setter.
//  CompileRun(
//      "function el_getter() { return 'el_getter'; };\n"
//      "function el_setter() { return 'el_setter'; };\n"
//      "Object.defineProperty(this, '42', {get: el_getter, set: el_setter});");

$v8_helper->CompileRun($context0,
      "function el_getter() { return 'el_getter'; };\n" .
      "function el_setter() { return 'el_setter'; };\n" .
      "Object.defineProperty(this, '42', {get: el_getter, set: el_setter});");


//  Local<Value> el_getter = global0->Get(v8_str("el_getter"));
//  Local<Value> el_setter = global0->Get(v8_str("el_setter"));

$el_getter = $global0->Get($context0, new \V8\StringValue($isolate, 'el_getter'));
$el_setter = $global0->Get($context0, new \V8\StringValue($isolate, 'el_setter'));


//  v8::HandleScope scope1(isolate);
//
//  v8::Local<Context> context1 = Context::New(isolate);
//  context1->Enter();
$context1 = new \V8\Context($isolate);


//  v8::Handle<v8::Object> global1 = context1->Global();
$global1 = $context1->GlobalObject();


//  global1->Set(v8_str("other"), global0);
$global1->Set($context1, new \V8\StringValue($isolate, 'other'), $global0);

//  // Access blocked property.
//  CompileRun("other.blocked_prop = 1");
$v8_helper->CompileTryRun($context1, 'other.blocked_prop = 1');

//  CHECK(CompileRun("other.blocked_prop").IsEmpty());
$v8_helper->CompileTryRun($context1, 'other.blocked_prop');

//  CHECK(CompileRun("Object.getOwnPropertyDescriptor(other, 'blocked_prop')").IsEmpty());
$v8_helper->CompileTryRun($context1, "Object.getOwnPropertyDescriptor(other, 'blocked_prop')");

//  CHECK(CompileRun("propertyIsEnumerable.call(other, 'blocked_prop')").IsEmpty());
$v8_helper->CompileTryRun($context1, "propertyIsEnumerable.call(other, 'blocked_prop')");

//  // Access blocked element.
//  CHECK(CompileRun("other[239] = 1").IsEmpty());
$v8_helper->CompileTryRun($context1, "other[239] = 1");

//  CHECK(CompileRun("other[239]").IsEmpty());
$v8_helper->CompileTryRun($context1, "other[239]");

//  CHECK(CompileRun("Object.getOwnPropertyDescriptor(other, '239')").IsEmpty());
$v8_helper->CompileTryRun($context1, "Object.getOwnPropertyDescriptor(other, '239')");

//  CHECK(CompileRun("propertyIsEnumerable.call(other, '239')").IsEmpty());
$v8_helper->CompileTryRun($context1, "propertyIsEnumerable.call(other, '239')");

//  allowed_access = true;
$allowed_access = true;

//  // Now we can enumerate the property.
//  ExpectTrue("propertyIsEnumerable.call(other, '239')");
$v8_helper->ExpectTrue($context1, "propertyIsEnumerable.call(other, '239')");


//  allowed_access = false;
$allowed_access = false;

//  // Access a property with JS accessor.
//  CHECK(CompileRun("other.js_accessor_p = 2").IsEmpty());
$v8_helper->CompileTryRun($context1, "other.js_accessor_p = 2");

//  CHECK(CompileRun("other.js_accessor_p").IsEmpty());
$v8_helper->CompileTryRun($context1, "other.js_accessor_p");

//  CHECK(CompileRun("Object.getOwnPropertyDescriptor(other, 'js_accessor_p')").IsEmpty());
$v8_helper->CompileTryRun($context1, "Object.getOwnPropertyDescriptor(other, 'js_accessor_p')");

//  allowed_access = true;
$allowed_access = true;

//  ExpectString("other.js_accessor_p", "getter");
$v8_helper->ExpectString($context1, "other.js_accessor_p", "getter");


//  ExpectObject("Object.getOwnPropertyDescriptor(other, 'js_accessor_p').get", getter);
$v8_helper->ExpectObject($context1, "Object.getOwnPropertyDescriptor(other, 'js_accessor_p').get", $getter);

//  ExpectObject("Object.getOwnPropertyDescriptor(other, 'js_accessor_p').set", setter);
$v8_helper->ExpectObject($context1, "Object.getOwnPropertyDescriptor(other, 'js_accessor_p').set", $setter);

//  ExpectUndefined("Object.getOwnPropertyDescriptor(other, 'js_accessor_p').value");
$v8_helper->ExpectUndefined($context1, "Object.getOwnPropertyDescriptor(other, 'js_accessor_p').value");

//  allowed_access = false;
$allowed_access = false;

//  // Access an element with JS accessor.
//  CHECK(CompileRun("other[42] = 2").IsEmpty());
$v8_helper->CompileTryRun($context1, "other[42] = 2");

//  CHECK(CompileRun("other[42]").IsEmpty());
$v8_helper->CompileTryRun($context1, "other[42]");

//  CHECK(CompileRun("Object.getOwnPropertyDescriptor(other, '42')").IsEmpty());
$v8_helper->CompileTryRun($context1, "Object.getOwnPropertyDescriptor(other, '42')");

//  allowed_access = true;
$allowed_access = true;

//  ExpectString("other[42]", "el_getter");
$v8_helper->ExpectString($context1, "other[42]", "el_getter");

//  ExpectObject("Object.getOwnPropertyDescriptor(other, '42').get", el_getter);
$v8_helper->ExpectObject($context1, "Object.getOwnPropertyDescriptor(other, '42').get", $el_getter);

//  ExpectObject("Object.getOwnPropertyDescriptor(other, '42').set", el_setter);
$v8_helper->ExpectObject($context1, "Object.getOwnPropertyDescriptor(other, '42').set", $el_setter);

//  ExpectUndefined("Object.getOwnPropertyDescriptor(other, '42').value");
$v8_helper->ExpectUndefined($context1, "Object.getOwnPropertyDescriptor(other, '42').value");


//  allowed_access = false;
$allowed_access = false;

//  // Access accessible property
//  value = CompileRun("other.accessible_prop = 3");
//  CHECK(value->IsNumber());
//  CHECK_EQ(3, value->Int32Value());
$v8_helper->ExpectNumber($context1, "other.accessible_prop = 3", 3);
//  CHECK_EQ(3, g_echo_value);
$helper->value_matches(3, $g_echo_value);


//  value = CompileRun("other.accessible_prop");
//  CHECK(value->IsNumber());
//  CHECK_EQ(3, value->Int32Value());
$v8_helper->ExpectNumber($context1, "other.accessible_prop", 3);

//  value = CompileRun("Object.getOwnPropertyDescriptor(other, 'accessible_prop').value");
//  CHECK(value->IsNumber());
//  CHECK_EQ(3, value->Int32Value());
$v8_helper->ExpectNumber($context1, "Object.getOwnPropertyDescriptor(other, 'accessible_prop').value", 3);

//  value = CompileRun("propertyIsEnumerable.call(other, 'accessible_prop')");
//  CHECK(value->IsTrue());
$v8_helper->ExpectTrue($context1, "propertyIsEnumerable.call(other, 'accessible_prop')");

//  // Enumeration doesn't enumerate accessors from inaccessible objects in
//  // the prototype chain even if the accessors are in themselves accessible.
//  // Enumeration doesn't throw, it silently ignores what it can't access.
//  value = CompileRun(
//      "(function() {"
//      "  var obj = { '__proto__': other };"
//      "  try {"
//      "    for (var p in obj) {"
//      "      if (p == 'accessible_prop' ||"
//      "          p == 'blocked_js_prop' ||"
//      "          p == 'blocked_js_prop') {"
//      "        return false;"
//      "      }"
//      "    }"
//      "    return true;"
//      "  } catch (e) {"
//      "    return false;"
//      "  }"
//      "})()");
//  CHECK(value->IsTrue());
//}

$v8_helper->ExpectTrue($context1,
    "(function() {" .
    "  var obj = { '__proto__': other };" .
    "  try {" .
    "    for (var p in obj) {" .
    "      if (p == 'accessible_prop' ||" .
    "          p == 'blocked_js_prop' ||" .
    "          p == 'blocked_js_prop') {" .
    "        return false;" .
    "      }" .
    "    }" .
    "    return true;" .
    "  } catch (e) {" .
    "    return false;" .
    "  }" .
    "})()");


?>
--XFAIL--
Waiting for data parameter to be added to AccessCheck callback, https://groups.google.com/d/msg/v8-dev/c7LhW2bNabY/2p8U7KtgDQAJ
TODO: test null-callback
--EXPECT--
other.blocked_prop = 1: V8\Exceptions\TryCatchException: TypeError: no access
other.blocked_prop: V8\Exceptions\TryCatchException: TypeError: no access
Object.getOwnPropertyDescriptor(other, 'blocked_prop'): V8\Exceptions\TryCatchException: TypeError: no access
propertyIsEnumerable.call(other, 'blocked_prop'): V8\Exceptions\TryCatchException: TypeError: no access
other[239] = 1: V8\Exceptions\TryCatchException: TypeError: no access
other[239]: V8\Exceptions\TryCatchException: TypeError: no access
Object.getOwnPropertyDescriptor(other, '239'): V8\Exceptions\TryCatchException: TypeError: no access
propertyIsEnumerable.call(other, '239'): V8\Exceptions\TryCatchException: TypeError: no access
Expected true value is identical to actual value true
other.js_accessor_p = 2: V8\Exceptions\TryCatchException: TypeError: no access
other.js_accessor_p: V8\Exceptions\TryCatchException: TypeError: no access
Object.getOwnPropertyDescriptor(other, 'js_accessor_p'): V8\Exceptions\TryCatchException: TypeError: no access
Expected 'getter' value is identical to actual value 'getter'
Actual and expected objects are the same
Actual and expected objects are the same
Actual result for expected value is undefined
other[42] = 2: V8\Exceptions\TryCatchException: TypeError: no access
other[42]: V8\Exceptions\TryCatchException: TypeError: no access
Object.getOwnPropertyDescriptor(other, '42'): V8\Exceptions\TryCatchException: TypeError: no access
Expected 'el_getter' value is identical to actual value 'el_getter'
Actual and expected objects are the same
Actual and expected objects are the same
Actual result for expected value is undefined
Expected 3 value is identical to actual value 3
Expected 3 value is identical to actual value 3
Expected 3 value is identical to actual value 3
Expected 3 value is identical to actual value 3
Expected true value is identical to actual value true
Expected true value is identical to actual value true
