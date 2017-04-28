--TEST--
V8\FunctionObject::Call()
--SKIPIF--
<?php if (!extension_loaded("v8")) {
    print "skip";
} ?>
--FILE--
<?php

/** @var \Phpv8Testsuite $helper */
$helper = require '.testsuite.php';

require '.v8-helpers.php';
$v8_helper = new \PhpV8Helpers($helper);

// See test case from v8 test suite
//THREADED_TEST(FunctionCall) {
//  LocalContext context;
//  v8::Isolate* isolate = context->GetIsolate();
//  v8::HandleScope scope(isolate);

$isolate = new \V8\Isolate();
$context = new \V8\Context($isolate);

//  CompileRun(
//      "function Foo() {"
//      "  var result = [];"
//      "  for (var i = 0; i < arguments.length; i++) {"
//      "    result.push(arguments[i]);"
//      "  }"
//      "  return result;"
//      "}"
//      "function ReturnThisSloppy() {"
//      "  return this;"
//      "}"
//      "function ReturnThisStrict() {"
//      "  'use strict';"
//      "  return this;"
//      "}");

$v8_helper->CompileRun(
    $context, ""
    . "function Foo() {"
    . "  var result = [];"
    . "  for (var i = 0; i < arguments.length; i++) {"
    . "    result.push(arguments[i]);"
    . "  }"
    . "  return result;"
    . "}"
    . "function ReturnThisSloppy() {"
    . "  return this;"
    . "}"
    . "function ReturnThisStrict() {"
    . "  'use strict';"
    . "  return this;"
    . "}"
);

//  Local<Function> Foo = Local<Function>::Cast(context->Global()->Get(v8_str("Foo")));
$Foo = $context->GlobalObject()->Get($context, new \V8\StringValue($isolate, 'Foo'));

//  Local<Function> ReturnThisSloppy = Local<Function>::Cast(context->Global()->Get(v8_str("ReturnThisSloppy")));
$ReturnThisSloppy = $context->GlobalObject()->Get($context, new \V8\StringValue($isolate, 'ReturnThisSloppy'));

//  Local<Function> ReturnThisStrict = Local<Function>::Cast(context->Global()->Get(v8_str("ReturnThisStrict")));
$ReturnThisStrict = $context->GlobalObject()->Get($context, new \V8\StringValue($isolate, 'ReturnThisStrict'));

//  v8::Handle<Value>* args0 = NULL;
$args0 = [];
//  Local<v8::Array> a0 = Local<v8::Array>::Cast(Foo->Call(Foo, 0, args0));
$a0 = $Foo->Call($context, $Foo, $args0);
//  CHECK_EQ(0u, a0->Length());
$v8_helper->CHECK_EQ(0, $a0->Length(), '0, $a0->Length()');
$helper->line();

//  v8::Handle<Value> args1[] = {v8_num(1.1)};
$args1 = [new \V8\NumberValue($isolate, 1.1)];
//  Local<v8::Array> a1 = Local<v8::Array>::Cast(Foo->Call(Foo, 1, args1));
$a1 = $Foo->Call($context, $Foo, $args1);
//  CHECK_EQ(1u, a1->Length());
$v8_helper->CHECK_EQ(1, $a1->Length(), '1, $a1->Length()');
//  CHECK_EQ(1.1, a1->Get(v8::Integer::New(isolate, 0))->NumberValue());
$v8_helper->CHECK_EQ(1.1, $a1->Get($context, new \V8\IntegerValue($isolate, 0))->Value(), '1.1, $a1->Get($context, 0)->Value()');
$helper->line();

//  v8::Handle<Value> args2[] = {v8_num(2.2), v8_num(3.3)};
$args2 = [new \V8\NumberValue($isolate, 2.2), new \V8\NumberValue($isolate, 3.3)];
//  Local<v8::Array> a2 = Local<v8::Array>::Cast(Foo->Call(Foo, 2, args2));
$a2 = $Foo->Call($context, $Foo, $args2);
//  CHECK_EQ(2u, a2->Length());
$v8_helper->CHECK_EQ(2, $a2->Length(), '2, $a2->Length()');
//  CHECK_EQ(2.2, a2->Get(v8::Integer::New(isolate, 0))->NumberValue());
$v8_helper->CHECK_EQ(2.2, $a2->Get($context, new \V8\IntegerValue($isolate, 0))->Value(), '2.2, $a2->Get($context, 0)->Value()');
//  CHECK_EQ(3.3, a2->Get(v8::Integer::New(isolate, 1))->NumberValue());
$v8_helper->CHECK_EQ(3.3, $a2->Get($context, new \V8\IntegerValue($isolate, 1))->Value(), '3.3, $a2->Get($context, 1)->Value()');
$helper->line();


//  v8::Handle<Value> args3[] = {v8_num(4.4), v8_num(5.5), v8_num(6.6)};
$args3 = [new \V8\NumberValue($isolate, 4.4), new \V8\NumberValue($isolate, 5.5), new \V8\NumberValue($isolate, 6.6)];
//  Local<v8::Array> a3 = Local<v8::Array>::Cast(Foo->Call(Foo, 3, args3));
$a3 = $Foo->Call($context, $Foo, $args3);
//  CHECK_EQ(3u, a3->Length());
$v8_helper->CHECK_EQ(3, $a3->Length(), '3, $a3->Length()');
//  CHECK_EQ(4.4, a3->Get(v8::Integer::New(isolate, 0))->NumberValue());
$v8_helper->CHECK_EQ(4.4, $a3->Get($context, new \V8\IntegerValue($isolate, 0))->Value(), '4.4, $a3->Get($context, 0)->Value()');
//  CHECK_EQ(5.5, a3->Get(v8::Integer::New(isolate, 1))->NumberValue());
$v8_helper->CHECK_EQ(5.5, $a3->Get($context, new \V8\IntegerValue($isolate, 1))->Value(), '5.5, $a3->Get($context, 1)->Value()');
//  CHECK_EQ(6.6, a3->Get(v8::Integer::New(isolate, 2))->NumberValue());
$v8_helper->CHECK_EQ(6.6, $a3->Get($context, new \V8\IntegerValue($isolate, 2))->Value(), '6.6, $a3->Get($context, 2)->Value()');
$helper->line();

//  v8::Handle<Value> args4[] = {v8_num(7.7), v8_num(8.8), v8_num(9.9), v8_num(10.11)};
$args4 = [new \V8\NumberValue($isolate, 7.7), new \V8\NumberValue($isolate, 8.8), new \V8\NumberValue($isolate, 9.9), new \V8\NumberValue($isolate, 10.11)];
//  Local<v8::Array> a4 = Local<v8::Array>::Cast(Foo->Call(Foo, 4, args4));
$a4 = $Foo->Call($context, $Foo, $args4);
//  CHECK_EQ(4u, a4->Length());
$v8_helper->CHECK_EQ(4, $a4->Length(), '4, $a4->Length()');
//  CHECK_EQ(7.7, a4->Get(v8::Integer::New(isolate, 0))->NumberValue());
$v8_helper->CHECK_EQ(7.7, $a4->Get($context, new \V8\IntegerValue($isolate, 0))->Value(), '7.7, $a4->Get($context, 0)->Value()');
//  CHECK_EQ(8.8, a4->Get(v8::Integer::New(isolate, 1))->NumberValue());
$v8_helper->CHECK_EQ(8.8, $a4->Get($context, new \V8\IntegerValue($isolate, 1))->Value(), '8.8, $a4->Get($context, 1)->Value()');
//  CHECK_EQ(9.9, a4->Get(v8::Integer::New(isolate, 2))->NumberValue());
$v8_helper->CHECK_EQ(9.9, $a4->Get($context, new \V8\IntegerValue($isolate, 2))->Value(), '9.9, $a4->Get($context, 2)->Value()');
//  CHECK_EQ(10.11, a4->Get(v8::Integer::New(isolate, 3))->NumberValue());
$v8_helper->CHECK_EQ(10.11, $a4->Get($context, new \V8\IntegerValue($isolate, 3))->Value(), '10.11, $a4->Get($context, 3)->Value()');
$helper->line();

//  Local<v8::Value> r1 = ReturnThisSloppy->Call(v8::Undefined(isolate), 0, NULL);
$r1 = $ReturnThisSloppy->Call($context, new \V8\UndefinedValue($isolate), []);
//  CHECK(r1->StrictEquals(context->Global()));
$v8_helper->CHECK($r1->StrictEquals($context->GlobalObject()), '$r1->StrictEquals($context->GlobalObject())');
//  Local<v8::Value> r2 = ReturnThisSloppy->Call(v8::Null(isolate), 0, NULL);
$r2 = $ReturnThisSloppy->Call($context, new \V8\NullValue($isolate), []);
//  CHECK(r2->StrictEquals(context->Global()));
$v8_helper->CHECK($r2->StrictEquals($context->GlobalObject()), '$r2->StrictEquals($context->GlobalObject())');
//  Local<v8::Value> r3 = ReturnThisSloppy->Call(v8_num(42), 0, NULL);
/** @var \V8\NumberObject $r3 */
$r3 = $ReturnThisSloppy->Call($context, new \V8\NumberValue($isolate, 42), []);
$helper->value_instanceof($r3, '\V8\NumberObject');
//  CHECK(r3->IsNumberObject());
$v8_helper->CHECK($r3->IsNumberObject(), '$r3->IsNumberObject()');
//  CHECK_EQ(42.0, r3.As<v8::NumberObject>()->ValueOf());
$v8_helper->CHECK_EQ(42.0, $r3->ValueOf(), '42.0, $r3->ValueOf()');
//  Local<v8::Value> r4 = ReturnThisSloppy->Call(v8_str("hello"), 0, NULL);
$helper->line();

/** @var \V8\StringObject $r4 */
$r4 = $ReturnThisSloppy->Call($context, new \V8\StringValue($isolate, 'hello'), []);
$helper->value_instanceof($r4, '\V8\StringObject');
//  CHECK(r4->IsStringObject());
$v8_helper->CHECK($r4->IsStringObject(), '$r4->IsStringObject()');
//  CHECK(r4.As<v8::StringObject>()->ValueOf()->StrictEquals(v8_str("hello")));
$v8_helper->CHECK($r4->ValueOf()->StrictEquals(new \V8\StringValue($isolate, 'hello')), '$r4->ValueOf()->StrictEquals(new \V8\StringValue($isolate, \'hello\'))');
$helper->line();

//  Local<v8::Value> r5 = ReturnThisSloppy->Call(v8::True(isolate), 0, NULL);
/** @var \V8\BooleanObject $r5 */
$r5 = $ReturnThisSloppy->Call($context, new \V8\BooleanValue($isolate, true), []);
$helper->value_instanceof($r5, '\V8\BooleanObject');
//  CHECK(r5->IsBooleanObject());
$v8_helper->CHECK($r5->IsBooleanObject(), '$r5->IsBooleanObject()');
//  CHECK(r5.As<v8::BooleanObject>()->ValueOf());
$v8_helper->CHECK($r5->ValueOf(), '$r5->ValueOf()');
$helper->line();

//  Local<v8::Value> r6 = ReturnThisStrict->Call(v8::Undefined(isolate), 0, NULL);
$r6 = $ReturnThisStrict->Call($context, new \V8\UndefinedValue($isolate), []);
//  CHECK(r6->IsUndefined());
$v8_helper->CHECK($r6->IsUndefined(), '$r6->IsUndefined()');
//  Local<v8::Value> r7 = ReturnThisStrict->Call(v8::Null(isolate), 0, NULL);
$r7 = $ReturnThisStrict->Call($context, new \V8\NullValue($isolate), []);
//  CHECK(r7->IsNull());
$v8_helper->CHECK($r7->IsNull(), '$r7->IsNull()');
//  Local<v8::Value> r8 = ReturnThisStrict->Call(v8_num(42), 0, NULL);
$r8 = $ReturnThisStrict->Call($context, new \V8\NumberValue($isolate, 42), []);
//  CHECK(r8->StrictEquals(v8_num(42)));
$v8_helper->CHECK($r8->StrictEquals(new \V8\NumberValue($isolate, 42)), '$r8->StrictEquals(new \V8\NumberValue($isolate, 42))');
//  Local<v8::Value> r9 = ReturnThisStrict->Call(v8_str("hello"), 0, NULL);
$r9 = $ReturnThisStrict->Call($context, new \V8\StringValue($isolate, 'hello'), []);
//  CHECK(r9->StrictEquals(v8_str("hello")));
$v8_helper->CHECK($r9->StrictEquals(new \V8\StringValue($isolate, 'hello')), '$r9->StrictEquals(new \V8\StringValue($isolate, \'hello\')');
//  Local<v8::Value> r10 = ReturnThisStrict->Call(v8::True(isolate), 0, NULL);
$r10 = $ReturnThisStrict->Call($context, new \V8\BooleanValue($isolate, true), []);
//  CHECK(r10->StrictEquals(v8::True(isolate)));
$v8_helper->CHECK($r10->StrictEquals(new \V8\BooleanValue($isolate, true)), '$r10->StrictEquals(new \V8\BooleanValue($isolate, true))');
//}

echo PHP_EOL;
echo 'We are done for now', PHP_EOL;

?>
--EXPECT--
CHECK_EQ (0, $a0->Length()): OK

CHECK_EQ (1, $a1->Length()): OK
CHECK_EQ (1.1, $a1->Get($context, 0)->Value()): OK

CHECK_EQ (2, $a2->Length()): OK
CHECK_EQ (2.2, $a2->Get($context, 0)->Value()): OK
CHECK_EQ (3.3, $a2->Get($context, 1)->Value()): OK

CHECK_EQ (3, $a3->Length()): OK
CHECK_EQ (4.4, $a3->Get($context, 0)->Value()): OK
CHECK_EQ (5.5, $a3->Get($context, 1)->Value()): OK
CHECK_EQ (6.6, $a3->Get($context, 2)->Value()): OK

CHECK_EQ (4, $a4->Length()): OK
CHECK_EQ (7.7, $a4->Get($context, 0)->Value()): OK
CHECK_EQ (8.8, $a4->Get($context, 1)->Value()): OK
CHECK_EQ (9.9, $a4->Get($context, 2)->Value()): OK
CHECK_EQ (10.11, $a4->Get($context, 3)->Value()): OK

CHECK $r1->StrictEquals($context->GlobalObject()): OK
CHECK $r2->StrictEquals($context->GlobalObject()): OK
Value is instance of \V8\NumberObject
CHECK $r3->IsNumberObject(): OK
CHECK_EQ (42.0, $r3->ValueOf()): OK

Value is instance of \V8\StringObject
CHECK $r4->IsStringObject(): OK
CHECK $r4->ValueOf()->StrictEquals(new \V8\StringValue($isolate, 'hello')): OK

Value is instance of \V8\BooleanObject
CHECK $r5->IsBooleanObject(): OK
CHECK $r5->ValueOf(): OK

CHECK $r6->IsUndefined(): OK
CHECK $r7->IsNull(): OK
CHECK $r8->StrictEquals(new \V8\NumberValue($isolate, 42)): OK
CHECK $r9->StrictEquals(new \V8\StringValue($isolate, 'hello'): OK
CHECK $r10->StrictEquals(new \V8\BooleanValue($isolate, true)): OK

We are done for now
