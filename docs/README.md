# PLEASE READ:

Maintaining this project takes significant amount of time and efforts.
If you like my work and want to show your appreciation, please consider supporting me at https://www.patreon.com/pinepain.


# About

[php-v8](https://github.com/pinepain/php-v8) is a PHP 7.x extension
that brings [V8](https://developers.google.com/v8/intro) JavaScript engine API to PHP with some abstraction in mind and
provides an accurate native V8 C++ API implementation available from PHP.

**Key features:**
 - provides up-to-date JavaScript engine with recent [ECMA](http://kangax.github.io/compat-table) features supported;
 - accurate native V8 C++ API implementation available from PHP;
 - solid experience between native V8 C++ API and V8 API in PHP;
 - no magic; no assumptions;
 - does what it is asked to do;
 - hides complexity with isolates and contexts scope management under the hood;
 - provides a both-way interaction with PHP and V8 objects, arrays and functions;
 - execution time and memory limits;
 - multiple isolates and contexts at the same time;
 - it works.

With this extension almost everything that the native V8 C++ API provides can be used. It provides a way to pass PHP scalars,
objects and functions to the V8 runtime and specify interactions with passed values (objects and functions only, as scalars
become js scalars too). While specific functionality will be done in PHP userland rather than in this C/C++ extension,
it lets you get into V8 hacking faster, reduces time costs and gives you a more maintainable solution. And it doesn't
make any assumptions for you, so you stay in control, it does exactly what you ask it to do.

With php-v8 you can even implement nodejs in PHP. Not sure whether anyone should/will do this anyway, but it's doable.

# Demo

Here is a [Hello World][v8-hello-world] from V8 [Getting Started][v8-intro] developers guide page implemented in raw php-v8:

```php
<?php declare(strict_types=1);

use V8\Isolate;
use V8\Context;
use V8\StringValue;
use V8\Script;

$isolate = new Isolate();
$context = new Context($isolate);
$source = new StringValue($isolate, "'Hello' + ', World!'");

$script = new Script($context, $source);
$result = $script->run($context);

echo $result->value(), PHP_EOL;
```




