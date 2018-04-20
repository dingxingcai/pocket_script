<?php

namespace App\Listeners;

use App\Events\Event;
use App\Events\TestEvent;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Redis;

class SqlListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  Event  $event
     * @return void
     */
    public function handle(QueryExecuted $event)
    {
        \Log::info($event->sql, $event->bindings);
    }
}
