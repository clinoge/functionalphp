<?php

namespace CLinoge\Functional;

class Right implements IMonad, IFunctor {
    public function __construct($value) {
        $this->value = $value;
    }

    public function fmap($x) {
        return $this->map($x);
    }

    public function ap($functor) {
        return F::chain(function($fn) use ($functor) {
            return $functor->map($fn);
        }, $this);
    }

    public static function of() {
        return call_user_func_array(F::curry(function($x) {
            return new Right($x);
        }), func_get_args());
    }

    public function map($fn) {
        return new Right($fn($this->value));
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
