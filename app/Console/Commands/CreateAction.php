<?php

namespace App\Console\Commands;

class CreateAction
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
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $fileName = $this->argument("name");
        //generate file
    }
}
