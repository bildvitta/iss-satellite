<?php

namespace Nave\IssSatellite\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;

/**
 * @method static Collection<\Nave\IssSatellite\MegaCloud> getRealEstateDevelopments(array $query = [])
 * @method static Collection<\Nave\IssSatellite\MegaCloud> getRealEstateDevelopmentBlocks(string $realEstateDevelopmentId)
 * @method static Collection<\Nave\IssSatellite\MegaCloud> getRealEstateDevelopmentUnitsByBlock(string $realEstateDevelopmentId, string $blockId)
 * @method static Collection<\Nave\IssSatellite\MegaCloud> getRealEstateDevelopmentsWithBlocksAndUnits(array $query)
 * @method static Collection<\Nave\IssSatellite\MegaCloud> getAllRealEstateDevelopmentUnits(array $query)
 * @see \Nave\IssSatellite\MegaCloud
 */
class MegaCloud extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Nave\IssSatellite\MegaCloud::class;
    }
}
