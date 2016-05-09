<?php
namespace CLinoge\Functional;
use ReflectionFunction;
use ReflectionMethod;

class F {
    // add :: Int -> Int -> Int
    public static function add() {
        $fn = function($x,$y) {
            return $x + $y;
        };

        return call_user_func_array(F::curry($fn), func_get_args());
    }

    // allEqual :: (Ord a) => [a] -> Bool
    public static function allEqual() {
        $fn = function($xs) {
            return F::every(F::isEqual(F::first($xs)), F::rest($xs));
        };

        return call_user_func_array(F::curry($fn), func_get_args());
    }

    // always :: a -> a
    public static function always() {
        $fn = function($x) {
            return function() use ($x) {
                return $x;
            };
        };

        return call_user_func_array(F::curry($fn), func_get_args());
    }

    // and :: Bool -> Bool -> Bool
    public static function and() {
        $fn = function ($x, $y) {
            return $x && $y;
        };

        return call_user_func_array(F::curry($fn), func_get_args());
    }

    // andN :: Bool -> ... -> Bool -> Bool
    public static function andN() {
        $fn = function($xs) {
            return array_reduce($xs, F::and(), true);
        };

        return call_user_func_array( F::curry($fn), [func_get_args()]);
    }

    // assoc :: * -> * -> Map * *
    public static function assoc() {
        $fn = function($k, $v) {
            return [$k => $v];
        };

        return call_user_func_array(F::curry($fn), func_get_args());
    }

    // call :: (* -> *) -> [*] -> *
    public static function call() {
        $fn = function($fn, $args) {
            if (is_array($args)) {
                return call_user_func_array($fn, $args);    
            } else {
                return call_user_func_array($fn, [$args]);
            }};

        return call_user_func_array( F::curry($fn),  func_get_args());
    }
    
    public static function callIfArgs() {
        $fn = function($fn, $args) {
            if (F::isEmpty($args)) {
                return $fn;
            } else {
                return F::call($fn, $args);
            }
        };

        return call_user_func_array(F::curry($fn), func_get_args());
    }

    // chain :: (a -> Monad b) -> Monad a -> Monad b
    public static function chain() {
        $fn = function($f, $monad) {
            return $monad->map($f)->join();
        };

        return call_user_func_array( F::curry($fn),  func_get_args());
    }

    // compose :: (a -> b), .., (x -> n) -> z
    // ^ almost same definition as in RamdaJS
    public static function compose() {
        $composeBinary = function ($f, $g) {
            return function($x = null) use ($f,$g) {
                return $f($g($x));
            };
        };

        return array_reduce(func_get_args(), $composeBinary, F::id());
    }

    // concat :: String -> String -> String
    public static function concat() {
        $concat = function($str, $str1) {
            return $str . $str1;
        };

        return call_user_func_array(F::curry($concat), func_get_args());
    }

    // cond :: [[(* -> Boolean), (* -> *)]] -> (* -> *)
    public static function cond() {
        $fn = function($cases, $x) {
            foreach($cases as $case) {
                $predFn = $case[0];
                $thenFn = $case[1];

                if ($predFn($x)) {
                    return $thenFn($x);
                }
            }
        };

        return call_user_func_array(F::curry($fn), func_get_args());
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
        $fn = function($f, $xs) {
            $res = true;
            foreach($xs as $x) {
                $res = $res && $f($x);
            }
            return $res;
        };

        return call_user_func_array(F::curry($fn), func_get_args());
    }

    // execute :: [(None -> a), ... , (None -> z)] -> [a, ..., z]
    public static function execute() {
        $fn = function($fxs) {
            $new_arr = [];

            foreach($fxs as $f) {
                $new_arr[] = $f();
            }

            return $new_arr;
        };

        return call_user_func_array(F::curry($fn), func_get_args());
    }

    // filter :: (a -> Bool) -> [a] -> [a]
    public static function filter() {
        $fn = function($fn, $xs) {
            return array_filter($xs, $fn);
        };

        return call_user_func_array(F::curry($fn), func_get_args());
    }

    // find :: a -> [a] -> a
    // ^ this should use Either or Maybe monad
    public static function find() {
        $fn = function($x, $xs) {
            foreach($xs as $x1) {
                if ($x == $x1) {
                    return $x;
                }
            }
            return false;
        };

        return call_user_func_array(F::curry($fn), func_get_args());
    }

    // first :: [a] -> a
    public static function first() {
        $fn = function($xs) {
            if (isset($xs[0])) {
                return $xs[0];
            } else {
                return array_values($xs)[0];
            }
        };

        return call_user_func_array(F::curryN($fn,1), func_get_args());
    }

    // foldr :: (a -> b) -> a -> [a] -> *
    public static function foldr() {
        $fn = function($fn, $z, $xs) {
            return F::__foldr($fn, $z, $xs);
        };

        return call_user_func_array(F::curry($fn), func_get_args());
    }
    // hasProp :: String -> Object -> Bool
    public static function hasProp() {
        $fn = function($prop, $obj) {
            if (is_object($obj)) {
                return isset($obj->{$prop});
            }
            else {
                return isset($obj[$prop]);
            }
        };

        return call_user_func_array(F::curry($fn), func_get_args());
    }

    // hasMethod :: String -> Object -> Bool
    public static function hasMethod() {
        $fn = function($method, $obj) {
            return method_exists($obj, $method);
        };

        return call_user_func_array(F::curry($fn), func_get_args());
    }

    // id :: a -> a
    public static function id() {
        $fn = function($x) {
            return $x;
        };

        return call_user_func_array(F::curry($fn), func_get_args());
    }

    // inverse :: Map -> Map
    public static function inverse() {
        $fn = function($xs) {
            return array_combine(array_values($xs), array_keys($xs));
        };

        return call_user_func_array(F::curry($fn), func_get_args());
    }

    public static function isEmpty() {
        $fn = function($xs) {
            return empty($xs);
        };

        return call_user_func_array(F::curry($fn), func_get_args());
    }

    // isFalse :: Bool -> Bool
    public static function isFalse() {
        $fn = function($x) {
            return $x === false;
        };

        return call_user_func_array(F::curryN($fn, 1),func_get_args());
    }

    // isNull :: * -> Bool
    public static function isNull() {
        $fn = function($x) {
            return $x === null;
        };

        return call_user_func_array(F::curry($fn), func_get_args());
    }

    // isEqual :: (Ord a b) => a -> b -> Bool
    public static function isEqual() {
        $fn = function($a, $b) {
            return $a === $b;
        };

        return call_user_func_array(F::curry($fn), func_get_args());
    }

    // join :: Monad (Monad a) -> Monad a
    public static function join() {
        $fn = function($monad) {
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
        };

        return call_user_func_array(F::curry($fn), func_get_args());
    }

    // juxt :: [(a -> b), ..., (a -> y)] -> [a, ..., a] -> [b, ..., y]
    public static function juxt() {
        $fn = function($fxs, $xs) {
            $new_arr = [];

            foreach($fxs as $f) {
                $new_arr[] = $f($xs);
            }

            return $new_arr;
        };

        return call_user_func_array(F::curry($fn), func_get_args());
    }

    // last :: [a] -> a
    public static function last() {
        $fn = function($xs) {
            $length = count($xs);
            return $xs[$length - 1];
        };

        return call_user_func_array(F::curry($fn), func_get_args());
    }

    // map :: (a -> b) -> Monad a -> Monad b
    // List (arrays) are monads, aren't they?
    public static function map() {
        $fn = function ($fn, $xs) {
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
        };

        return call_user_func_array(F::curry($fn), func_get_args());
    }

    // match :: Pattern -> String -> String
    public static function match() {
        $fn = function($pattern, $string) {
            $arr = [];
            preg_match($pattern, $string, $arr);
            if (count($arr) > 0) {
                return $arr[0];
            } else {
                return null;
            }
        };

        return call_user_func_array(F::curry($fn), func_get_args());
    }

    // maybe :: a -> (b -> c) -> Monad -> a | c
    public static function maybe() {
        $fn = function($x, $f, $m) {
            if (! $m->value) {
                return $x;
            } else {
                return $f($m->value);
            }
        };

        return call_user_func_array(F::curry($fn), func_get_args());
    }

    public static function contain() {
        $fn = function($f, $args) {
            return function () use ($f, $args) {
                return call_user_func_array($f, $args);
            };
        };

        return call_user_func_array($fn, func_get_args());
    }

    // not :: (a -> b) -> a -> Bool
    public static function not() {
        $fn = function($f, $x) {
            return ! $f($x);
        };

        return call_user_func_array(F::curry($fn), func_get_args());
    }

    // not working, will take care of it later
    public static function memoize() {
        $fn = function() {
            $args = func_get_args();
            $fn = $args[0];
            $args1 = array_slice($args,1);
            $memory = [];

            $fn2 = function($fn, $args) use ($memory) {
                if (!isset($memory[$args])) {
                    $memory[$args] = call_user_func_array($fn, $args);    
                }

                return $memory[$args];
            };

            return call_user_func_array($fn2, $args1);
        };

        return call_user_func_array($fn, func_get_args());
    }

    // method :: String -> [*] -> Object -> *
    public static function method() {
        $fn = function($method, $args, $obj) {
            return call_user_func_array([$obj, $method], $args);
        };

        return call_user_func_array(F::curry($fn), func_get_args());
    }

    // prop :: String -> Object -> *
    public static function prop() {
        $fn = function($prop, $obj) {
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
        };

        return call_user_func_array(F::curry($fn), func_get_args());
    }

    // reference :: [Object | String, String | None] -> Callable
    public static function reference() {
        $fn = function($xs) {
            $subject = $xs[0];

            if (! is_callable($subject) &&
                (is_object($subject) || class_exists($subject))) {
                $method = $xs[1];
                if (is_object($subject)) {
                    $rflM = new ReflectionMethod(get_class($subject), $method);
                }
                else {
                    $rflM = new ReflectionMethod($subject, $method);
                }
                $rflP = $rflM->getNumberOfParameters();
                $fn2 = F::curryN(function() use ($subject, $method) {
                    return call_user_func_array([$subject, $method], func_get_args());
                }, $rflP);
                $args = array_slice($xs, 2);
            }

            else if (is_callable($subject) || 
                    (is_string($subject) && function_exists($subject))) {
                $func = $subject;
                $rflF = new ReflectionFunction($subject);
                $rflP = $rflF->getNumberOfParameters();
                $fn2 = F::curryN(function() use ($func) {
                    return call_user_func_array($func, func_get_args());
                }, $rflP);
                $args = array_slice($xs, 1);
            }
            if (F::not(F::isEmpty(), $args)) {
                return call_user_func_array($fn2, $args);
            }
            return $fn2;
        };

        return call_user_func_array(F::curry($fn), [func_get_args()]);
    }

    // rest :: [a] -> [a]
    public static function rest() {
        $fn = function($xs) {
            return array_slice($xs, 1);
        };

        return call_user_func_array(F::curryN($fn, 1), func_get_args());
    }

    // reverse :: [a] -> [a]
    public static function reverse() {
        $fn = function($xs) {
            return array_reverse($xs);
        };

        return call_user_func_array(F::curry($fn), func_get_args());
    }
    
    // safeMatch :: String -> String -> Maybe String
    public static function safeMatch() {
        $fn = function($pattern, $string) {
            return Maybe::of(F::match($pattern, $string));
        };

        return call_user_func_array(F::curry($fn), func_get_args());
    }

    // safeProp :: String -> Object -> Maybe *
    public static function safeProp() {
        $fn = function($prop, $obj) {
            return Maybe::of(F::prop($prop, $obj));
        };

        return call_user_func_array(F::curry($fn), func_get_args());
    }

    // setProp :: String -> * -> Object -> Object
    // ^ immutable
    public static function setProp() {
        $fn = function($prop, $new_val, $obj) {
            if (is_object($obj)) {
                $o = clone $obj;
                $o->{$prop} = $new_val;
                return $o;
            }
            else {
                $new_arr = $obj;
                $new_arr[$prop] = $new_val;
                return $new_arr;
            }
        };

        return call_user_func_array(F::curry($fn), func_get_args());
    }

    // startsWith :: String -> String -> Bool
    public static function startsWith() {
        $fn = function($x, $y) {
            return substr($y, 0, strlen($x)) == $x;
        };

        return call_user_func_array(F::curry($fn), func_get_args());
    }

    // trace :: (Show a) => a -> a
    public static function trace() {
        $fn = function($args) {
            var_dump($args);
            return $args;
        };

        return call_user_func_array(F::curry($fn), func_get_args());
    }

    // T :: * -> Bool
    public static function T() {
        $fn = function() {
            return true;
        };

        return call_user_func_array(F::curryN($fn, 1), func_get_args());
    }

    public static function __curry($fn, $args, $n, $right = false) {
        if ($n <= 0) {
            if ($right) {
                return call_user_func_array($fn, array_reverse($args));
            }
            return call_user_func_array($fn, $args);
        } else {
            return function() use ($fn, $args, $n, $right) {
                $args1 = func_get_args();
                $fargs = array_merge($args, $args1);

                return F::__curry($fn,
                                  $fargs, 
                                  $n - count($args1), $right);
            };
        }
    }

    public static function __foldr($fn, $z, $xs) {
        if ($xs == []) {
            return $z;
        } else {
            return $fn(F::first($xs), F::__foldr($fn,$z, F::rest($xs)));
        }
    }

    public static function __liftN($n) {
        $fn = function() {
            $args = func_get_args();
            $functor = $args[1];
            $fn = $args[0];

            return array_reduce(array_slice($args, 2),
                function ($carry, $fun) 
                    { 
                        return $carry->ap($fun); 
            }, $functor->map($args[0]));
        };

        return F::curryN($fn, $n + 1);
    }

    // Used for lifting as of now
    public static function __callStatic($name, $args) {
        if (F::startsWith('lift', $name)) {
            return call_user_func_array(F::__liftN(intval(substr($name, 4))), $args);
        }
    }
}
