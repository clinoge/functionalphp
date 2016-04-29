<?php

require dirname(__DIR__) . "/vendor/autoload.php";
$requires = array_slice(scandir(dirname(__DIR__) . "/src/"), 2);
array_map(function($x) { require dirname(__DIR__) . "/src/" . $x;}, $requires);

use CLinoge\Functional\F;
use QCheck\Generator as Gen;
use QCheck\Quick;

$add = Gen::forAll(
    [Gen::ints(), Gen::ints()],
    function($x) {
        return F::add($x, $x) == $x * 2;
    });

$startsWith = Gen::forAll(
    [Gen::asciiStrings(),
    Gen::asciiStrings()],
    function($str, $str2) {
        return F::and(
            F::startsWith($str, $str . $str2),
            F::startsWith($str2, $str2 . $str));
    });

$resultAdd = Quick::check(100, $add);
$resultStartsWith = Quick::check(100, $startsWith);

echo "Results for F::add\n";
var_dump($resultAdd);
echo "Results for F::startsWith\n";
var_dump($resultStartsWith);


