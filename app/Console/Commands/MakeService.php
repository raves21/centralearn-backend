<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeService extends Command
{
    protected $signature = 'make:service {name}';

    protected $description = 'Create a new service class';

    public function handle()
    {
        $name = $this->argument('name');
        $name = str_replace(['/', '\\'], '/', $name);
        $parts = explode('/', $name);
        $baseName = array_pop($parts);
        $subDir = count($parts) > 0 ? implode('/', $parts) : '';

        $serviceName = str_ends_with($baseName, 'Service') ? $baseName : "{$baseName}Service";
        $serviceNamespace = "App\\Http\\Services" . ($subDir ? "\\" . str_replace('/', "\\", $subDir) : "");
        $servicePath = app_path("Http/Services/" . ($subDir ? "{$subDir}/" : "") . "{$serviceName}.php");

        if (File::exists($servicePath)) {
            $this->error("Service already exists!");
            return;
        }

        $template = file_get_contents(__DIR__ . '/stubs/service.stub');

        // Try to find a repository to import
        $repositoryImport = '';
        $content = '';
        $modelName = Str::replaceLast('Service', '', $baseName);
        $repoName = "{$modelName}Repository";
        
        // Look for repository in same subfolder first
        $potentialRepoPath = app_path("Http/Repositories/" . ($subDir ? "{$subDir}/" : "") . "{$repoName}.php");
        if (File::exists($potentialRepoPath)) {
             $repoNamespace = "App\\Http\\Repositories" . ($subDir ? "\\" . str_replace('/', "\\", $subDir) : "");
             $repositoryImport = "use {$repoNamespace}\\{$repoName};";
             
             $repoVarName = Str::camel($modelName) . "Repo";
             $content = "    public function __construct(\n" .
                        "        private {$repoName} \${$repoVarName}\n" .
                        "    ) {}";
        }

        // Replace placeholders in the stub
        $template = str_replace(
            ['{{ namespace }}', '{{ serviceName }}', '{{ repository_import }}', '{{ content }}'],
            [$serviceNamespace, $serviceName, $repositoryImport, $content],
            $template
        );

        File::ensureDirectoryExists(dirname($servicePath));
        File::put($servicePath, $template);

        $this->info("Service {$serviceName} created successfully in {$serviceNamespace}.");
    }
}
