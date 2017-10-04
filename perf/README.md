To run specific perf test execute run something like:

`./vendor/bin/phpbench run <perf test name> --report=aggregate --retry-threshold=5`

For more details see [phpbench documentation](http://phpbench.readthedocs.io/en/latest/introduction.html).

e.g.

 - `./vendor/bin/phpbench run src/SetObjectProperty.php --report=aggregate --retry-threshold=5`

 - `./vendor/bin/phpbench run src/CreatePrimitiveValue.php --report=aggregate --retry-threshold=5`
