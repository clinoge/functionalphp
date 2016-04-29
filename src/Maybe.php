<?php

namespace CLinoge\Functional;

class Maybe implements IMonad, IFunctor {
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
