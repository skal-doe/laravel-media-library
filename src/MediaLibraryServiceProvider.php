// src/MediaLibraryServiceProvider.php
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

        Route::prefix('api/' . config('media-library.route_prefix'))
            ->middleware(config('media-library.middleware'))
            ->group(__DIR__ . '/../routes/api.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/media-library.php' => config_path('media-library.php'),
            ], 'media-library-config');
        }
    }
}