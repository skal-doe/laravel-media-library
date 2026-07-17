<?php

namespace SkalDoe\MediaLibrary;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class MediaLibraryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/media-library.php', 'media-library');
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        Route::group([
            'prefix' => 'api/' . config('media-library.route_prefix'),
            'middleware' => array_unique(array_merge(
                config('media-library.middleware', []),
                [\Illuminate\Routing\Middleware\SubstituteBindings::class],
            )),
        ], function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        });

        if ($this->app->runningInConsole()) {
            $this->publishesMigrations([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'media-library-migrations');

            $this->publishes([
                __DIR__ . '/../config/media-library.php' => config_path('media-library.php'),
            ], 'media-library-config');
        }
    }
}
