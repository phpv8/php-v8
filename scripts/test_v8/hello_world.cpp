#include <v8.h>
#include <libplatform/libplatform.h>

#include <stdlib.h>
#include <string.h>

using namespace v8;

void weak_callback(const v8::WeakCallbackInfo<v8::Persistent<v8::String>>& data) {
  printf("Weak callback called\n");
  data.GetParameter()->Reset();
//  data.GetIsolate()->AdjustAmountOfExternalAllocatedMemory(-(1024*1024*1024));
}

int main(int argc, char* argv[]) {
  // Initialize V8.
  v8::V8::InitializeICU();

  std::unique_ptr<v8::Platform> platform = v8::platform::NewDefaultPlatform();
  v8::V8::InitializePlatform(platform.get());

  V8::Initialize();

  v8::Isolate::CreateParams create_params;
  create_params.array_buffer_allocator = v8::ArrayBuffer::Allocator::NewDefaultAllocator();

  // Create a new Isolate and make it the current one.
  Isolate* isolate = v8::Isolate::New(create_params);

  v8::Persistent<v8::String> test;

  {
    Isolate::Scope isolate_scope(isolate);

    // Create a stack-allocated handle scope.
    HandleScope handle_scope(isolate);

    // Create a new context.
    Local<Context> context = Context::New(isolate);

    // Enter the context for compiling and running the hello world script.
    Context::Scope context_scope(context);


    test.Reset(isolate, String::NewFromUtf8(isolate, "Hello' + ', World!'"));
    test.SetWeak(&test, weak_callback, v8::WeakCallbackType::kParameter);
//    isolate->AdjustAmountOfExternalAllocatedMemory((1024*1024*1024));


    // Create a string containing the JavaScript source code.
//    Local<String> source = String::NewFromUtf8(isolate, "(2+2*2) + ' ' + hw");
    Local<String> source = String::NewFromUtf8(isolate, "(2+2*2) + ' ' + 'Hello' + ', World!'");

//    v8::Local<v8::String> hw = v8::Local<v8::String>::New(isolate, test);
//    context->Global()->Set(String::NewFromUtf8(isolate, "hw"), hw);

    // Compile the source code.
    Local<Script> script = Script::Compile(source);

    // Run the script to get the result.
    Local<Value> result = script->Run();

    // Convert the result to an UTF8 string and print it.
    String::Utf8Value utf8(isolate, result);
    printf("%s\n", *utf8);
  }

  isolate->LowMemoryNotification();


  // Dispose the isolate and tear down V8.
  isolate->Dispose();
  V8::Dispose();
  V8::ShutdownPlatform();

  return 0;
}
