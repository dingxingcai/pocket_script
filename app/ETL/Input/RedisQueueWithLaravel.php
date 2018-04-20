<?php
namespace App\ETL\Input;

use Illuminate\Support\Facades\Redis;

class RedisQueueWithLaravel implements IInput
{
    private $connection = null;
    private $queue = null;

    public function __construct($connection, $queue)
    {
        $this->connection = $connection;
        $this->queue = $queue;
    }

    public function pull($limit, $params)
    {
        $redis = Redis::connection($this->connection);

        $data = $redis->lrange($this->queue, 0, $limit-1);
        $redis->ltrim($this->queue, count($data), -1);
        return $data;
    }

    public function getOffset()
    {
        // TODO: Implement getOffset() method.
    }

    public function setOffset($offset)
    {
        // TODO: Implement setOffset() method.
    }
}