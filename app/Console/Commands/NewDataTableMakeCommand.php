<?php

namespace App\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

class NewDataTableMakeCommand extends GeneratorCommand
{
    protected $name = 'make:datatable-file';

    protected $description = 'Create a new yajra DataTables class';

    protected $type = 'DataTable';

    protected function getStub()
    {
        return resource_path('stubs/datatable.stub');
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\\DataTables';
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
