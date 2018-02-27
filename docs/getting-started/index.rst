***************
Getting started
***************

About
=====

`php-v8`_ is a PHP 7.x extension that brings `V8`_ JavaScript engine API to PHP with some abstraction in mind and
provides an accurate native V8 C++ API implementation available from PHP.

Key features:
-------------

 - provides up-to-date JavaScript engine with recent `ECMA`_ features supported;
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
become js scalars too). While specific functionality will be done in PHP user space rather than in this C/C++ extension,
it lets you get into V8 hacking faster, reduces time costs and gives you a more maintainable solution. And it doesn't
make any assumptions for you, so you stay in control, it does exactly what you ask it to do.

With php-v8 you can even implement NodeJs in PHP. Not sure whether anyone should/will do this anyway, but it's doable.

Demo
====

Here is a `Hello World`_ from V8 `Getting Started` developers guide page implemented in raw php-v8:

.. code:: php

    <?php declare(strict_types=1);

    use V8\{
        Isolate,
        Context,
        StringValue,
        Script,
    };

    $isolate = new Isolate();
    $context = new Context($isolate);
    $source  = new StringValue($isolate, "'Hello' + ', World!'");

    $script = new Script($context, $source);
    $result = $script->run($context);

    echo $result->value(), PHP_EOL;

Installation
============


Requirements
------------

V8
""

You will need a recent v8 Google JavaScript engine version installed. At this time v8 >= 6.6.313 required.

PHP
"""

This extension is PHP7-only. It works and tested with both PHP 7.0 and PHP 7.1.

OS
"""

This extension works and tested on x64 Linux and macOS. As of written it is Ubuntu 16.04 LTS Xenial Xerus, amd64
and macOS 10.12.5. Windows is not supported at this time.

Quick guide
-----------

Docker
""""""

There is default ``pinepain/php-v8`` docker image with basic dependencies to evaluate and play with php-v8:

.. code-block:: bash

    docker run -it pinepain/php-v8 bash -c "php test.php"


Ubuntu
""""""

There is

.. code-block:: bash

    $ sudo add-apt-repository -y ppa:ondrej/php
    $ sudo add-apt-repository -y ppa:pinepain/php
    $ sudo apt-get update -y
    $ sudo apt-get install -y php7.2 php-v8
    $ php --ri v8


While `pinepain/php <https://launchpad.net/~pinepain/+archive/ubuntu/php>`_ PPA targets to contain all necessary
extensions with dependencies, you may find following standalone PPAs useful:

- `pinepain/libv8-6.6 <https://launchpad.net/~pinepain/+archive/ubuntu/libv8-6.6>`_
- `pinepain/libv8-experimental <https://launchpad.net/~pinepain/+archive/ubuntu/libv8-experimental>`_
- `pinepain/php-v8 <https://launchpad.net/~pinepain/+archive/ubuntu/php-v8>`_



OS X (homebrew)
"""""""""""""""

.. code-block:: bash

    $ brew tap homebrew/dupes
    $ brew tap homebrew/php
    $ brew tap pinepain/devtools
    $ brew install php71 php71-v8
    $ php --ri v8

For macOS php-v8 formulae and dependencies provided by `pinepain/devtools <https://github.com/pinepain/homebrew-devtools>`_ tap.

Building php-v8 from sources
----------------------------

.. code-block:: bash

    git clone https://github.com/pinepain/php-v8.git
    cd php-v8
    phpize && ./configure && make
    make test

To install extension globally run

.. code-block:: bash

    $ sudo make install

.. _V8: https://developers.google.com/v8/intro
.. _php-v8: https://github.com/pinepain/php-v8
.. _Hello World: https://chromium.googlesource.com/v8/v8/+/master/samples/hello-world.cc
.. _Getting Started: https://developers.google.com/v8/intro
.. _php-v8-stubs: https://github.com/pinepain/php-v8-stubs
.. _ECMA: http://kangax.github.io/compat-table
