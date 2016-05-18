# FunctionalPHP

A small library porting tools from functional world (tm) to PHP. This is an ongoing effort and I'm doing it while learning about FP, along with category theory.

I've written a few tests. All functions are curried.

## Writing a curried function
```php
use CLinoge\Functional\F;

function myFunction(... $args) {
    $myFunction = F::curry(function($arg1, $arg2) {
        // Operate on $arg1 and $arg2
        return $result;
    });

    return call_user_func_array($myFunction, $args);
}
```

## Monads, Functors

There's basic support for IO, Maybe and Left/Right. Their implementation is based upon the ideas exposed in the [Mostly Adequate Guide](https://github.com/MostlyAdequate/mostly-adequate-guide) by [Dr. Boolean](https://github.com/DrBoolean) and RamdaJS

## Function placeholders
For a curried function n-ary f the following holds true:

```php
use CLinoge\Functional\Placeholder;

f(a1, a2, ..., a(N - 1), aN) == f(a1, new Placeholder, ..., new Placeholder, aN)(a2)(a(N-1));
```

## AutoCurry objects
Experimental feature, you can test it:

```php
use CLinoge\Functional\CurryTransform;

$constructor = CurryTransform::take(SomeClass::class);
$object = $constructor(a1, a2, ..., aN);
$object->someMethod($a1)($a2) == $object->someMethod($a1, $a2);
```

# Contributing
Add docs, improve perfomance, write tests :-). I'm improving this day by day and haven't still reached stable ^^.
