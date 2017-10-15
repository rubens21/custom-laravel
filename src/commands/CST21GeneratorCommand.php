<?php

namespace CST21\commands;

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
        try{
            $this->comment("Generating models in ".config('cst21.path'));
            //$table = $this->getTable();
            $this->customizer->map();
            $this->comment("Db mapped");
            $this->customizer->saveFiles(config('cst21.path'));
            $this->info("Success");
        } catch (\Exception $e) {
            $this->error("Sorry: ".$e->getMessage());
        }
    }


    /**
     * @return string
     */
    protected function getTable()
    {
        return $this->option('table');
    }
}
