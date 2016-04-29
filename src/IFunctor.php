<?php

namespace CLinoge\Functional;

interface IFunctor {
    public function fmap($x);
    public function ap($x);
}
