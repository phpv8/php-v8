# php-v8
PHP extension for V8 JavaScript engine

[![Build Status](https://travis-ci.org/pinepain/php-v8.svg)](https://travis-ci.org/pinepain/php-v8)
[![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg)](https://raw.githubusercontent.com/pinepain/php-v8/master/LICENSE)

This extension is for PHP 7 only.

**This extension is still under heavy development and it public API may change without any warning. Use at your own risk.**


## Installation

### Requirements

You will need some fresh v8 Google JavaScript enging version installed. At this time extension tested on 5.2.371.

 - For Ubuntu there are [pinepain/libv8-5.2](https://launchpad.net/~pinepain/+archive/ubuntu/libv8-5.2) PPA.
   To install fresh libv8 do:

   ```
   $ sudo add-apt-repository ppa:pinepain/libv8-5.2 -y
   $ sudo apt-get update -q
   $ sudo apt-get install -y libv8-5.2-dev
   ```
 - For OS X there are [v8.rb](https://github.com/pinepain/php-v8/blob/master/scripts/homebrew/v8.rb) homebrew formula.
  To install fresh libv8 do:

  ```
  $ brew install https://raw.githubusercontent.com/pinepain/php-v8/master/scripts/homebrew/v8.rb
  ```

### Building from sources

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
 - `export DEV_TESTS=1` allows to run tests that made for development reason (e.g. test some weird behavior or for debugging)
 - To prevent asking test suite to send results to PHP QA team do `export NO_INTERACTION=1`

 - To track memory usage you may want to use `smem`, `pmem` and even `lsof` to see what shared object are loaded
   and `free` to display free and used memory in the system.


## Edge cases:

### Templates recursion:

When you set property on any `Template` (`ObjectTemplate` or `FunctionTemplate`) it shouldn't lead to recursion during
template instantiation while it leads to segfault and for now there are no reasonable way to avoid this on extension
level (probably, some wrapper around `ObjectTemplate` and `FunctionTemplate` will solve this.

Known issues demo:

```php
$isolate = new v8\Isolate();

$template = new ObjectTemplate($isolate);

$template->Set('self', $template); // leads to segfault
```

## License

Copyright (c) 2015-2016 Bogdan Padalko &lt;pinepain@gmail.com&gt;

[php-v8](https://github.com/pinepain/php-v8) PHP extension is licensed under the [MIT license](http://opensource.org/licenses/MIT).
