<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeBaseRepository extends Command
{
    protected $signature = 'make:base-repository {--force : Overwrite the existing BaseRepository}';

    protected $description = 'Initialize the BaseRepository class';

    public function handle()
    {
        $path = app_path('Http/Repositories/BaseRepository.php');

        if (File::exists($path) && !$this->option('force')) {
            $this->error('BaseRepository already exists! Use --force to overwrite.');
            return;
        }

        $template = file_get_contents(__DIR__ . '/stubs/base-repository.stub');

        File::ensureDirectoryExists(dirname($path));
        File::put($path, $template);

        $this->info('BaseRepository created successfully in App\Http\Repositories.');
    }
}
