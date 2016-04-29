<?php

namespace CLinoge\Functional;
use ReflectionMethod;
use ReflectionClass;

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
