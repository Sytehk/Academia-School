<?php

namespace App\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

class NewActionMakeCommand extends GeneratorCommand
{
    protected $name = 'make:action';

    protected $description = 'Create a new action class';

    protected $type = 'Action';

    protected function getStub()
    {
        return resource_path('stubs/action.stub');
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\\Actions';
    }

    protected function buildClass($name)
    {
        $stub = parent::buildClass($name);

        $model = $this->option('model');
        $action = $this->option('action');

        return str_replace(
            ['DummyModel', 'DummyAction'],
            [$model, $action],
            $stub
        );
    }

    protected function getOptions()
    {
        return [
            ['model', null, InputOption::VALUE_REQUIRED, 'The name of the model'],
            ['action', null, InputOption::VALUE_REQUIRED, 'The action type (Create, Read, Update, Delete)'],
        ];
    }
}
