<?php
namespace App\ETL\Output;

use Illuminate\Bus\Queueable;

class LaravelQueue implements IOutput
{
    private $queue = false;
    private $connection = null;
    private $jobCreator = null;

    public function __construct($queue, $connection, $jobCreator)
    {
        $this->queue = $queue;
        $this->connection = $connection;
        $this->jobCreator = $jobCreator;
    }

    public function push($aData)
    {
        foreach ($aData as $data){
            /** @var Queueable $job */
            $job = call_user_func($this->jobCreator, $data);

            dispatch($job->onConnection($this->connection)->onQueue($this->queue));
        }
    }
}