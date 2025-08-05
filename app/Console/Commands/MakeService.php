<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeService extends Command
{
    protected $signature = 'make:service {name}';

    protected $description = 'Create a new service class';

    public function handle()
    {

        $name = $this->argument('name');
        $serviceName = "{$name}Service";
        $serviceName = str_contains($name, 'Service') ? $name  : "{$name}Service";
        $servicePath = app_path("Http/Services/{$serviceName}.php");

        if (File::exists($servicePath)) {
            $this->error("Service already exists!");
            return;
        }

        $template = file_get_contents(__DIR__ . '/stubs/service.stub');

        // Replace placeholders in the stub
        $template = str_replace(
            ['{{ serviceName }}'],
            [$serviceName],
            $template
        );

        if (!File::isDirectory(app_path('Services'))) {
            File::makeDirectory(app_path('Services'));
        }

        File::put($servicePath, $template);

        $this->info("Service {$name} created successfully.");
    }
}
