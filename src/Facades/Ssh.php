<?php

namespace Nave\IssSatellite\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Nave\IssSatellite\Ssh
 */
class Ssh extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Nave\IssSatellite\Ssh::class;
    }
}
