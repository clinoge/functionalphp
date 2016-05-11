<?php

require dirname(__DIR__) . "/vendor/autoload.php";
$requires = array_slice(scandir(dirname(__DIR__) . "/src/"), 2);
array_map(function($x) { require dirname(__DIR__) . "/src/" . $x;}, $requires);

use CLinoge\Functional\F;
use CLinoge\Functional\Placeholder;
use QCheck\Generator as Gen;
use QCheck\Quick;

$add = Gen::forAll(
    [Gen::ints(), Gen::ints()],
    function($x, $y) {
        return F::andN([
            F::add(new Placeholder, new Placeholder)($x)($y) == F::add($x, $y),
            F::add($x, $y) == F::add($y, $x),
            F::add($x, 0) == $x
        ]);
    });

$and = Gen::forAll(
    [Gen::booleans(), Gen::booleans()],
    function ($x, $y) {
        return F::andN([
            F::and($x, $y) == F::and($y, $x),
            F::and($x, false) == false,
            F::and($x, true) == $x
        ]);
    });

$andN = Gen::forAll(
    [Gen::booleans()->intoArrays()->notEmpty()],
    function ($xs) {
        $res = F::andN($xs);
        $hasFalse = F::compose(F::find(F::isFalse()), F::filter(F::isFalse()));
        return $res == ! $hasFalse($xs);
    });

$call = Gen::forAll(
    [Gen::ints(), Gen::ints()],
    function ($x, $y) {
        $f = F::add();
        $fn = F::add($x);
        $result = F::add($x,$y);
        return F::allEqual([
            F::call($f, [$x,$y]),
            F::call($fn, [$y]),
            $result
        ]);
    });

$startsWith = Gen::forAll(
    [Gen::asciiStrings(),
    Gen::asciiStrings()],
    function($str, $str2) {
        return F::andN([
            F::startsWith($str, $str . $str2),
            F::startsWith($str2, $str2 . $str)
        ]);
    });

$resultAdd = [
    Quick::check(100, $add), 
    'F::add'
];
$resultAnd = [
    Quick::check(100, $and), 
    'F::and'
];

$resultAndN = [
    Quick::check(100, $andN),
    'F::andN'
];

$resultStartsWith = [
    Quick::check(100, $startsWith), 
    'F::startsWith'
];

$resultCall = [
    Quick::check(100, $call),
    'F::call'
];

$vars = get_defined_vars();

$getTests = F::filter(F::startsWith('result'));
$printTests = F::map(function($x) use ($vars) {
    $r = $vars[$x];
    echo "Tests results for ${r[1]}\n";
    var_dump($r[0]);
    return $r;
});
$runTests = F::compose($printTests, $getTests);

$runTests(array_keys($vars));
