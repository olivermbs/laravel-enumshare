<?php

namespace Olivermbs\LaravelEnumshare;

use Illuminate\Support\ServiceProvider;
use Olivermbs\LaravelEnumshare\Commands\EnumsDiscoverCommand;
use Olivermbs\LaravelEnumshare\Commands\EnumsExportAllLocalesCommand;
use Olivermbs\LaravelEnumshare\Commands\EnumsExportCommand;
use Olivermbs\LaravelEnumshare\Commands\EnumsWatchCommand;
use Olivermbs\LaravelEnumshare\Support\EnumAutoDiscovery;
use Olivermbs\LaravelEnumshare\Support\EnumRegistry;
use Olivermbs\LaravelEnumshare\Support\EnumValidator;
use Olivermbs\LaravelEnumshare\Support\TypeScriptEnumGenerator;
use Olivermbs\LaravelEnumshare\Support\TypeScriptTypeResolver;

class LaravelEnumshareServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/enumshare.php', 'enumshare'
        );

        $this->app->singleton(EnumValidator::class);

        $this->app->singleton(EnumAutoDiscovery::class, function ($app) {
            return new EnumAutoDiscovery(
                config('enumshare.auto_paths', [])
            );
        });

        $this->app->singleton(EnumRegistry::class, function ($app) {
            return new EnumRegistry(
                config('enumshare.enums', []),
                $app->make(EnumAutoDiscovery::class),
                $app->make(EnumValidator::class)
            );
        });

        $this->app->singleton(TypeScriptTypeResolver::class);

        $this->app->singleton(TypeScriptEnumGenerator::class, function ($app) {
            return new TypeScriptEnumGenerator(
                $app->make(TypeScriptTypeResolver::class)
            );
        });
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/Resources/Views', 'enumshare');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/enumshare.php' => config_path('enumshare.php'),
            ], 'enumshare-config');

            $this->commands([
                EnumsExportCommand::class,
                EnumsExportAllLocalesCommand::class,
                EnumsWatchCommand::class,
                EnumsDiscoverCommand::class,
            ]);
        }
    }
}
