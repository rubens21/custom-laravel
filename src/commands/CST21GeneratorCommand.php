<?php

namespace CST21\commands;

use CST21\Customize;
use Illuminate\Console\Command;
use Symfony\Component\Console\Helper\Table;

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
			$table = new Table($this->output);
			$table->setHeaders(['Table', 'Class', 'Path']);
			foreach ($this->customizer->getClasses() as $tableName => $metaClass){
			    if($metaClass->shouldBeIgnored()) {
					$table->addRow([$tableName, '', '-- ignored --']);
                } else {
					$result = $this->customizer->saveClassFile($tableName, config('cst21.path'));
					if($result === false ) {
						$this->error("$tableName Failed!");
					} else {
						$table->addRow([$tableName, $result['class_name'], $result['path']]);
					}
                }
			}
			$table->render();
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
