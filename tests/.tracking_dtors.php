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

namespace v8Tests\TrackingDtors;

class Isolate extends \v8\Isolate {
    public function __destruct() {
        echo 'Isolate dies now!', PHP_EOL;
    }
}

class Context extends \v8\Context {
    public function __destruct() {
        echo 'Context dies now!', PHP_EOL;
    }
}

class Script extends \v8\Script {
    public function __destruct() {
        echo 'Script dies now!', PHP_EOL;
    }
}

class FunctionTemplate extends \v8\FunctionTemplate {
    public function __destruct() {
        echo 'FunctionTemplate dies now!', PHP_EOL;
    }
}

class ObjectTemplate extends \v8\ObjectTemplate {
    public function __destruct() {
        echo 'ObjectTemplate dies now!', PHP_EOL;
    }
}

class FunctionObject extends \v8\FunctionObject {
    public function __destruct() {
        echo 'FunctionObject dies now!', PHP_EOL;
    }
}

class Value extends \v8\Value {
    public function __destruct() {
        echo 'Value dies now!', PHP_EOL;
    }
}
