<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeRepository extends Command
{
    protected $signature = 'make:repository {name} {--model= : The model class to use} {--no-service= : Skip service} {--base : Extend base repository}';

    protected $description = 'Create a new repository class';

    public function handle()
    {
        $model = $this->option('model');

        if (!$model) {
            $this->error('Please provide model name.');
            return;
        }

        $modelClass = "App\\Models\\{$model}";

        if (!class_exists($modelClass)) {
            $this->error('Model does not exist.');
            return;
        }

        $this->info("Model resolved {$model}");

        $name = $this->argument('name');
        $repositoryName = str_contains($name, 'Repository') ? $name  : "{$name}Repository";
        $repositoryPath = app_path("Http/Repositories/{$repositoryName}.php");

        if (File::exists($repositoryPath)) {
            $this->error("Repository already exists!");
            return;
        }

        $template = file_get_contents(__DIR__ . '/stubs/repository.stub');
        $extendsBase = $this->option('base') ? "extends BaseRepository" : "";

        // Replace placeholders in the stub
        $template = str_replace(
            ['{{repository}}', '{{modelName}}', '{{extendsBase}}'],
            [$repositoryName, $model, $extendsBase],
            $template
        );

        if (!File::isDirectory(app_path('Http/Repositories'))) {
            File::makeDirectory(app_path('Http/Repositories'));
        }

        File::put($repositoryPath, $template);

        $this->info("Repository {$repositoryName} created successfully.");

        $noService = $this->option('no-service');

        if (empty($noService)) {
            $serviceName = "{$model}Service";
            $servicePath = app_path("Http/Services/{$serviceName}.php");

            if (File::exists($servicePath)) {
                $this->error("Service already exists!");
                return;
            }

            $serviceTemplate = file_get_contents(__DIR__ . '/stubs/service.stub');
            // Replace placeholders in the stub
            $serviceTemplate = str_replace(
                ['{{serviceName}}', '{{repository}}'],
                [$serviceName, "use App\\Http\\Repositories\\{$repositoryName};"],
                $serviceTemplate
            );

            if (!File::isDirectory(app_path('Http/Services'))) {
                File::makeDirectory(app_path('Http/Services'));
            }

            File::put($servicePath, $serviceTemplate);

            $this->info("Service {$serviceName} created successfully.");
        }
    }
}
