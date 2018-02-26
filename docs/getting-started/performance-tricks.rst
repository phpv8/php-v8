******************
Performance tricks
******************

If you use ``php-v8`` extension for short-lived tasks or you have your :class:`Context` likely to be long-living enough
so that ``V8`` runtime optimizations won't have significant impact, you can still improve your performance.

Important note: all caching techniques are V8 version-specific and platform specific, some caches won't even work on
different CPU with different instructions set, you have to test following techniques for your environment and
infrastructure and be ready to fallback to raw, cache-less flow.


Let's say you have an typical Hello, world! script:

.. code:: php

    <?php declare(strict_types=1);

    use V8\{
        Isolate,
        Context,
        StringValue,
        Script,
    };

    $script_source_string = "function say() { return 'Hello' + ', World!'}; say()";

    $isolate = new Isolate();
    $context = new Context($isolate);
    $source  = new StringValue($isolate, $script_source_string);

    $script = new Script($context, $source);
    $result = $script->run($context);

    echo $result->value(), PHP_EOL;

Let's reshape it a bit to make it more suitable for further tweaks by introducing :class:`ScriptCompiler`:

.. code:: php

    <?php declare(strict_types=1);

    use V8\{
        Isolate,
        Context,
        StringValue,
        ScriptCompiler,
    };

    $script_source_string = "function say() { return 'Hello' + ', World!'}; say()";

    $isolate       = new Isolate();
    $context       = new Context($isolate);
    $source_string = new StringValue($isolate, $script_source_string);
    $source        = new ScriptCompiler\Source($source_string);
    $script        = ScriptCompiler::compile($context, $source);

    $result = $script->run($context);

    echo $result->value(), PHP_EOL;

Script code cache
=================

Using script code cache could boost your performance from 5% to 25% or even more, itlargely depends on your script and
host. On slower machines it may give you better result in terms of performance gain %%, while on faster it may be not so
large, according to performance benchmark.

.. code:: php

    <?php declare(strict_types=1);

    use V8\{
        Isolate,
        Context,
        StringValue,
        ScriptCompiler,
    };

    $script_source_string = "function say() { return 'Hello' + ', World!'}; say()";

    $isolate       = new Isolate();
    $context       = new Context($isolate);
    $source_string = new StringValue($isolate, $script_source_string);
    $source        = new ScriptCompiler\Source($source_string);

    // Generating script cache. Normally you want to cache this data somewhere else
    // either on filesystem, in database or in memory. Redis could be your friend
    // but don't let it be your memory hog.
    $unbound_script = ScriptCompiler::compileUnboundScript($context, $source);
    $cached_data = ScriptCompiler::createCodeCache($unbound_script, $source_string);

    // Here we utilize script cache
    $source = new ScriptCompiler\Source($source_string, null, $cached_data);
    $script = ScriptCompiler::compile($context, $source, ScriptCompiler::OPTION_CONSUME_CODE_CACHE);

    if ($cached_data->isRejected()) {
        throw new RuntimeException('Script code cache rejected!');
    }

    $result = $script->run($context);

    echo $result->value(), PHP_EOL;


Isolate startup data
====================

Startup data can speedup your context creation by populating them with script run result. It can save from 1% to 3%, so
it's not so effective as script code cache, however, the benchmark was done on so complex example so if you have a lot
of entities that you need to bootstrap your context with, your saving may be more.

.. code:: php

    <?php declare(strict_types=1);

    use V8\{
        Isolate,
        Context,
        StringValue,
        ScriptCompiler,
        StartupData,
    };

    $script_source_string = "function say() { return 'Hello' + ', World!'}; say()";

    // Same here, you are likely want to store it in some quick and cheap to access storage
    $startup_data = StartupData::createFromSource($script_source_string);

    $isolate       = new Isolate($startup_data);
    $context       = new Context($isolate);
    $source_string = new StringValue($isolate, $script_source_string);
    $source        = new ScriptCompiler\Source($source_string);

    $script = ScriptCompiler::compile($context, $source);

    $result = $script->run($context);

    echo $result->value(), PHP_EOL;


Combining both approaches
=========================

Combining both techniques is you friend in boosting performance:

.. code:: php

    <?php declare(strict_types=1);

    use V8\{
        Isolate,
        Context,
        StringValue,
        ScriptCompiler,
        StartupData,
    };

    $script_source_string = "function say() { return 'Hello' + ', World!'}; say()";

    $startup_data = StartupData::createFromSource($script_source_string);

    $isolate       = new Isolate($startup_data);
    $context       = new Context($isolate);
    $source_string = new StringValue($isolate, $script_source_string);
    $source        = new ScriptCompiler\Source($source_string);

    $unbound_script = ScriptCompiler::compileUnboundScript($context, $source);
    $cached_data = ScriptCompiler::createCodeCache($unbound_script, $source_string);

    $source = new ScriptCompiler\Source($source_string, null, $cached_data);
    $script = ScriptCompiler::compile($context, $source, ScriptCompiler::OPTION_CONSUME_CODE_CACHE);

    if ($cached_data->isRejected()) {
        throw new RuntimeException('Script code cache rejected!');
    }

    $script = ScriptCompiler::compile($context, $source);

    $result = $script->run($context);

    echo $result->value(), PHP_EOL;

Benchmarks
==========


Note, that your mileage may varies so you are highly encouraged to run benchmarks located under project's root ``/pref``
folder by yourself on your hardware, in your infra and even with your js script.

From Ubuntu in Docker on macOS
------------------------------
4 cores, 16Gb memory

.. code:: bash

    # php -v
    PHP 7.2.2-3+ubuntu16.04.1+deb.sury.org+1 (cli) (built: Feb  6 2018 16:11:23) ( NTS )
    Copyright (c) 1997-2018 The PHP Group
    Zend Engine v3.2.0, Copyright (c) 1998-2018 Zend Technologies
        with Zend OPcache v7.2.2-3+ubuntu16.04.1+deb.sury.org+1, Copyright (c) 1999-2018, by Zend Technologies

    # php --ri v8
    V8 support => enabled
    Version => v0.2.1-master-dev
    Revision => 5d7c3e4
    Compiled => Feb 25 2018 @ 11:29:00

    V8 Engine Compiled Version => 6.6.313
    V8 Engine Linked Version => 6.6.313


*Less is better*

+---------------------------------+-------------+----------+--------+---------------------------+
| subject                         | mode        | stdev    | rstdev | diff (*less is better*)   |
+=================================+=============+==========+========+===========================+
| Cold Isolate, no code cache     | 3,602.599us | 49.778us | 1.38%  |                   +26.98% |
+---------------------------------+-------------+----------+--------+---------------------------+
| Cold Isolate, with code cache   | 2,885.638us | 39.775us | 1.36%  |                   +2.86%  |
+---------------------------------+-------------+----------+--------+---------------------------+
| Warm Isolate, no code cache     | 3,489.959us | 44.036us | 1.27%  |                   +22.46% |
+---------------------------------+-------------+----------+--------+---------------------------+
| Warm Isolate, with code cache   | 2,813.156us | 43.351us | 1.53%  |                   0.00%   |
+---------------------------------+-------------+----------+--------+---------------------------+

From macOS host
---------------
4 cores, 16Gb memory

.. code:: bash

    $ php -v
    PHP 7.2.2 (cli) (built: Feb  1 2018 11:50:40) ( NTS )
    Copyright (c) 1997-2018 The PHP Group
    Zend Engine v3.2.0, Copyright (c) 1998-2018 Zend Technologies
        with Zend OPcache v7.2.2, Copyright (c) 1999-2018, by Zend Technologies

    $ php --ri v8
    V8 support => enabled
    Version => v0.2.1-master-dev
    Revision => 5d7c3e4
    Compiled => Feb 25 2018 @ 11:42:00

    V8 Engine Compiled Version => 6.6.313
    V8 Engine Linked Version => 6.6.313




+---------------------------------+-------------+-----------+--------+---------------------------+
| subject                         | mode        | stdev     | rstdev | diff (*less is better*)   |
+=================================+=============+===========+========+===========================+
| Cold Isolate, no code cache     | 8,732.585us | 97.889us  | 1.11%  |                    +6.90% |
+---------------------------------+-------------+-----------+--------+---------------------------+
| Cold Isolate, with code cache   | 8,290.880us | 141.583us | 1.69%  |                    +1.78% |
+---------------------------------+-------------+-----------+--------+---------------------------+
| Warm Isolate, no code cache     | 8,722.684us | 104.547us | 1.19%  |                    +6.68% |
+---------------------------------+-------------+-----------+--------+---------------------------+
| Warm Isolate, with code cache   | 8,194.924us | 70.345us  | 0.85%  |                    0.00%  |
+---------------------------------+-------------+-----------+--------+---------------------------+
