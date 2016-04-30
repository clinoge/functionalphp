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
        return F::add($x, $x) == $x * 2;
    });

$and = Gen::forAll(
    [Gen::booleans(), Gen::booleans()],
    function ($x, $y) {
        return F::and($x, $y) == F::and($y, $x);
    });

$startsWith = Gen::forAll(
    [Gen::asciiStrings(),
    Gen::asciiStrings()],
    function($str, $str2) {
        return F::every( F::id(),
            [F::startsWith($str, $str . $str2),
            F::startsWith($str2, $str2 . $str)]);
    });

$resultAdd = [Quick::check(100, $add), 'F::add'];
$resultAnd = [Quick::check(100, $and), 'F::and'];
$resultStartsWith = [Quick::check(100, $startsWith), 'F::startsWith'];

$vars = get_defined_vars();

$getTests = F::filter(F::startsWith('result'));
$printTests = F::map(function($x) use ($vars) {
    $r = $vars[$x];
    echo "Tests for ${r[1]}\n";
    var_dump($r[0]);
    return $r;
});
$runTests = F::compose($printTests, $getTests);

F::call($runTests, [array_keys($vars)]);
