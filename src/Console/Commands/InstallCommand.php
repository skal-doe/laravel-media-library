<?php

namespace SkalDoe\MediaLibrary\Console\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'media-library:install';
    protected $description = 'Publie et exécute les migrations de laravel-media-library';

    public function handle(): int
    {
        $this->call('vendor:publish', [
            '--tag' => 'media-library-migrations',
        ]);

        $this->call('vendor:publish', [
            '--tag' => 'media-library-config',
        ]);

        $this->call('migrate');

        $this->info('laravel-media-library installé avec succès.');

        return self::SUCCESS;
    }
}
