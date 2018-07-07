Welcome to php-v8's documentation!
==================================

.. include:: getting-started/donate.rst

About
=====

`php-v8`_ is a PHP 7.x extension that brings `V8`_ JavaScript engine API to PHP with some abstraction in mind and
provides an accurate native V8 C++ API implementation available from PHP.

Hello, world!
=============

.. code:: php

   <?php

   use V8\{Isolate, Context, StringValue, Script};

   $script = "'Hello' + ', World!'";

   $isolate = new Isolate();
   $context = new Context($isolate);

   echo (new Script($context, new StringValue($isolate, $script)))
       ->run($context)
       ->value(), PHP_EOL;

Content
=======

.. toctree::
   :maxdepth: 2

   getting-started/index
   getting-started/performance-tricks
   development/index

.. _V8: https://developers.google.com/v8/intro
.. _php-v8: https://github.com/phpv8/php-v8
