<?php

namespace App\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class GenerateCrudServiceCommand extends GeneratorCommand
{
    protected $name = 'make:crud';

    protected $description = 'Generate CRUD operations including controller, requests, service, interface, views, and routes for a given model';

    public function handle(): bool
    {
        $model = $this->argument('model');
        $path = $this->option('path') ?? '';

        // Generate model and migration
        // $this->generateModelWithMigration($model, $path);

        // Generate controller
        $this->generateController($model, $path = 'WEB');

        // Generate request classes
        $this->generateRequestClass($model, 'Store', $path = $model);
        $this->generateRequestClass($model, 'Update', $path = $model);

        // Generate service and interface
        $this->generateService($model, $path = $model);
        $this->generateInterface($model, $path = $model);
        $this->generateDataTable($model, $path = $model);

        // Generate views
        $this->generateView($model, 'index');
        $this->generateView($model, 'create');
        $this->generateView($model, 'edit');

        // Generate route file
        $this->generateRouteFile($model);

        // Now, add API specific generation
        // $this->generateApiController($model);

        return true;
    }

    // protected function generateModelWithMigration($model, $path)
    // {
    //     $namespace = $path ? Str::replace('/', '\\', $path) : '';

    //     $this->call('make:model', [
    //         'name' => ($namespace ? $namespace . '\\' : '') . $model,
    //         '--migration' => true, // generate migration automatically
    //     ]);
    // }

    protected function generateController($model, $path)
    {
        $controllerName = "{$model}Controller";
        $namespace = 'App\\Http\\Controllers\\'.($path ? Str::replace('/', '\\', $path).'\\' : '');

        $this->call('make:controller', [
            'name' => $namespace.$controllerName,
            '--model' => $model,
            '--path' => $path,
        ]);
    }

    protected function generateApiController($model)
    {
        $controllerName = "{$model}Controller";
        $namespace = 'App\\Http\\Controllers\\API';
        $controllerPath = app_path("Http/Controllers/API/{$controllerName}.php");

        // Load the custom API controller stub
        $stub = file_get_contents(resource_path('stubs/api-controller.stub'));

        // Replace placeholder values in the stub
        $content = str_replace(
            ['DummyNamespace', 'DummyFullModelClass', 'DummyClass', 'DummyModel', 'model'],
            [
                $namespace,
                "App\\Models\\{$model}",
                "{$model}Controller",
                "{$model}",
                strtolower($model),
            ],
            $stub
        );

        // Write the controller file to the existing directory
        file_put_contents($controllerPath, $content);
    }

    protected function generateRequestClass($model, $action, $path)
    {
        $name = "{$model}{$action}Request";
        $namespace = 'App\\Http\\Requests\\'.($path ? Str::replace('/', '\\', $path).'\\' : '');

        $this->call('make:request', [
            'name' => $namespace.$name,
        ]);
    }

    protected function generateInterface($model, $path)
    {
        $interfaceName = "{$model}Interface";
        $namespace = 'App\\Interfaces\\'.($path ? Str::replace('/', '\\', $path).'\\' : '');

        $this->call('make:interface-file', [
            'name' => $namespace.$interfaceName,
        ]);
    }

    protected function generateService($model, $path)
    {
        $serviceName = "{$model}Service";
        $namespace = 'App\\Services\\'.($path ? Str::replace('/', '\\', $path).'\\' : '');

        $this->call('make:service-file', [
            'name' => $namespace.$serviceName,
            '--model' => $model,
        ]);
    }

    protected function generateDataTable($model, $path)
    {
        $dataTableName = "{$model}DataTable";
        $namespace = 'App\\DataTables\\'.($path ? Str::replace('/', '\\', $path).'\\' : '');

        $this->call('make:datatable-file', [
            'name' => $namespace.$dataTableName,
            '--model' => $model,
        ]);
    }

    protected function generateView($model, $view)
    {
        $modelLower = strtolower($model);
        $viewPath = resource_path("views/backend/{$modelLower}/{$view}.blade.php");

        if (! file_exists($viewPath)) {
            $this->makeDirectory($viewPath);
            file_put_contents($viewPath, $this->getViewStub($view, $model));
        }
    }

    protected function getViewStub($view, $model)
    {
        $stub = file_get_contents(resource_path("stubs/{$view}.stub"));

        // Replace DummyModel with the actual model name
        return str_replace(['DummyModel', 'model'], [$model, strtolower($model)], $stub);
    }

    protected function generateRouteFile($model)
    {
        $modelLower = strtolower($model);
        $webRouteFile = base_path('routes/web.php');
        // $apiRouteFile = base_path('routes/api.php');

        // Web route content
        $webContent = <<<EOL
Route::resource('{$modelLower}s', App\Http\Controllers\\WEB\\{$model}Controller::class);
EOL;

        // API route content
        //         $apiContent = <<<EOL
        // Route::get('all-{$modelLower}', [App\Http\Controllers\API\\{$model}Controller::class, 'all']);
        // Route::apiResource('{$modelLower}s', App\Http\Controllers\API\\{$model}Controller::class);
        // EOL;

        // Include in web.php
        $this->includeInFile($webRouteFile, $webContent);

        // Include in api.php
        // $this->includeInFile($apiRouteFile, $apiContent);
    }

    protected function includeInFile($filePath, $content)
    {
        $includeStatement = trim($content);

        // Ensure the file doesn't already include the content
        $currentContent = file_get_contents($filePath);

        if (strpos($currentContent, $includeStatement) === false) {
            file_put_contents($filePath, PHP_EOL.$includeStatement.PHP_EOL, FILE_APPEND);
        }
    }

    protected function getStub()
    {
        return '';
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return '';
    }

    protected function getArguments()
    {
        return [
            ['model', InputOption::VALUE_REQUIRED, 'The name of the model'],
        ];
    }

    protected function getOptions()
    {
        return [
            ['path', null, InputOption::VALUE_OPTIONAL, 'The optional path for the generated files'],
        ];
    }
}
