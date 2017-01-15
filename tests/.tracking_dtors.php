<?php

/**
 * This file is part of the pinepain/php-v8 PHP extension.
 *
 * Copyright (c) 2015-2017 Bogdan Padalko <pinepain@gmail.com>
 *
 * Licensed under the MIT license: http://opensource.org/licenses/MIT
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source or visit
 * http://opensource.org/licenses/MIT
 */

namespace v8Tests\TrackingDtors;

class Isolate extends \V8\Isolate {
    public function __destruct() {
        echo 'Isolate dies now!', PHP_EOL;
    }
}

class Context extends \V8\Context {
    public function __destruct() {
        echo 'Context dies now!', PHP_EOL;
    }
}

class Script extends \V8\Script {
    public function __destruct() {
        echo 'Script dies now!', PHP_EOL;
    }
}

class FunctionTemplate extends \V8\FunctionTemplate {
    public function __destruct() {
        echo 'FunctionTemplate dies now!', PHP_EOL;
    }
}

class ObjectTemplate extends \V8\ObjectTemplate {
    public function __destruct() {
        echo 'ObjectTemplate dies now!', PHP_EOL;
    }
}

class FunctionObject extends \V8\FunctionObject {
    public function __destruct() {
        echo 'FunctionObject dies now!', PHP_EOL;
    }
}

class Value extends \V8\Value {
    public function __destruct() {
        echo 'Value dies now!', PHP_EOL;
    }
}
