<?php
namespace App\ETL\Buffer;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Redis;

class LaravelQueueWithRedis implements IBuffer
{
    private $pullStep = 0;
    private $bufferSize;
    private $queueResultBufferSize = 0;
    private $jobCreator = null;
    private $queueDispatch = null;
    private $laravelQueueDispatchName = null;
    private $queueResult = null;

    private $pullInterval = null;

    private $connection = null;

    public function __construct($bufferSize, $pullStep, $pullInterval, $jobCreator, $queueDispatch, $queueResult, $queueResultBufferSize=null, $connection='default')
    {
        $this->bufferSize = $bufferSize;
        $this->pullStep = isset($pullStep) ? $pullStep : $this->bufferSize;

        $this->jobCreator = $jobCreator;
        $this->queueDispatch = $queueDispatch;
        $this->laravelQueueDispatchName = 'queues:' . $queueDispatch;

        $this->pullInterval = $pullInterval;
        $this->queueResult = $queueResult;

        $this->connection = $connection;

        $this->queueResultBufferSize = $queueResultBufferSize ?: $this->bufferSize;
    }

    public function pull()
    {
        $redis = Redis::connection($this->connection);

        $this->pullInterval > 0 && $redis->llen($this->queueResult) < $this->pullStep && sleep($this->pullInterval);

        $data = $redis->lrange($this->queueResult, 0, $this->pullStep-1);
        $redis->ltrim($this->queueResult, count($data), -1);
        return $data;
    }

    public function push($dataArray)
    {
        foreach ($dataArray as $data){
            /** @var Queueable $job */
            $job = call_user_func($this->jobCreator, $data);

            dispatch($job->onConnection('redis')->onQueue($this->queueDispatch));
        }
    }

    public function isFull()
    {
        $redis = Redis::connection($this->connection);
        return $redis->llen($this->laravelQueueDispatchName) >= $this->bufferSize ||
            $redis->llen($this->queueResult) >= $this->queueResultBufferSize
            ;
    }

    public function isComplete()
    {
        $redis = Redis::connection($this->connection);

        return $redis->llen($this->laravelQueueDispatchName) <= 0 && $redis->llen($this->queueResult) <= 0;
    }

    public function clear()
    {
    }
}