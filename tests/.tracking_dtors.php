<?php declare(strict_types=1);

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


trait DestructMessageAwareTrait {
    //private $message = '';

    //public function setOnDestructMessage($message) {
    //    $this->message = $message;
    //}

    public function __destruct()
    {
        if (isset($this->destructor_test_message)) {
            $message = $this->destructor_test_message;
        }else {
            $message = (new \ReflectionClass($this))->getShortName() . ' dies now!';
        }

        echo $message, PHP_EOL;
    }
}

class Isolate extends \V8\Isolate {
    use DestructMessageAwareTrait;
}

class Context extends \V8\Context {
    use DestructMessageAwareTrait;
}

class Script extends \V8\Script {
    use DestructMessageAwareTrait;
}

class FunctionTemplate extends \V8\FunctionTemplate {
    use DestructMessageAwareTrait;
}

class ObjectTemplate extends \V8\ObjectTemplate {
    use DestructMessageAwareTrait;
}

class FunctionObject extends \V8\FunctionObject {
    use DestructMessageAwareTrait;
}

class Value extends \V8\Value {
    use DestructMessageAwareTrait;
}
