--TEST--
V8\FunctionObject::call()
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
//  v8::Isolate* isolate = context->getIsolate();
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

//  Local<Function> Foo = Local<Function>::Cast(context->Global()->get(v8_str("Foo")));
$Foo = $context->globalObject()->get($context, new \V8\StringValue($isolate, 'Foo'));

//  Local<Function> ReturnThisSloppy = Local<Function>::Cast(context->Global()->get(v8_str("ReturnThisSloppy")));
$ReturnThisSloppy = $context->globalObject()->get($context, new \V8\StringValue($isolate, 'ReturnThisSloppy'));

//  Local<Function> ReturnThisStrict = Local<Function>::Cast(context->Global()->get(v8_str("ReturnThisStrict")));
$ReturnThisStrict = $context->globalObject()->get($context, new \V8\StringValue($isolate, 'ReturnThisStrict'));

//  v8::Handle<Value>* args0 = NULL;
$args0 = [];
//  Local<v8::Array> a0 = Local<v8::Array>::Cast(Foo->Call(Foo, 0, args0));
$a0 = $Foo->call($context, $Foo, $args0);
//  CHECK_EQ(0u, a0->length());
$v8_helper->CHECK_EQ(0, $a0->length(), '0, $a0->length()');
$helper->line();

//  v8::Handle<Value> args1[] = {v8_num(1.1)};
$args1 = [new \V8\NumberValue($isolate, 1.1)];
//  Local<v8::Array> a1 = Local<v8::Array>::Cast(Foo->Call(Foo, 1, args1));
$a1 = $Foo->call($context, $Foo, $args1);
//  CHECK_EQ(1u, a1->length());
$v8_helper->CHECK_EQ(1, $a1->length(), '1, $a1->length()');
//  CHECK_EQ(1.1, a1->get(v8::Integer::New(isolate, 0))->numberValue());
$v8_helper->CHECK_EQ(1.1, $a1->get($context, new \V8\IntegerValue($isolate, 0))->value(), '1.1, $a1->get($context, 0)->value()');
$helper->line();

//  v8::Handle<Value> args2[] = {v8_num(2.2), v8_num(3.3)};
$args2 = [new \V8\NumberValue($isolate, 2.2), new \V8\NumberValue($isolate, 3.3)];
//  Local<v8::Array> a2 = Local<v8::Array>::Cast(Foo->Call(Foo, 2, args2));
$a2 = $Foo->call($context, $Foo, $args2);
//  CHECK_EQ(2u, a2->length());
$v8_helper->CHECK_EQ(2, $a2->length(), '2, $a2->length()');
//  CHECK_EQ(2.2, a2->get(v8::Integer::New(isolate, 0))->numberValue());
$v8_helper->CHECK_EQ(2.2, $a2->get($context, new \V8\IntegerValue($isolate, 0))->value(), '2.2, $a2->get($context, 0)->value()');
//  CHECK_EQ(3.3, a2->get(v8::Integer::New(isolate, 1))->numberValue());
$v8_helper->CHECK_EQ(3.3, $a2->get($context, new \V8\IntegerValue($isolate, 1))->value(), '3.3, $a2->get($context, 1)->value()');
$helper->line();


//  v8::Handle<Value> args3[] = {v8_num(4.4), v8_num(5.5), v8_num(6.6)};
$args3 = [new \V8\NumberValue($isolate, 4.4), new \V8\NumberValue($isolate, 5.5), new \V8\NumberValue($isolate, 6.6)];
//  Local<v8::Array> a3 = Local<v8::Array>::Cast(Foo->Call(Foo, 3, args3));
$a3 = $Foo->call($context, $Foo, $args3);
//  CHECK_EQ(3u, a3->length());
$v8_helper->CHECK_EQ(3, $a3->length(), '3, $a3->length()');
//  CHECK_EQ(4.4, a3->get(v8::Integer::New(isolate, 0))->numberValue());
$v8_helper->CHECK_EQ(4.4, $a3->get($context, new \V8\IntegerValue($isolate, 0))->value(), '4.4, $a3->get($context, 0)->value()');
//  CHECK_EQ(5.5, a3->get(v8::Integer::New(isolate, 1))->numberValue());
$v8_helper->CHECK_EQ(5.5, $a3->get($context, new \V8\IntegerValue($isolate, 1))->value(), '5.5, $a3->get($context, 1)->value()');
//  CHECK_EQ(6.6, a3->get(v8::Integer::New(isolate, 2))->numberValue());
$v8_helper->CHECK_EQ(6.6, $a3->get($context, new \V8\IntegerValue($isolate, 2))->value(), '6.6, $a3->get($context, 2)->value()');
$helper->line();

//  v8::Handle<Value> args4[] = {v8_num(7.7), v8_num(8.8), v8_num(9.9), v8_num(10.11)};
$args4 = [new \V8\NumberValue($isolate, 7.7), new \V8\NumberValue($isolate, 8.8), new \V8\NumberValue($isolate, 9.9), new \V8\NumberValue($isolate, 10.11)];
//  Local<v8::Array> a4 = Local<v8::Array>::Cast(Foo->Call(Foo, 4, args4));
$a4 = $Foo->call($context, $Foo, $args4);
//  CHECK_EQ(4u, a4->length());
$v8_helper->CHECK_EQ(4, $a4->length(), '4, $a4->length()');
//  CHECK_EQ(7.7, a4->get(v8::Integer::New(isolate, 0))->numberValue());
$v8_helper->CHECK_EQ(7.7, $a4->get($context, new \V8\IntegerValue($isolate, 0))->value(), '7.7, $a4->get($context, 0)->value()');
//  CHECK_EQ(8.8, a4->get(v8::Integer::New(isolate, 1))->numberValue());
$v8_helper->CHECK_EQ(8.8, $a4->get($context, new \V8\IntegerValue($isolate, 1))->value(), '8.8, $a4->get($context, 1)->value()');
//  CHECK_EQ(9.9, a4->get(v8::Integer::New(isolate, 2))->numberValue());
$v8_helper->CHECK_EQ(9.9, $a4->get($context, new \V8\IntegerValue($isolate, 2))->value(), '9.9, $a4->get($context, 2)->value()');
//  CHECK_EQ(10.11, a4->get(v8::Integer::New(isolate, 3))->numberValue());
$v8_helper->CHECK_EQ(10.11, $a4->get($context, new \V8\IntegerValue($isolate, 3))->value(), '10.11, $a4->get($context, 3)->value()');
$helper->line();

//  Local<v8::Value> r1 = ReturnThisSloppy->Call(v8::Undefined(isolate), 0, NULL);
$r1 = $ReturnThisSloppy->call($context, new \V8\UndefinedValue($isolate), []);
//  CHECK(r1->strictEquals(context->Global()));
$v8_helper->CHECK($r1->strictEquals($context->globalObject()), '$r1->strictEquals($context->globalObject())');
//  Local<v8::Value> r2 = ReturnThisSloppy->Call(v8::Null(isolate), 0, NULL);
$r2 = $ReturnThisSloppy->call($context, new \V8\NullValue($isolate), []);
//  CHECK(r2->strictEquals(context->Global()));
$v8_helper->CHECK($r2->strictEquals($context->globalObject()), '$r2->strictEquals($context->globalObject())');
//  Local<v8::Value> r3 = ReturnThisSloppy->Call(v8_num(42), 0, NULL);
/** @var \V8\NumberObject $r3 */
$r3 = $ReturnThisSloppy->call($context, new \V8\NumberValue($isolate, 42), []);
$helper->value_instanceof($r3, '\V8\NumberObject');
//  CHECK(r3->isNumberObject());
$v8_helper->CHECK($r3->isNumberObject(), '$r3->isNumberObject()');
//  CHECK_EQ(42.0, r3.As<v8::NumberObject>()->valueOf());
$v8_helper->CHECK_EQ(42.0, $r3->valueOf(), '42.0, $r3->valueOf()');
//  Local<v8::Value> r4 = ReturnThisSloppy->Call(v8_str("hello"), 0, NULL);
$helper->line();

/** @var \V8\StringObject $r4 */
$r4 = $ReturnThisSloppy->call($context, new \V8\StringValue($isolate, 'hello'), []);
$helper->value_instanceof($r4, '\V8\StringObject');
//  CHECK(r4->isStringObject());
$v8_helper->CHECK($r4->isStringObject(), '$r4->isStringObject()');
//  CHECK(r4.As<v8::StringObject>()->valueOf()->strictEquals(v8_str("hello")));
$v8_helper->CHECK($r4->valueOf()->strictEquals(new \V8\StringValue($isolate, 'hello')), '$r4->valueOf()->strictEquals(new \V8\StringValue($isolate, \'hello\'))');
$helper->line();

//  Local<v8::Value> r5 = ReturnThisSloppy->Call(v8::True(isolate), 0, NULL);
/** @var \V8\BooleanObject $r5 */
$r5 = $ReturnThisSloppy->call($context, new \V8\BooleanValue($isolate, true), []);
$helper->value_instanceof($r5, '\V8\BooleanObject');
//  CHECK(r5->isBooleanObject());
$v8_helper->CHECK($r5->isBooleanObject(), '$r5->isBooleanObject()');
//  CHECK(r5.As<v8::BooleanObject>()->valueOf());
$v8_helper->CHECK($r5->valueOf(), '$r5->valueOf()');
$helper->line();

//  Local<v8::Value> r6 = ReturnThisStrict->Call(v8::Undefined(isolate), 0, NULL);
$r6 = $ReturnThisStrict->call($context, new \V8\UndefinedValue($isolate), []);
//  CHECK(r6->isUndefined());
$v8_helper->CHECK($r6->isUndefined(), '$r6->isUndefined()');
//  Local<v8::Value> r7 = ReturnThisStrict->Call(v8::Null(isolate), 0, NULL);
$r7 = $ReturnThisStrict->call($context, new \V8\NullValue($isolate), []);
//  CHECK(r7->isNull());
$v8_helper->CHECK($r7->isNull(), '$r7->isNull()');
//  Local<v8::Value> r8 = ReturnThisStrict->Call(v8_num(42), 0, NULL);
$r8 = $ReturnThisStrict->call($context, new \V8\NumberValue($isolate, 42), []);
//  CHECK(r8->strictEquals(v8_num(42)));
$v8_helper->CHECK($r8->strictEquals(new \V8\NumberValue($isolate, 42)), '$r8->strictEquals(new \V8\NumberValue($isolate, 42))');
//  Local<v8::Value> r9 = ReturnThisStrict->Call(v8_str("hello"), 0, NULL);
$r9 = $ReturnThisStrict->call($context, new \V8\StringValue($isolate, 'hello'), []);
//  CHECK(r9->strictEquals(v8_str("hello")));
$v8_helper->CHECK($r9->strictEquals(new \V8\StringValue($isolate, 'hello')), '$r9->strictEquals(new \V8\StringValue($isolate, \'hello\')');
//  Local<v8::Value> r10 = ReturnThisStrict->Call(v8::True(isolate), 0, NULL);
$r10 = $ReturnThisStrict->call($context, new \V8\BooleanValue($isolate, true), []);
//  CHECK(r10->strictEquals(v8::True(isolate)));
$v8_helper->CHECK($r10->strictEquals(new \V8\BooleanValue($isolate, true)), '$r10->strictEquals(new \V8\BooleanValue($isolate, true))');
//}

echo PHP_EOL;
echo 'We are done for now', PHP_EOL;

?>
--EXPECT--
CHECK_EQ (0, $a0->length()): OK

CHECK_EQ (1, $a1->length()): OK
CHECK_EQ (1.1, $a1->get($context, 0)->value()): OK

CHECK_EQ (2, $a2->length()): OK
CHECK_EQ (2.2, $a2->get($context, 0)->value()): OK
CHECK_EQ (3.3, $a2->get($context, 1)->value()): OK

CHECK_EQ (3, $a3->length()): OK
CHECK_EQ (4.4, $a3->get($context, 0)->value()): OK
CHECK_EQ (5.5, $a3->get($context, 1)->value()): OK
CHECK_EQ (6.6, $a3->get($context, 2)->value()): OK

CHECK_EQ (4, $a4->length()): OK
CHECK_EQ (7.7, $a4->get($context, 0)->value()): OK
CHECK_EQ (8.8, $a4->get($context, 1)->value()): OK
CHECK_EQ (9.9, $a4->get($context, 2)->value()): OK
CHECK_EQ (10.11, $a4->get($context, 3)->value()): OK

CHECK $r1->strictEquals($context->globalObject()): OK
CHECK $r2->strictEquals($context->globalObject()): OK
Value is instance of \V8\NumberObject
CHECK $r3->isNumberObject(): OK
CHECK_EQ (42.0, $r3->valueOf()): OK

Value is instance of \V8\StringObject
CHECK $r4->isStringObject(): OK
CHECK $r4->valueOf()->strictEquals(new \V8\StringValue($isolate, 'hello')): OK

Value is instance of \V8\BooleanObject
CHECK $r5->isBooleanObject(): OK
CHECK $r5->valueOf(): OK

CHECK $r6->isUndefined(): OK
CHECK $r7->isNull(): OK
CHECK $r8->strictEquals(new \V8\NumberValue($isolate, 42)): OK
CHECK $r9->strictEquals(new \V8\StringValue($isolate, 'hello'): OK
CHECK $r10->strictEquals(new \V8\BooleanValue($isolate, true)): OK

We are done for now
