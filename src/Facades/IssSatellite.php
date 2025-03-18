<?php

namespace Nave\IssSatellite\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Nave\IssSatellite\IssSatellite
 */
class IssSatellite extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Nave\IssSatellite\IssSatellite::class;
    }
}
