<?php

namespace Olivermbs\LaravelEnumshare;

use Illuminate\Support\ServiceProvider;
use Olivermbs\LaravelEnumshare\Commands\EnumsClearCommand;
use Olivermbs\LaravelEnumshare\Commands\EnumsDiscoverCommand;
use Olivermbs\LaravelEnumshare\Commands\EnumsExportCommand;
use Olivermbs\LaravelEnumshare\Commands\EnumsExportAllLocalesCommand;
use Olivermbs\LaravelEnumshare\Commands\EnumsWatchCommand;
use Olivermbs\LaravelEnumshare\Support\EnumAutoDiscovery;
use Olivermbs\LaravelEnumshare\Support\EnumRegistry;

class LaravelEnumshareServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/enumshare.php', 'enumshare'
        );

        $this->app->singleton(EnumAutoDiscovery::class, function ($app) {
            return new EnumAutoDiscovery(
                config('enumshare.autodiscovery.paths', []),
                config('enumshare.autodiscovery.namespaces', []),
                config('enumshare.autodiscovery.cache', [])
            );
        });

        $this->app->singleton(EnumRegistry::class, function ($app) {
            return new EnumRegistry(
                config('enumshare.enums', []),
                $app->make(EnumAutoDiscovery::class)
            );
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/enumshare.php' => config_path('enumshare.php'),
            ], 'enumshare-config');

            $this->publishes([
                __DIR__.'/../stubs/EnumRuntime.ts' => resource_path('js/enums/EnumRuntime.ts'),
            ], 'enumshare-stubs');


            $this->commands([
                EnumsExportCommand::class,
                EnumsExportAllLocalesCommand::class,
                EnumsWatchCommand::class,
                EnumsDiscoverCommand::class,
                EnumsClearCommand::class,
            ]);
        }
    }
}
