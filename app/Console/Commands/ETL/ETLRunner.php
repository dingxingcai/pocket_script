<?php 
namespace App\Console\Commands\ETL;

use App\ETL\ETL;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class ETLRunner extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'etl:runner';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description.';

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
        $opsQueues = $this->option('queue');
        $etlQueue = explode(',', $opsQueues);

        foreach($etlQueue as $name){
            $etlCfg = \Config::get($name);
            \Log::info("start task $name");

            ETL::constructFromCfg($etlCfg)->run();

            \Log::info("end task $name");
        }
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array(
            array('queue', null, InputOption::VALUE_REQUIRED, 'the queue is run'),
        );
    }
}
