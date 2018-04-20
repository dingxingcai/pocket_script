<?php 
namespace ETLCommand;

use ETL\Project\AbstractProject;
use ETL\Project\ProjectFactory;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;

class ETLProjectRunner extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'etl:project';

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
    public function fire()
    {
        $cmd = $this->argument('operate');
        if($cmd == 'clean')
        {
            $date = $this->option('date');
            $view = $this->option('view');

            $rebuilds = \EtlProjectRecord::whereIn('status', array(
                AbstractProject::STATUS_BEGIN,
                AbstractProject::STATUS_DISCARDED,
                AbstractProject::STATUS_EXPIRED))->where('created_time', '<', $date)->get();

            foreach($rebuilds as $build){
                $cfg = \Config::get("etlProject.$build->name");
                $cfg['name'] = $build->name;
                $project = ProjectFactory::createProject($cfg['adapter'], $cfg);

                if($view === true){
                    \Log::info('', $build->toArray());
                }else{
                    $project->clean($build);
                }

            }
        }else{
            $projectName = $this->option('project');
            $cfg = \Config::get("etlProject.$projectName");
            $cfg['name'] = $projectName;

            $queue = $this->option('queue');
            !is_null($queue) && $cfg['queue'] = explode(',', $queue);

            $project = ProjectFactory::createProject($cfg['adapter'], $cfg);
            if($cmd == 'rebuild'){
                $project->rebuild();
            }else if($cmd == 'run'){
                $project->run();
            }else if($cmd == 'swap'){
                $project->swap();
            }
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array(
            array('operate', InputArgument::OPTIONAL, 'An example argument.', 'run'),
        );
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array(
            array('project', null, InputOption::VALUE_OPTIONAL, 'the project is run'),
            array('queue', null, InputOption::VALUE_OPTIONAL, 'the project is run'),
            array('date', null, InputOption::VALUE_OPTIONAL, 'clean时候用,删除指定时间之前的', date('Y-m-d', strtotime('-1 day'))),
            array('view', null, InputOption::VALUE_OPTIONAL, 'clean时候用,预览模式', true)
        );
    }
}
