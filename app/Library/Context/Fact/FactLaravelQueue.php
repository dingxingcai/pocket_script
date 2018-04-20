<?php
namespace App\Libiary\Context\Fact;

use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;

class FactLaravelQueue extends FactBase
{
    private static $self = null;

    /**
     * @return null|FactLaravelQueue
     */
    public static function instance()
    {
        if(is_null(self::$self)){
            self::$self = new self();
        }

        return self::$self;
    }

    /**
     * 获取名称
     * @return string
     */
    public function name()
    {
        return "fact_laravel_queue";
    }

    /**
     * 记录处理完毕的job
     * @param JobProcessed $event
     */
    public function recordAfter(JobProcessed $event)
    {
        $this->record(
            [
                'connection' => $event->connectionName,
                'payload' => $event->job->getRawBody(),
                'name' => $event->job->getName(),
                'queue' => $event->job->getQueue(),
                'event' => 'after'
            ]
        );
    }

    /**
     * 记录失败的job
     * @param JobFailed $event
     */
    public function recordFailed(JobFailed $event)
    {
        $this->record(
            [
                'connection' => $event->connectionName,
                'payload' => $event->job->getRawBody(),
                'name' => $event->job->getName(),
                'queue' => $event->job->getQueue(),
                'event' => 'failed',
                'code' => $event->exception->getCode(),
                'msg' => $event->exception->getMessage(),
                'file' => $event->exception->getFile(),
                'line' => $event->exception->getLine()
            ]
        );
    }
}