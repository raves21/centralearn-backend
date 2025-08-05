<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class MakeRepository extends Command
{
    protected $signature = 'make:repository {name} {--no-service : Skip service and resource} {--all : Generate all basic crud in service}';

    protected $description = 'Create a new repository class';

    public function handle()
    {
        if ($this->option('no-service') && $this->option('all')) {
            $this->error('Cannot have options: --no-service and --all at the same time');
            return;
        }
        $model = explode('Repository', $this->argument('name'))[0];
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

        // Replace placeholders in the stub
        $modelVarName = strtolower($model[0]) . substr($model, 1);
        $template = str_replace(
            ['{{ repository }}', '{{ modelName }}', '{{ modelVarName }}'],
            [$repositoryName, $model, $modelVarName],
            $template
        );

        if (!File::isDirectory(app_path('Http/Repositories'))) {
            File::makeDirectory(app_path('Http/Repositories'));
        }

        File::put($repositoryPath, $template);

        $this->info("Repository {$repositoryName} created successfully.");

        $noService = $this->option('no-service');

        if (!$noService) {
            $serviceName = "{$model}Service";
            $servicePath = app_path("Http/Services/{$serviceName}.php");

            if (File::exists($servicePath)) {
                $this->error("Service already exists!");
                return;
            }

            if ($this->option('all')) {
                Artisan::call('make:resource ' . "{$model}Resource");
                $this->info("{$model}Resource created successfully.");
                $serviceTemplate = file_get_contents(__DIR__ . '/stubs/service-generate-all.stub');
                $repoVarName = strtolower($model[0]) . substr($model, 1) . "Repo";
                // Replace placeholders in the stub
                $serviceTemplate = str_replace(
                    ['{{ serviceName }}', '{{ repository }}', '{{ model }}', '{{ repoVarName }}'],
                    [$serviceName, "use App\\Http\\Repositories\\{$repositoryName};", $model, $repoVarName],
                    $serviceTemplate
                );
            } else {
                $serviceTemplate = file_get_contents(__DIR__ . '/stubs/service.stub');
                // Replace placeholders in the stub
                $serviceTemplate = str_replace(
                    ['{{ serviceName }}', '{{ repository }}'],
                    [$serviceName, "use App\\Http\\Repositories\\{$repositoryName};"],
                    $serviceTemplate
                );
            }

            if (!File::isDirectory(app_path('Http/Services'))) {
                File::makeDirectory(app_path('Http/Services'));
            }

            File::put($servicePath, $serviceTemplate);

            $this->info("Service {$serviceName} created successfully.");
        }
    }
}
