<?php

namespace Nave\IssSatellite;

use Nave\IssSatellite\Commands\IssSatelliteCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class IssSatelliteServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('iss-satellite')
            ->hasConfigFile();
        // ->hasViews()
        // ->hasMigration('create_iss_satellite_table')
        // ->hasCommand(IssSatelliteCommand::class);
    }
}
