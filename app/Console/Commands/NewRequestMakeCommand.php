<?php

namespace App\Console\Commands;

use Illuminate\Console\GeneratorCommand;

class NewRequestMakeCommand extends GeneratorCommand
{
    protected $name = 'make:request';

    protected $description = 'Create a new form request class';

    protected $type = 'Request';

    protected function getStub()
    {
        return resource_path('stubs/request.stub');
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\\Http\\Requests';
    }

    protected function buildClass($name)
    {
        $stub = parent::buildClass($name);

        return $stub;
    }
}
