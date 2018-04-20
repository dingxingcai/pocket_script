<?php
namespace App\Libiary\Context\Fact;

use \Illuminate\Contracts\Queue\Job;

class FactSequenceQueueCompleted extends FactBase
{
    private static $self = null;

    /**
     * @return null|FactSequenceQueueCompleted
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
        return "fact_sequence_queue_completed";
    }

    /**
     * @param Job $job
     */
    public function recordJob(Job $job)
    {
        $this->record(
            [
                'payload' => $job->getRawBody(),
                'name' => $job->getName(),
                'queue' => $job->getQueue(),
            ]
        );
    }
}