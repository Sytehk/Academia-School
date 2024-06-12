<?php

namespace App\Console\Commands;

use Illuminate\Console\GeneratorCommand;

class NewInterfaceMakeCommand extends GeneratorCommand
{
    protected $name = 'make:interface-file';

    protected $description = 'Create a new interface class';

    protected $type = 'Interface';

    protected function getStub()
    {
        return resource_path('stubs/interface.stub');
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\\Interfaces';
    }
}
