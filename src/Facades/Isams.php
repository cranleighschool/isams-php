<?php

namespace spkm\isams\Facades;

use Illuminate\Support\Facades\Facade;

class Isams extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'isams';
    }
}
