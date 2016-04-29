<?php

namespace CLinoge\Functional;
use ReflectionMethod;
use ReflectionClass;

interface Monad {
    public function map($x);
    public function join();
}

interface Functor {
    public function fmap($x);
    public function ap($x);
}

class Maybe implements Monad, Functor {
    public function __construct($x) {
        $this->value = $x;
    }

    public function fmap($x) {
        $this->map($x);
    }

    public function ap($functor) {
        return $functor->map($this->value);
    }

    public static function of() {
        return call_user_func_array(F::curry(function($x) {
            return new self($x); }), func_get_args());
    }

    public function map($f) {
        if (F::prop('value', $this) != null) {
            return new $this($f(F::prop('value', $this)));
        }
        else {
            return $this;
        }
    }

    public function join() {
        if (is_object($this->value) && 
            get_class($this->value) == get_class($this)) {
            return $this->value;
        } else {
            return $this;
        }
    }
}

class IO implements Monad, Functor {
    public function __construct($fn) {
        $this->value = $fn;
    }

    public function fmap($x) {
        $this->map($x);
    }

    public function ap($x) {
        return;
    }

    public static function of() {
        return call_user_func_array(F::curry(function($x) {
            new self(function() use ($x) {
                return $x;
            });
        }), func_get_args());
    }

    public function map($fn) {
        return new $this(F::compose($fn, F::prop('value', $this)));
    }

    public function __unsafePerformIO() {
        return ($this->value)();
    }

    public function join() {
        $thiz = $this;

        return new IO(function() use ($thiz) {
            $stg1 = $thiz->__unsafePerformIO();

            if (is_object($stg1) && get_class($stg1) == get_class($this)) {
                return $thiz->__unsafePerformIO()->__unsafePerformIO();
            }
            return $stg1;
        });
    }
}

// Curries every method of any object, including constructor!
class CurryTransform {
    public function __construct($x) {
        $this->contained = $x;
    }

    public function __call($name, $args) {
        $rflMethod = new ReflectionMethod(get_class($this->contained), $name);
        $params = $rflMethod->getNumberOfParameters();

        return call_user_func_array(F::curryN(
            function() use ($name) {
                return F::method($name, func_get_args(), $this->contained);
        }, $params), $args);
    }

    public static function take($className) {
        $rflClass = new ReflectionClass($className);
        $params =$rflClass->getConstructor()->getNumberOfParameters();

        return call_user_func_array(F::curryN(
            function() use ($rflClass) {
                return new self($rflClass->newInstanceArgs(func_get_args()));
        }, $params), array_diff(func_get_args(), [$className]));
    }
}
