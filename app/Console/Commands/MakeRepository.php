<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeRepository extends Command
{
    protected $signature = 'make:repository {name} ' .
        '{--service : Create a service for the repository} ' .
        '{--resource : Create a resource for the model} ' .
        '{--controller : Create a controller that uses the service} ' .
        '{--all : Create repository, service, resource, and controller} ' .
        '{--repo-only : Create only the repository}';

    protected $description = 'Create a new repository class and optional related components';

    public function handle()
    {
        // Ensure BaseRepository exists
        if (!File::exists(app_path('Http/Repositories/BaseRepository.php'))) {
            $this->info('BaseRepository not found. Creating it...');
            Artisan::call('make:base-repository');
        }

        $name = $this->argument('name');
        $name = str_replace(['/', '\\'], '/', $name);
        $parts = explode('/', $name);
        $baseName = array_pop($parts);
        $subDir = count($parts) > 0 ? implode('/', $parts) : '';

        // Determine Model Name
        $model = Str::replaceLast('Repository', '', $baseName);
        $modelClass = "App\\Models\\{$model}";

        if (!class_exists($modelClass)) {
            $this->error("Model {$modelClass} does not exist.");
            return;
        }

        $this->info("Model resolved: {$model}");

        // Options
        $isAll = $this->option('all');
        $repoOnly = $this->option('repo-only');
        $makeService = ($isAll || $this->option('service')) && !$repoOnly;
        $makeResource = ($isAll || $this->option('resource')) && !$repoOnly;
        $makeController = ($isAll || $this->option('controller')) && !$repoOnly;

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

        // Resource Creation
        $resourceName = "{$model}Resource";
        $resourceNamespace = "App\\Http\\Resources" . ($subDir ? "\\" . str_replace('/', "\\", $subDir) : "");
        if ($makeResource) {
            $resourceFullName = ($subDir ? "{$subDir}/" : "") . $resourceName;
            Artisan::call('make:resource', ['name' => $resourceFullName]);
            $this->info("Resource {$resourceName} created successfully.");
        }

        // Service Creation
        $serviceName = "{$model}Service";
        $serviceNamespace = "App\\Http\\Services" . ($subDir ? "\\" . str_replace('/', "\\", $subDir) : "");
        $servicePath = app_path("Http/Services/" . ($subDir ? "{$subDir}/" : "") . "{$serviceName}.php");

        if ($makeService) {
            if (File::exists($servicePath)) {
                $this->error("Service already exists!");
            } else {
                if ($makeResource) {
                    $serviceTemplate = file_get_contents(__DIR__ . '/stubs/service-generate-all.stub');
                    $repoVarName = Str::camel($model) . "Repo";
                    
                    $serviceTemplate = str_replace(
                        ['{{ namespace }}', '{{ serviceName }}', '{{ repository_import }}', '{{ resource_import }}', '{{ model }}', '{{ repoVarName }}'],
                        [$serviceNamespace, $serviceName, "use {$repositoryNamespace}\\{$repositoryName};", "use {$resourceNamespace}\\{$resourceName};", $model, $repoVarName],
                        $serviceTemplate
                    );
                } else {
                    $serviceTemplate = file_get_contents(__DIR__ . '/stubs/service.stub');
                    $repoVarName = Str::camel($model) . "Repo";
                    
                    $content = "    public function __construct(\n" .
                               "        private {$repositoryName} \${$repoVarName}\n" .
                               "    ) {}";

                    $serviceTemplate = str_replace(
                        ['{{ namespace }}', '{{ serviceName }}', '{{ repository_import }}', '{{ content }}'],
                        [$serviceNamespace, $serviceName, "use {$repositoryNamespace}\\{$repositoryName};", $content],
                        $serviceTemplate
                    );
                }

                File::ensureDirectoryExists(dirname($servicePath));
                File::put($servicePath, $serviceTemplate);
                $this->info("Service {$serviceName} created successfully.");
            }
        }

        // Controller Creation
        if ($makeController) {
            $controllerName = "{$model}Controller";
            $controllerNamespace = "App\\Http\\Controllers" . ($subDir ? "\\" . str_replace('/', "\\", $subDir) : "");
            $controllerPath = app_path("Http/Controllers/" . ($subDir ? "{$subDir}/" : "") . "{$controllerName}.php");

            if (File::exists($controllerPath)) {
                $this->error("Controller already exists!");
            } else {
                $controllerTemplate = file_get_contents(__DIR__ . '/stubs/controller.stub');
                
                $serviceImport = '';
                $constructor = '';
                $indexContent = "        // TODO: Implement index";
                $storeContent = "        // TODO: Implement store";
                $showContent = "        // TODO: Implement show";
                $updateContent = "        // TODO: Implement update";
                $destroyContent = "        // TODO: Implement destroy";

                if ($makeService) {
                    $serviceImport = "use {$serviceNamespace}\\{$serviceName};";
                    $serviceVarName = Str::camel($serviceName);
                    $constructor = "    public function __construct(\n" .
                                   "        private {$serviceName} \${$serviceVarName}\n" .
                                   "    ) {}";
                    
                    $indexContent = "        return \$this->{$serviceVarName}->getAll();";
                    $storeContent = "        return \$this->{$serviceVarName}->create(\$request->validated());";
                    $showContent = "        return \$this->{$serviceVarName}->findById(\$id);";
                    $updateContent = "        return \$this->{$serviceVarName}->updateById(\$id, \$request->validated());";
                    $destroyContent = "        return \$this->{$serviceVarName}->deleteById(\$id);";
                }

                $controllerTemplate = str_replace(
                    [
                        '{{ namespace }}', 
                        '{{ controllerName }}', 
                        '{{ service_import }}', 
                        '{{ constructor }}',
                        '{{ index_content }}',
                        '{{ store_content }}',
                        '{{ show_content }}',
                        '{{ update_content }}',
                        '{{ destroy_content }}'
                    ],
                    [
                        $controllerNamespace, 
                        $controllerName, 
                        $serviceImport, 
                        $constructor,
                        $indexContent,
                        $storeContent,
                        $showContent,
                        $updateContent,
                        $destroyContent
                    ],
                    $controllerTemplate
                );

                File::ensureDirectoryExists(dirname($controllerPath));
                File::put($controllerPath, $controllerTemplate);
                $this->info("Controller {$controllerName} created successfully.");
            }
        }
    }
}
