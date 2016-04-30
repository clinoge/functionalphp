<?php
namespace CLinoge\Functional;
use ReflectionFunction;
use ReflectionMethod;

class F {
    // add :: Int -> Int -> Int
    public static function add() {
        return call_user_func_array(F::curry(function($x,$y) {
            return $x + $y;
        }), func_get_args());
    }

    // and :: Bool -> Bool -> Bool
    public static function and() {
        return call_user_func_array(F::curry(
            function ($x, $y) {
                return $x && $y;
        }), func_get_args());
    }

    // call :: (* -> *) -> [*] -> *
    public static function call() {
        return call_user_func_array(F::curry(
            function($fn, $args) {
                if (is_array($args)) {
                    return call_user_func_array($fn, $args);    
                } else {
                    return call_user_func_array($fn, [$args]);
                }
        }), func_get_args());
    }

    // chain :: (a -> Monad b) -> Monad a -> Monad b
    public static function chain() {
        return call_user_func_array(F::curry(
            function($f, $monad) {
                return $monad->map($f)->join();
        }), func_get_args());
    }

    // compose :: (a -> b), .., (x -> n) -> z
    // ^ almost same definition as in RamdaJS
    public static function compose() {
        $composeBinary = function ($f, $g) {
            return function($x = null) use ($f,$g) {
                if ($x) {
                    return $f($g($x));
                } else {
                    return $f($g());
                }
            };
        };

        return array_reduce(func_get_args(), $composeBinary, F::id());
    }

    // curry :: (* -> a) -> (* -> a)
    // ^ according to RamdaJS
    public static function curry($fn) {
        $rfl = new ReflectionFunction($fn);
        $args = $rfl->getNumberOfParameters();
        return F::__curry($fn, [], $args);
    }

    public static function curryN($fn, $n) {
        if ($n == 0) {
            return $fn;
        }
        else {
            return F::__curry($fn, [], $n);
        }
    }
    
    public static function curryRight($fn) {
        $rfl = new ReflectionFunction($fn);
        $args = $rfl->getNumberOfParameters();
        return F::__curry($fn, [], $args, true);
    }

    // every :: (a -> Bool), .. , (z -> Bool) -> Bool
    public static function every() {
        return call_user_func_array(F::curry(
            function($f, $xs) {
                $res = true;
                foreach($xs as $x) {
                    $res = $res && $f($x);
                }
                return $res;
        }), func_get_args());
    }

    // filter :: (a -> Bool) -> [a] -> [a]
    public static function filter() {
        return call_user_func_array(F::curry(function($fn, $xs) {
            return array_filter($xs, $fn);
        }), func_get_args());
    }

    // hasMethod :: String -> Object -> Bool
    public static function hasMethod() {
        return call_user_func_array(F::curry(
            function($method, $obj) {
                return method_exists($obj, $method);
        }), func_get_args());
    }

    // id :: a -> a
    public static function id() {
        return call_user_func_array(F::curry(
            function($x) {
                return $x;
        }), func_get_args());
    }

    // join :: Monad (Monad a) -> Monad a
    public static function join() {
        return call_user_func_array(F::curry(
            function($monad) {
                if (is_object($monad)) {
                    return $monad->join();    
                }
                // Arrays are monads
                if (is_array($monad)) {
                    if (count($monad) == 1
                        && is_array($monad[0])) {
                        return $monad[0];
                    }
                }
        }), func_get_args());
    }

    // last :: [a] -> a
    public static function last() {
        return call_user_func_array(F::curry(
            function($xs) {
                $length = count($xs);
                return $xs[$length - 1];
        }), func_get_args());
    }

    // map :: (a -> b) -> Monad a -> Monad b
    // List (arrays) are monads, aren't they?
    public static function map() {
        return call_user_func_array(F::curry(
            function ($fn, $xs) {
                if (is_object($xs) 
                    && $xs instanceof IMonad
                    && F::hasMethod('map', $xs)) {
                    return F::method('map', [$fn], $xs);
                }
                $new_arr = [];
                foreach($xs as $x) {
                    $new_arr[] = $fn($x);
                }
                return $new_arr;
        }), func_get_args());
    }

    // not :: (a -> b) -> a -> Bool
    public static function not() {
        return call_user_func_array(F::curry(
            function($f, $x) {
                return ! $f($x);
        }), func_get_args());
    }

    // method :: String -> [*] -> Object -> *
    public static function method() {
        return call_user_func_array(F::curry(
            function($method, $args, $obj) {
                return call_user_func_array([$obj, $method], $args);
        }), func_get_args());
    }

    // prop :: String -> Object -> *
    public static function prop() {
        return call_user_func_array(F::curry(
            function($prop, $obj) {
                if (!is_array($obj)) {
                    if (isset($obj->{$prop})) {
                        return $obj->{$prop};
                    }
                    return null;
                } else {
                    if (isset($obj[$prop])) {
                        return $obj[$prop];
                    }
                    return null;
                }
        }), func_get_args());
    }
    
    // safeProp :: String -> Object -> Maybe *
    public static function safeProp() {
        return call_user_func_array(F::curry(function($prop, $obj) {
            return Maybe::of(F::prop($prop, $obj));
        }), func_get_args());
    }

    // setProp :: String -> * -> Object -> Object
    // ^ immutable
    public static function setProp() {
        return call_user_func_array(F::curry(
            function($prop, $new_val, $obj) {
                $o = clone $obj;
                $o->{$prop} = $new_val;
                return $o;
        }), func_get_args());
    }

    // startsWith :: String -> String -> Bool
    public static function startsWith() {
        return call_user_func_array(F::curry(function($x, $y) {
            return substr($y, 0, strlen($x)) == $x;
        }), func_get_args());
    }

    // trace :: (Show a) => a -> a

    public static function trace() {
        return call_user_func_array(F::curry(function($args) {
            if (is_array($args))
            {
                call_user_func_array('print_r', $args);
            } else {
                call_user_func_array('print_r', [$args]);
            }
            return $args;
        }), func_get_args());
    }

    // T :: * -> Bool
    public static function T() {
        return call_user_func_array(F::curryN(function() {
            return true;
        }, 1), func_get_args());
    }

    public static function __curry($fn, $args, $n, $right = false, $firstCall = true) {
        if ($n <= 0) {
            if ($right) {
                return call_user_func_array($fn, array_reverse($args));
            }
            return call_user_func_array($fn, $args);
        } else {
            return function() use ($fn, $args, $n, $right, $firstCall) {
                $args1 = func_get_args();
                $fargs = array_merge($args, $args1);

                if ($firstCall == false 
                    && count($args1) == 0 ) {
                    return F::__curry($fn, $fargs, 0, false);
                }
                return F::__curry($fn,
                                  $fargs, 
                                  $n - count($args1), $right, false);
            };
        }
    }

    public static function __liftN($n) {
        return F::curryN(function() {
            $args = func_get_args();
            $functor = $args[1];
            $fn = $args[0];
            $a = F::add();

            return array_reduce(array_slice($args, 2),
                function ($carry, $fun) 
                    { 
                        return $carry->ap($fun); 
            }, $functor->map($args[0]));
        }, $n + 1);
    }

    // Used for lifting as of now
    public static function __callStatic($name, $args) {
        if (F::startsWith('lift', $name)) {
            return call_user_func_array(F::__liftN(intval(substr($name, 4))), $args);
        }
    }
}
