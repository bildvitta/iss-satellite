<?php

namespace Nave\IssSatellite\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Nave\IssSatellite\Ssh connection(string $connectionName)
 * @method static \Nave\IssSatellite\Ssh connect(bool $debug = false)
 * @see \Nave\IssSatellite\Ssh
 */
class Ssh extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Nave\IssSatellite\Ssh::class;
    }
}
