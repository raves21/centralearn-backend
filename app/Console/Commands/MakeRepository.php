<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeRepository extends Command
{
    protected $signature = 'make:repository {name} {--repo-only : Skip service and resource} {--all : Generate all basic crud in service and make resource}';

    protected $description = 'Create a new repository class';

    public function handle()
    {
        $name = $this->argument('name');
        $name = str_replace(['/', '\\'], '/', $name);
        $parts = explode('/', $name);
        $baseName = array_pop($parts);
        $subDir = count($parts) > 0 ? implode('/', $parts) : '';

        // Determine Model Name
        $model = str_replace('Repository', '', $baseName);
        $modelClass = "App\\Models\\{$model}";

        if (!class_exists($modelClass)) {
            $this->error("Model {$modelClass} does not exist.");
            return;
        }

        $this->info("Model resolved: {$model}");

        // Repository Name and Path
        $repositoryName = str_ends_with($baseName, 'Repository') ? $baseName : "{$baseName}Repository";
        $repositoryNamespace = "App\\Http\\Repositories" . ($subDir ? "\\" . str_replace('/', "\\", $subDir) : "");
        $repositoryPath = app_path("Http/Repositories/" . ($subDir ? "{$subDir}/" : "") . "{$repositoryName}.php");

        if (File::exists($repositoryPath)) {
            $this->error("Repository already exists!");
        } else {
            $template = file_get_contents(__DIR__ . '/stubs/repository.stub');

            // Replace placeholders in the stub
            $modelVarName = Str::camel($model);
            $template = str_replace(
                ['{{ namespace }}', '{{ repository }}', '{{ modelName }}', '{{ modelVarName }}'],
                [$repositoryNamespace, $repositoryName, $model, $modelVarName],
                $template
            );

            File::ensureDirectoryExists(dirname($repositoryPath));
            File::put($repositoryPath, $template);

            $this->info("Repository {$repositoryName} created successfully in {$repositoryNamespace}.");
        }

        if ($this->option('repo-only')) {
            return;
        }

        // Service Creation
        $serviceName = "{$model}Service";
        $serviceNamespace = "App\\Http\\Services" . ($subDir ? "\\" . str_replace('/', "\\", $subDir) : "");
        $servicePath = app_path("Http/Services/" . ($subDir ? "{$subDir}/" : "") . "{$serviceName}.php");

        if (File::exists($servicePath)) {
            $this->error("Service already exists!");
            return;
        }

        if ($this->option('all')) {
            $resourceName = ($subDir ? "{$subDir}/" : "") . "{$model}Resource";
            Artisan::call('make:resource ' . $resourceName);
            $this->info("{$resourceName} created successfully.");
            
            $serviceTemplate = file_get_contents(__DIR__ . '/stubs/service-generate-all.stub');
            $repoVarName = Str::camel($model) . "Repo";
            $resourceNamespace = "App\\Http\\Resources" . ($subDir ? "\\" . str_replace('/', "\\", $subDir) : "");
            
            $serviceTemplate = str_replace(
                ['{{ namespace }}', '{{ serviceName }}', '{{ repository_import }}', '{{ resource_import }}', '{{ model }}', '{{ repoVarName }}'],
                [$serviceNamespace, $serviceName, "use {$repositoryNamespace}\\{$repositoryName};", "use {$resourceNamespace}\\{$model}Resource;", $model, $repoVarName],
                $serviceTemplate
            );
        } else {
            $serviceTemplate = file_get_contents(__DIR__ . '/stubs/service.stub');
            $serviceTemplate = str_replace(
                ['{{ namespace }}', '{{ serviceName }}', '{{ repository_import }}'],
                [$serviceNamespace, $serviceName, "use {$repositoryNamespace}\\{$repositoryName};"],
                $serviceTemplate
            );
        }

        File::ensureDirectoryExists(dirname($servicePath));
        File::put($servicePath, $serviceTemplate);

        $this->info("Service {$serviceName} created successfully in {$serviceNamespace}.");
    }
}
