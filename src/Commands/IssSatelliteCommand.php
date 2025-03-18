<?php

namespace Nave\IssSatellite\Commands;

use Illuminate\Console\Command;

class IssSatelliteCommand extends Command
{
    public $signature = 'iss-satellite';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
