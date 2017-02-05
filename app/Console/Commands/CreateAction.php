<?php

namespace App\Console\Commands;

use Illuminate\Console\GeneratorCommand;

class CreateAction extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bot:action {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a new bot action';


    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Bot action';


    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/stubs/action.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Custom';
    }
}
