<?php

namespace App\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

class NewControllerMakeCommand extends GeneratorCommand
{
    protected $name = 'make:controller';

    protected $description = 'Create a new resource controller class';

    protected $type = 'Controller';

    protected function getStub()
    {
        return resource_path('stubs/controller.service.stub');
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\\Http\\Controllers';
    }

    protected function buildClass($name)
    {
        $stub = parent::buildClass($name);

        $model = $this->option('model');
        $modelLower = strtolower($model);

        return str_replace(
            ['DummyModel', 'model'],
            [$model, $modelLower],
            $stub
        );
    }

    protected function getOptions()
    {
        return [
            ['model', null, InputOption::VALUE_REQUIRED, 'The name of the model'],
            ['path', null, InputOption::VALUE_OPTIONAL, 'The optional path for the generated files'],
        ];
    }
}
