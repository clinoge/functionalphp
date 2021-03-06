<?php

namespace CLinoge\Functional;

class IO implements IMonad, IFunctor {
    public function __construct($fn) {
        $this->value = $fn;
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
            return new self(function() use ($x) {
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
