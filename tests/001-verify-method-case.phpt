--TEST--
Check whether all methods follows naming convention
--SKIPIF--
<?php if (!extension_loaded("v8")) print "skip"; ?>
--ENV--
HOME=/tmp/we-need-home-env-var-set-to-load-valgrindrc
--FILE--
<?php
$re = new ReflectionExtension('v8');

$classes = $re->getClasses();


class MethodsCaseVerifier
{
    private $invalid = [];

    public function verifyClass(ReflectionClass $class)
    {
        foreach ($class->getMethods() as $m) {
            $this->verifyMethod($m);
        }
    }

    public function verifyMethod(ReflectionMethod $method)
    {
        if (strtolower($method->getName()[0]) == $method->getName()[0]) {
            return;
        }

        $method_name = $method->getDeclaringClass()->getName() . '::' . $method->getName();

        if (isset($this->invalid[$method_name])) {
            return;
        }

        $this->invalid[$method_name] = true;

        echo "{$method_name}() - invalid method name", PHP_EOL;
    }

    public function isValid()
    {
        return empty($this->invalid);
    }
}

$v = new MethodsCaseVerifier();


foreach ($classes as $c) {
    $v->verifyClass($c);
}

if ($v->isValid()) {
    echo 'All methods are valid', PHP_EOL;
}

?>
--EXPECT--
All methods are valid
