<?php

namespace App\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

class ServiceFileMakeCommand extends GeneratorCommand
{
    protected $name = 'make:service-file';

    protected $description = 'Create a new service class';

    protected $type = 'Service';

    protected function getStub()
    {
        return resource_path('stubs/service.stub');
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\\Services';
    }

    protected function buildClass($name)
    {
        $stub = parent::buildClass($name);

        $model = $this->option('model');
        $namespaceModel = 'App\\Models\\'.$model;

        return str_replace(
            ['DummyModel', 'DummyModelNamespace'],
            [$model, $namespaceModel],
            $stub
        );
    }

    protected function getOptions()
    {
        return [
            ['model', null, InputOption::VALUE_REQUIRED, 'The name of the model'],
        ];
    }
}
