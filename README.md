# php-v8
PHP extension for V8 JavaScript engine

[![Build Status](https://api.travis-ci.org/pinepain/php-v8.svg?branch=master)](https://travis-ci.org/pinepain/php-v8)
[![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg)](https://raw.githubusercontent.com/pinepain/php-v8/master/LICENSE)

**This extension requires PHP >= 7.1**. Last version that supports PHP 7.0 is v0.1.9.

**This extension is still under heavy development and its public API may change without any warning. Use at your own risk.**

### PLEASE READ:

Maintaining this project takes significant amount of time and efforts.
If you like my work and want to show your appreciation, please consider supporting me at https://www.patreon.com/pinepain.

Work in progress documentation could be found at https://pinepain.gitbooks.io/php-v8/content/. You can also use tests and 
stubs as reference.

## Why (aka Rationale)

This tool solves following domain problems:

 - execute arbitrary untrusted code from user;
 - provide restricted/experimental api to end-user;
 - allow to use that with scripting/DSL language;
 - limit execution time and used memory;
 - use common language that is familiar to large audience;
 - be well-maintainable and mature.

By accident (not by design) this tool could also be used to:

 - render React/Vue/Angular components in PHP;
 - implement node.js in PHP;
 - increase the number of "why", "why not just <...>" questions.

If you have any other use, feels free to share


## About
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

## Demo

Here is a [Hello World][v8-hello-world]
from V8 [Getting Started][v8-intro] developers guide page implemented in PHP with php-v8:

```php
<?php
$isolate = new \V8\Isolate();
$context = new \V8\Context($isolate);
$source = new \V8\StringValue($isolate, "'Hello' + ', World!'");

$script = new \V8\Script($context, $source);
$result = $script->run($context);

echo $result->value(), PHP_EOL;
```

which will output `Hello, World!`. See how it's shorter and more readable than [that C++ version][v8-hello-world]?
And it also doesn't limit you from V8 API utilizing to implement more amazing stuff.

## Quick start

You can try php-v8 in `pinepain/php-v8`: `docker run -it pinepain/php-v8 bash -c "php test.php"`

## Stub files

If you are also using Composer, it is recommended to add the [php-v8-stub][php-v8-stubs]
package as a dev-mode requirement. It provides skeleton definitions and annotations to enable support for auto-completion
in your IDE and other code-analysis tools.

    composer require --dev pinepain/php-v8-stubs

## High-level wrapper library

There is [pinepain/js-sandbox](https://github.com/pinepain/js-sandbox) library that provides high-level abstraction
on top of php-v8 extension and makes embedding JavaScript in PHP easier.

## Installation

### Requirements

#### V8
You will need a recent v8 Google JavaScript engine version installed. At this time v8 >= 6.6.313 required.

#### PHP
This extension is PHP7-only. It works and tested with both PHP 7.0 and PHP 7.1.

#### OS
This extension works and tested on x64 Linux and macOS. As of written it is Ubuntu 16.04 LTS Xenial Xerus, amd64
and macOS 10.12.5. Windows is not supported at this time.

### Quick guide

#### Ubuntu

```
$ sudo add-apt-repository -y ppa:ondrej/php
$ sudo add-apt-repository -y ppa:pinepain/php
$ sudo apt-get update -y
$ sudo apt-get install -y php7.1 php-v8
$ php --ri v8
```

While [pinepain/php](https://launchpad.net/~pinepain/+archive/ubuntu/php) PPA targets to contain all necessary
extensions with dependencies, you may find
[pinepain/libv8-6.5](https://launchpad.net/~pinepain/+archive/ubuntu/libv8-6.5),
[pinepain/libv8-experimental](https://launchpad.net/~pinepain/+archive/ubuntu/libv8-experimental) and
[pinepain/php-v8](https://launchpad.net/~pinepain/+archive/ubuntu/php-v8) standalone PPAs useful.


#### OS X (homebrew)

```
$ brew tap homebrew/dupes
$ brew tap homebrew/php
$ brew tap pinepain/devtools
$ brew install php71 php71-v8
$ php --ri v8
```

For macOS php-v8 formulae and dependencies provided by [pinepain/devtools](https://github.com/pinepain/homebrew-devtools) tap.

### Building php-v8 from sources

```
git clone https://github.com/pinepain/php-v8.git
cd php-v8
phpize && ./configure && make
make test
```

To install extension globally run

```
$ sudo make install
```

## Developers note

 - to be able to customize some tests make sure you have `variables_order = "EGPCS"` in your php.ini
 - `export DEV_TESTS=1` allows to run tests that are made for development reasons (e.g. test some weird behavior or for debugging)
 - To prevent the test suite from asking you to send results to the PHP QA team do `export NO_INTERACTION=1`
 - To run tests with memory leaaks check, install `valgrind` and do `export TEST_PHP_ARGS="-m"`

 - To track memory usage you may want to use `smem`, `pmem` or even `lsof` to see what shared object are loaded
   and `free` to display free and used memory in the system.
 - [pinepain/libv8-experimental](https://launchpad.net/~pinepain/+archive/ubuntu/libv8-experimental) normally contains
   `libv8` version that used in current `master` branch.

### Docker

First, let's build docker image `docker build -t pinepain/php-v8 .` that we'll use later for development. By default, 
it contains PHP 7.2, though you can change that by passing `--build-arg PHP=MAJOR.MINOR` where MAJOR.MINOR version
present in [ondrej/php](https://launchpad.net/~ondrej/+archive/ubuntu/php) PPA.

To start playing with php-v8 in docker, run ```docker run -e TEST_PHP_ARGS -v `pwd`:/root/php-v8 -it pinepain/php-v8 bash``.
Now you can build php-v8 as usual with `phpize && ./configure && make`. Don't forget to run `make test`!

### Docs

To start writing docs you will need to get [GitBook](https://gitbook.com), just run `npm install gitbook-cli -g` and
then `gitbook serve` in project rood directory to live preview docs or `gitbook build` to build static website. See
[Setup and Installation of GitBook](https://toolchain.gitbook.com/setup.html) official GitBook manual for more;

You may find [GitBook Editor](https://www.gitbook.com/editor) useful for docs writing and editing.

## Credits

My thanks to the following people and projects, without whom this extension wouldn't be what it is today.
(Please let me know if I've mistakenly omitted anyone.)

 - [v8js](https://github.com/phpv8/v8js) PHP extension which used as a reference on early stages;
 - [Stefan Siegl](https://github.com/stesie), for his profound work on [v8js](https://github.com/phpv8/v8js)
   PHP extension and for his personal time at helping building V8;
 - all [v8js](https://github.com/phpv8/v8js) [contributors](https://github.com/phpv8/v8js/graphs/contributors);
 - Jérémy Lal, one of [libv8](https://anonscm.debian.org/git/collab-maint/libv8.git) maintainers for his personal
   help on building V8 on Ubuntu;
 - [John Gardner](https://github.com/Alhadis) for dealing with V8 building system changes;
 - [@ilovezfs](https://github.com/ilovezfs) for his help and mentoring on upgrading V8 homebrew formulae.

## License

Copyright (c) 2015-2018 Bogdan Padalko &lt;pinepain@gmail.com&gt;

[php-v8](https://github.com/pinepain/php-v8) PHP extension is licensed under the [MIT license](http://opensource.org/licenses/MIT).


[v8-hello-world]: https://chromium.googlesource.com/v8/v8/+/master/samples/hello-world.cc
[v8-intro]: https://developers.google.com/v8/intro
[php-v8-stubs]: https://github.com/pinepain/php-v8-stubs
