<?php
namespace CLinoge\Functional;

interface IMonad {
    public function map($x);
    public function join();
}
