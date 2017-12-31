<?php declare(strict_types=1);

/**
 * This file is part of the pinepain/php-v8 PHP extension.
 *
 * Copyright (c) 2015-2018 Bogdan Padalko <pinepain@gmail.com>
 *
 * Licensed under the MIT license: http://opensource.org/licenses/MIT
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source or visit
 * http://opensource.org/licenses/MIT
 */

class size {
    const b = 1;
    const kb = 1024;
    const mb = 1024 * 1024;
    const gb = 1024 * 1024 * 1024;
}

class PhpV8Helpers {

    /**
     * @var PhpV8Testsuite
     */
    private $testsuite;

    public function __construct(PhpV8Testsuite $testsuite) {

        $this->testsuite = $testsuite;
    }

    /**
     * @param \V8\Context      $context
     * @param \V8\StackFrame[] $frames
     *
     * @return \V8\ArrayObject
     */
    public function getStackTraceFramesAsArray(\V8\Context $context, array $frames)
    {
        $isolate = $context->getIsolate();

        $arr = new \V8\ArrayObject($context);

        foreach ($frames as $i => $frame) {
            $f = new \V8\ObjectValue($context);

            $f->set($context, new \V8\StringValue($isolate, 'line'), new \V8\NumberValue($isolate, $frame->getLineNumber()));
            $f->set($context, new \V8\StringValue($isolate, 'column'), new \V8\NumberValue($isolate, $frame->getColumn()));
            $f->set($context, new \V8\StringValue($isolate, 'scriptId'), new \V8\NumberValue($isolate, $frame->getScriptId()));
            $f->set($context, new \V8\StringValue($isolate, 'scriptName'), new \V8\StringValue($isolate, $frame->getScriptName()));
            $f->set($context, new \V8\StringValue($isolate, 'scriptNameOrSourceURL'), new \V8\StringValue($isolate, $frame->getScriptNameOrSourceURL()));
            $f->set($context, new \V8\StringValue($isolate, 'functionName'), new \V8\StringValue($isolate, $frame->getFunctionName()));
            $f->set($context, new \V8\StringValue($isolate, 'isEval'), new \V8\BooleanValue($isolate, $frame->isEval()));
            $f->set($context, new \V8\StringValue($isolate, 'isConstructor'), new \V8\BooleanValue($isolate, $frame->isConstructor()));

            $arr->set($context, new \V8\IntegerValue($isolate, $i), $f);
        }

        return $arr;
    }

    public function getPrintFunctionTemplate(\V8\Isolate $isolate, $newline = false)
    {
        $print_func_tpl = new \V8\FunctionTemplate($isolate, function (\V8\FunctionCallbackInfo $args) use ($newline) {

            $context = $args->getContext();

            $out = [];

            foreach ($args->arguments() as $arg) {
                $out[] = $this->toString($arg, $context);
            }

            echo implode('', $out), ($newline ? PHP_EOL : false);
        });

        return $print_func_tpl;
    }

    public function injectConsoleLog(\V8\Context $context) {
        $isolate = $context->getIsolate();
        $obj_tpl = new \V8\ObjectTemplate($isolate);
        $obj_tpl->set(new \V8\StringValue($isolate, 'log'), $this->getPrintFunctionTemplate($isolate, true));

        $console_obj = $obj_tpl->newInstance($context);
        $context->globalObject()->set($context, new \V8\StringValue($isolate, 'console'), $console_obj);

        return $context;
    }

    /**
     * @param \V8\Value | \V8\ObjectValue | \V8\SymbolValue | \V8\StringValue | \V8\NumberValue $arg
     * @param \V8\Context                                                                       $context
     *
     * @return mixed|string
     */
    public function toString(\V8\Value $arg, \V8\Context $context)
    {
        if ($arg->isUndefined()) {
            return '<undefined>';
        }

        if ($arg->isNull()) {
            return var_export(null, true);
        }

        if ($arg->isTrue() || $arg->isFalse()) {
            return var_export($arg->booleanValue($context), true);
        }

        if ($arg->isArray()) {
            $len = $arg->length();

            $items = '<empty>';

            if ($len > 0) {
                $items = [];
                for($i =0; $i < $len; $i++) {
                    $item = $arg->get($context, new \V8\NumberValue($context->getIsolate(), $i));

                    $items[] = $this->toString($item, $context);
                }

                $items = implode(', ', $items);
            }

            return '[' . $items . ']';
        }

        if ($arg->isSymbol()) {
            return '{Symbol: ' . $arg->name()->value() . '}';
        }

        if ($arg->isSymbolObject()) {
            /** @var \V8\SymbolObject $arg */
            return '{Symbol object: ' . $arg->valueOf()->name()->value() . '}';
        }


        return $arg->toString($context)->value();
    }

    public function run_checks(\V8\Value $value, $title=null) {
        $title = $title ?: 'Checks on ' . get_class($value);
        $this->testsuite->header($title);

        $filter = new ArrayListFilter(['typeOf'], false);
        $finalizer = new CallChainFinalizer([\V8\StringValue::class => 'value'], [], false);
        $this->testsuite->dump_object_methods($value, [], $filter, $finalizer);
        $this->testsuite->line();

        $filter = new RegexpFilter('/^is/');
        $this->testsuite->dump_object_methods($value, [], $filter);
        $this->testsuite->space();
    }

    public function CompileRun(\V8\Context $context, $script) {

        if (!($script instanceof \V8\StringValue)) {
            $script = new \V8\StringValue($context->getIsolate(), $script);
        }

        $script = new \V8\Script($context, $script, new \V8\ScriptOrigin('test.js'));

        return $script->run($context);
    }

    public function CompileTryRun(\V8\Context $context, $script) {
        try {
            $res = $this->CompileRun($context, $script);
        } catch (\Exception $e) {
            echo $script, ': ';
            $this->testsuite->exception_export($e);
            $res = null;
        }

        return $res;
    }

    public function ExpectString(\V8\Context $context, $script, $expected) {
        $res = $this->CompileTryRun($context,$script);

        if ($res) {
            if (!$res->isString()) {
                echo 'Actual result for expected ', var_export($expected, true), ' is not a string', PHP_EOL;
            } else {
                $this->testsuite->value_matches($expected, $res->value());
            }
        }
    }

    public function ExpectBoolean(V8\Context $context, $script, $expected) {
        $res = $this->CompileTryRun($context, $script);

        if ($res) {
            if (!$res->isBoolean()) {
                echo 'Actual result for expected value is not a boolean', PHP_EOL;
                return;
            }

            $this->testsuite->value_matches($expected, $res->booleanValue($context));
        }
    }

    public function ExpectTrue(\V8\Context $context, $script) {
        $this->ExpectBoolean($context, $script, true);
    }

    public function ExpectFalse(\V8\Context $context, $script) {
        $this->ExpectBoolean($context, $script, false);
    }

    public function ExpectObject(V8\Context $context, $script, \V8\Value $expected) {
        $res = $this->CompileTryRun($context,$script);

        if (!$res) {
            return;
        }

        if (!$res->sameValue($expected)) {
            echo 'Actual and expected objects are not the same', PHP_EOL;
        } else {
            echo 'Actual and expected objects are the same', PHP_EOL;
        }
    }

    public function ExpectUndefined(V8\Context $context, $script) {
        $res = $this->CompileTryRun($context,$script);

        if (!$res) {
            return;
        }

        if (!$res->isUndefined()) {
            echo 'Actual result for expected value is not undefined', PHP_EOL;
        } else {
            echo 'Actual result for expected value is undefined', PHP_EOL;
        }
    }

    public function ExpectNumber(V8\Context $context, $script, $expected=null) {
        $res = $this->CompileTryRun($context,$script);

        if (!$res) {
            return;
        }

        if (!$res->isNumber()) {
            echo 'Actual result for expected value is not a number', PHP_EOL;
            return;
        }

        if ($expected !== null) {
            $this->testsuite->value_matches($expected, $res->int32Value($context));
        }
    }

    public function CHECK($value, $strvalue) {
        echo 'CHECK ', $strvalue, ': ', ( $value ? 'OK' :'failed'), PHP_EOL;
    }

    public function CHECK_EQ($lhs, $rhs, $strvalue = '') {
        $strvalue = $strvalue ? ' (' . $strvalue . ')' : '';
        echo "CHECK_EQ{$strvalue}: ", ( $lhs === $rhs ? 'OK' :'failed'), PHP_EOL;
    }

    public function CHECK_NE($lhs, $rhs, $strvalue = '') {
        $strvalue = $strvalue ? ' (' . $strvalue . ')' : '';
        echo "CHECK_NE{$strvalue}: ", ( $lhs !== $rhs ? 'OK' :'failed'), PHP_EOL;
    }
}

#define CHECK_EQ(lhs, rhs) CHECK_OP(EQ, ==, lhs, rhs)
#define CHECK_NE(lhs, rhs) CHECK_OP(NE, !=, lhs, rhs)
#define CHECK_LE(lhs, rhs) CHECK_OP(LE, <=, lhs, rhs)
#define CHECK_LT(lhs, rhs) CHECK_OP(LT, <, lhs, rhs)
#define CHECK_GE(lhs, rhs) CHECK_OP(GE, >=, lhs, rhs)
#define CHECK_GT(lhs, rhs) CHECK_OP(GT, >, lhs, rhs)
#define CHECK_NULL(val) CHECK((val) == nullptr)
#define CHECK_NOT_NULL(val) CHECK((val) != nullptr)
#define CHECK_IMPLIES(lhs, rhs) CHECK(!(lhs) || (rhs))
