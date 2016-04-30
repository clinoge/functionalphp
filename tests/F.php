<?php

require dirname(__DIR__) . "/vendor/autoload.php";
$requires = array_slice(scandir(dirname(__DIR__) . "/src/"), 2);
array_map(function($x) { require dirname(__DIR__) . "/src/" . $x;}, $requires);

use CLinoge\Functional\F;
use QCheck\Generator as Gen;
use QCheck\Quick;

$add = Gen::forAll(
    [Gen::ints(), Gen::ints()],
    function($x, $y) {
        return F::andN([
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
        $hasFalse = F::compose(F::find(false), F::filter(F::isFalse()));
        return $res == ! $hasFalse($xs);
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
