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

    public function getPrintFunctionTemplate (\v8\Isolate $isolate) {
        $print_func_tpl = new \v8\FunctionTemplate($isolate, function (\v8\FunctionCallbackInfo $args) {

            $context = $args->GetContext();

            $out = [];

            foreach ($args->Arguments() as $arg) {
                $out[] = $this->toString($arg, $context);
            }

            echo implode('', $out);
        });

        return $print_func_tpl;
    }

    /**
     * @param \v8\Value | \v8\ObjectValue | \v8\SymbolValue | \v8\StringValue | \v8\NumberValue $arg
     * @param \v8\Context   $context
     *
     * @return mixed|string
     */
    public function toString(\v8\Value $arg, \v8\Context $context)
    {
        if ($arg->IsUndefined()) {
            return '<undefined>';
        }

        if ($arg->IsNull()) {
            return var_export(null, true);
        }

        if ($arg->IsTrue() || $arg->IsFalse()) {
            return var_export($arg->BooleanValue($context), true);
        }

        if ($arg->IsArray()) {
            $len = $arg->Length();

            $items = '<empty>';

            if ($len > 0) {
                $items = [];
                for($i =0; $i < $len; $i++) {
                    $item = $arg->GetIndex($context, $i);

                    $items[] = $this->toString($item, $context);
                }

                $items = implode(', ', $items);
            }

            return '[' . $items . ']';
        }

        if ($arg->IsSymbol()) {
            return '{Symbol: ' . $arg->Name()->Value() . '}';
        }

        if ($arg->IsSymbolObject()) {
            return '{Symbol object: ' . $arg->ValueOf()->Name()->Value() . '}';
        }


        return $arg->ToString($context)->Value();
    }

    public function run_checks(\v8\Value $value, $title=null) {
        $title = $title ?: 'Checks on ' . get_class($value);
        $this->testsuite->header($title);

        $filter = new RegexpFilter('/^Is/');
        $this->testsuite->dump_object_methods($value, [], $filter);
        $this->testsuite->space();
    }

    public function CompileRun(\v8\Context $context, $script) {

        if (!($script instanceof \v8\StringValue)) {
            $script = new \v8\StringValue($context->GetIsolate(), $script);
        }

        $script = new \v8\Script($context, $script, new \v8\ScriptOrigin('test.js'));

        return $script->Run();
    }

    public function CompileTryRun(\v8\Context $context, $script) {
        try {
            $res = $this->CompileRun($context, $script);
        } catch (\Exception $e) {
            echo $script, ': ';
            $this->testsuite->exception_export($e);
            $res = null;
        }

        return $res;
    }

    public function ExpectString(\v8\Context $context, $script, $expected) {
        $res = $this->CompileTryRun($context,$script);

        if ($res) {
            if (!$res->IsString()) {
                echo 'Actual result for expected ', var_export($expected, true), ' is not a string', PHP_EOL;
            } else {
                $this->testsuite->value_matches($expected, $res->Value());
            }
        }
    }

    public function ExpectBoolean(v8\Context $context, $script, $expected) {
        $res = $this->CompileTryRun($context, $script);

        if ($res) {
            if (!$res->IsBoolean()) {
                echo 'Actual result for expected value is not a boolean', PHP_EOL;
                return;
            }

            $this->testsuite->value_matches($expected, $res->BooleanValue($context));
        }
    }

    public function ExpectTrue(\v8\Context $context, $script) {
        $this->ExpectBoolean($context, $script, true);
    }

    public function ExpectFalse(\v8\Context $context, $script) {
        $this->ExpectBoolean($context, $script, false);
    }

    public function ExpectObject(v8\Context $context, $script, \v8\Value $expected) {
        $res = $this->CompileTryRun($context,$script);

        if (!$res) {
            return;
        }

        if (!$res->SameValue($expected)) {
            echo 'Actual and expected objects are not the same', PHP_EOL;
        } else {
            echo 'Actual and expected objects are the same', PHP_EOL;
        }
    }

    public function ExpectUndefined(v8\Context $context, $script) {
        $res = $this->CompileTryRun($context,$script);

        if (!$res) {
            return;
        }

        if (!$res->IsUndefined()) {
            echo 'Actual result for expected value is not undefined', PHP_EOL;
        } else {
            echo 'Actual result for expected value is undefined', PHP_EOL;
        }
    }

    public function ExpectNumber(v8\Context $context, $script, $expected=null) {
        $res = $this->CompileTryRun($context,$script);

        if (!$res) {
            return;
        }

        if (!$res->IsNumber()) {
            echo 'Actual result for expected value is not a number', PHP_EOL;
            return;
        }

        if ($expected !== null) {
            $this->testsuite->value_matches($expected, $res->Int32Value($context));
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
