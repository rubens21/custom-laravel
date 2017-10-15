<?php

namespace ILazi\Coders\Console;

use CST21\Customize;
use Illuminate\Console\Command;

class CST21GeneratorCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cst21:generate {--t|table= : The name of the table}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate models base on your DB tables';


    /**
     * @var Customize
     */
    protected $customizer;

    /**
     * Create a new command instance.
     * @param Customize $customize
     */
    public function __construct(Customize $customize)
    {
        parent::__construct();

        $this->customizer = $customize;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //$table = $this->getTable();
        $this->customizer->map();
        $this->customizer->saveFiles(__DIR__.'/sample');
    }


    /**
     * @return string
     */
    protected function getTable()
    {
        return $this->option('table');
    }
}
