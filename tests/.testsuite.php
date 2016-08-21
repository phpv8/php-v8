<?php

/*
  +----------------------------------------------------------------------+
  | This file is part of the pinepain/php-v8 PHP extension.              |
  |                                                                      |
  | Copyright (c) 2015-2016 Bogdan Padalko <pinepain@gmail.com>          |
  |                                                                      |
  | Licensed under the MIT license: http://opensource.org/licenses/MIT   |
  |                                                                      |
  | For the full copyright and license information, please view the      |
  | LICENSE file that was distributed with this source or visit          |
  | http://opensource.org/licenses/MIT                                   |
  +----------------------------------------------------------------------+
*/


function exception_error_handler($errno, $errstr, $errfile, $errline)
{
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}

set_error_handler('exception_error_handler');


class PhpV8Testsuite
{
    private $dumper = 'var_dump';

    public function header($title)
    {
        echo $title, ':', PHP_EOL;

        echo str_repeat('-', strlen($title) + 1), PHP_EOL;
    }

    public function inline($message, $value)
    {
        echo $message, ': ', $value, PHP_EOL;
    }

    public function space($new_lines_number = 2)
    {
        echo str_repeat(PHP_EOL, max(1, $new_lines_number));
    }

    public function line()
    {
        $this->space(1);
    }


    public function exception_export(Throwable $e)
    {
        echo get_class($e), ': ', $e->getMessage(), PHP_EOL;
    }

    public function value_export($value)
    {
        echo gettype($value), ': ', var_export($value, true), PHP_EOL;
    }

    public function dump($value, $level=0)
    {
        echo $this->to_dump($value, $level, false), PHP_EOL;
    }

    public function pretty_dump($title, $value, $level=0)
    {
        echo $title, ': ', $this->to_dump($value, $level), PHP_EOL;
    }

    public function object_type($object)
    {
        echo get_class($object), PHP_EOL;
    }

    public function function_export($func, array $args = [])
    {
        echo $func, '(): ', var_export(call_user_func_array($func, $args), true), PHP_EOL;
    }

    public function method_export($object, $method, array $args = [])
    {
        $filter = new ArrayMapFilter([$method => true]);
        $this->dump_object_methods($object, [$method => $args], $filter);
    }

    public function to_dump($value, $level = 1, $initial_nl = true)
    {
        ob_start();
        var_dump($value);
        $res = ob_get_clean();

        $maybe_strings = explode(PHP_EOL, $res);

        if (count($maybe_strings) > 2) {
            array_pop($maybe_strings);

            $offset = str_repeat(' ', 4 * $level);
            $res = ($initial_nl ? PHP_EOL: '') .$offset . implode(PHP_EOL .$offset, $maybe_strings);

        } elseif (count($maybe_strings) > 1) {
            array_pop($maybe_strings);
            $res = $maybe_strings[0];
        }

        return $res;
    }

    public function assert($message, $actual, $expected = true, $identical = true)
    {
        echo $message, ': ';

        if ($identical) {
            echo($expected === $actual ? 'ok' : 'failed'), PHP_EOL;
        } else {
            echo($expected == $actual ? 'ok' : 'failed'), PHP_EOL;
        }
    }

    public function value_matches($expected, $actual, $identical = true)
    {
        if ($identical) {
            echo 'Expected ', var_export($expected,
                true), ' value is ', ($expected === $actual ? 'identical to' : 'not identical to'), ' actual value ', var_export($actual,
                true), PHP_EOL;
        } else {
            echo 'Expected ', var_export($expected,
                true), ' value is ', ($expected == $actual ? 'matches' : 'doesn\'t match'), ' actual value ', var_export($actual,
                true), PHP_EOL;
        }
    }

    public function value_instanceof($value, $expected)
    {
        echo 'Value', ($value instanceof $expected ? ' is' : ' not an'), ' instance of ', $expected, PHP_EOL;
    }

    public function value_matches_with_no_output($expected, $actual, $identical = true)
    {
        if ($identical) {
            echo 'Expected value is ', ($expected === $actual ? 'identical to' : 'not identical to'), ' actual value', PHP_EOL;
        } else {
            echo 'Expected value ', ($expected == $actual ? 'matches' : 'doesn\'t match'), ' actual value', PHP_EOL;
        }
    }

    public function method_matches($object, $method, $expected, array $args = [])
    {
        echo get_class($object), '::', $method, '()', ' ', ($expected === call_user_func_array([$object, $method],
            $args) ? 'matches' : 'doesn\'t match'), ' expected value', PHP_EOL;
    }

    public function method_throws($object, $method, $exception, $message = null, array $args = [])
    {
        try {
            call_user_func_array([$object, $method], $args);
        } catch (\Throwable $e) {

            if ($e instanceof $exception) {

                if ($message !== null) {
                    if ($message == $e->getMessage()) {
                        echo get_class($object), '::', $method, '()', ' throw expected exception and messages match', PHP_EOL;
                    } else {
                        echo get_class($object), '::', $method, '()', ' throw expected exception, but messages doesn\'t match', PHP_EOL;
                    }
                } else {
                    echo get_class($object), '::', $method, '()', ' throw expected exception', PHP_EOL;
                }
            } else {
                echo get_class($object), '::', $method, '()', ' throw unexpected exception', PHP_EOL;
            }

            return;
        }

        echo get_class($object), '::', $method, '()', ' ', 'doesn\'t throw any exception', PHP_EOL;

    }


    public function method_matches_instanceof($object, $method, $expected)
    {
        echo get_class($object), '::', $method, '() result', ($object->$method() instanceof $expected ? ' is' : ' not an'), ' instance of ', $expected, PHP_EOL;
    }


    public function method_matches_with_output($object, $method, $expected)
    {
        echo get_class($object), '::', $method, '()', ' ', ($expected === $object->$method() ? 'matches' : 'doesn\'t match'), ' expected ', var_export($expected,
            true), PHP_EOL;
    }

    public function dump_object_constants($object)
    {
        $rc = new ReflectionClass($object);

        $class_name = $rc->getName();
        foreach ($rc->getConstants() as $name => $value) {
            echo $class_name, '::', $name, ' = ', var_export($value, true), PHP_EOL;
        }
    }

    /**
     * @param                    $object
     * @param array              $arguments
     * @param FilterInterface    $filter
     * @param FinalizerInterface $finalizer
     */
    public function dump_object_methods($object, array $arguments = [], FilterInterface $filter = null, FinalizerInterface $finalizer = null)
    {
        $rc = new ReflectionClass($object);

        if (!$filter) {
            $filter = new DefaultFilter();
        }

        if (!$finalizer) {
            $finalizer = new DefaultFinalizer();
        }

        $finalizer->setStringify(function ($val) {return $this->to_dump($val, 1);});
        $methods = $rc->getMethods($filter->getFilter());

        foreach ($methods as $m) {
            if ($m->isConstructor() || $m->isDestructor() || $m->isStatic()) {
                // skip constructor and destructors, also skip static methods
                continue;
            }

            if ($filter->shouldSkip($m)) {
                continue;
            }

            if (isset($arguments[$m->getName()])) {
                $args = $arguments[$m->getName()];
            } elseif(isset($arguments['@@default'])) {
                $args = $arguments['@@default'];
            } else {
                $args = [];
            }

            if ($m->getNumberOfRequiredParameters() > 0 && empty($args)) {
                throw new RuntimeException("Cannot call {$m->getName()} method without arguments");
            }

            $thrown = null;
            $res = null;

            try {
                $res = $m->invokeArgs($object, $args);
            } catch (Exception $e) {
                $thrown = $e;
            }

            $access = $m->isStatic() ? '::' : '->';

            if ($rc->getName() != $m->getDeclaringClass()->getName()) {
                $info = '(' . $m->getDeclaringClass()->getName() . ')';
            } else {
                $info = '';
            }

            $final_res = $finalizer->finalize($res, $thrown);

            if ($final_res[0] != "\n" && $final_res[0] != "\r") {
                $final_res = ' ' . $final_res;
            }

            echo $rc->getName(), $info, $access, $m->getName(), '():', $final_res, PHP_EOL;
        }
    }

    public function need_more_time() {
        // NOTE: this check is a bit fragile but should fits our need
        return isset($_ENV['TRAVIS']) && isset($_ENV['TEST_PHP_ARGS']) && $_ENV['TEST_PHP_ARGS'] == '-m';
    }
}

interface FilterInterface
{
    public function getFilter() : int;

    public function shouldSkip(ReflectionMethod $rm) : bool;
}

class DefaultFilter implements FilterInterface
{
    /**
     * @var int
     */
    private $filter;

    public function __construct(int $filter = ReflectionMethod::IS_PUBLIC)
    {
        $this->filter = $filter;
    }

    public function getFilter() : int
    {
        return $this->filter;
    }

    public function shouldSkip(ReflectionMethod $rm) : bool
    {
        return false;
    }
}

class RegexpFilter extends DefaultFilter
{
    /**
     * @var string
     */
    private $regexp;
    /**
     * @var bool
     */
    private $is_blacklist;

    public function __construct(
        string $regexp,
        bool $is_blacklist = true,
        int $filter = ReflectionMethod::IS_PUBLIC
    ) {
        parent::__construct($filter);
        $this->regexp = $regexp;
        $this->is_blacklist = $is_blacklist;
    }

    public function shouldSkip(ReflectionMethod $rm) : bool
    {
        if (empty($this->regexp)) {
            return !$this->is_blacklist;
        }

        $name = $rm->getName();

        $matched = !!preg_match($this->regexp, $name);

        if ($matched === !$this->is_blacklist) {
            // if method matches blacklisted regexp - skip
            // OR if method doesn't match whitelist regexp - skip
            return true;
        }

        return false;
    }
}

class ArrayListFilter extends DefaultFilter {
    /**
     * @var array
     */
    private $list;
    /**
     * @var bool
     */
    private $is_blacklist;

    public function __construct(
        array $list,
        bool $is_blacklist = true,
        int $filter = ReflectionMethod::IS_PUBLIC
    ) {
        parent::__construct($filter);
        $this->list = $list;
        $this->is_blacklist = $is_blacklist;
    }

    public function shouldSkip(ReflectionMethod $rm) : bool
    {
        if (empty($this->list)) {
            return !$this->is_blacklist;
        }

        $name = $rm->getName();

        if (in_array($name, $this->list)) {
            return $this->is_blacklist;
        }

        return !$this->is_blacklist;
    }
}

class ArrayMapFilter extends DefaultFilter {
    /**
     * @var array
     */
    private $map;
    /**
     * @var bool
     */
    private $default;

    public function __construct(
        array $map,
        bool $default = false,
        int $filter = ReflectionMethod::IS_PUBLIC
    ) {
        parent::__construct($filter);
        $this->map = $map;
        $this->default = $default;
    }

    public function shouldSkip(ReflectionMethod $rm) : bool
    {
        if (empty($this->map)) {
            return !$this->default;
        }

        $name = $rm->getName();

        return !isset($this->map[$name]) || false === $this->map[$name];
    }
}

interface FinalizerInterface {
    public function finalize($res, Throwable $exception = null) : string;
    public function setStringify(callable $stringify);
}

class DefaultFinalizer implements FinalizerInterface {
    /**
     * @var callable
     */
    private $stringify;

    public function setStringify(callable $stringify) {
        $this->stringify = $stringify;
    }

    public function finalize($res, Throwable $exception = null) : string
    {
        if ($exception) {
            return get_class($exception) . ': ' . $exception->getMessage();
        }

        $stringify = $this->stringify;
        return $stringify($res);
    }
}

class CallChainFinalizer extends DefaultFinalizer {
    /**
     * @var array
     */
    private $map;
    /**
     * @var array
     */
    private $args;
    /**
     * @var bool
     */
    private $expanded;

    public function __construct(array $map = [], array $args = [], $expanded = true)
    {
        $this->map = $map;
        $this->args = $args;
        $this->expanded = $expanded;
    }

    public function finalize($res, Throwable $exception = null) : string
    {
        if ($exception) {
            return parent::finalize($res, $exception);
        }

        if (!is_object($res)) {
            return parent::finalize($res, $exception);
        }

        $cls = get_class($res);

        $method = isset($this->map[$cls]) ? $this->map[$cls] : (isset($this->map['@@default']) ? $this->map['@@default'] : '<nonexistent>');

        if (!is_callable([$res, $method])) {
            throw new RuntimeException("Invalid callable for {$cls} specified: '{$method}'");
        }

        $args = isset($this->args[$cls]) ? $this->args[$cls] : (isset($this->args['@@default']) ? $this->args['@@default'] : []);


        if (!is_array($args)) {
            $type = gettype($args);
            throw new RuntimeException("Invalid args type for {$cls} specified: expected array, {$type} given");
        }

        // TODO: at this place exception possible
        $res = $res->$method(...$args);

        if ($this->expanded) {
            return PHP_EOL . '    ' . '@@' .$cls . '->'. $method .'(): ' . parent::finalize($res, $exception);
        }

        return $cls.'->'. $method .'(): ' . parent::finalize($res, $exception);
    }
}

return new PhpV8Testsuite();
