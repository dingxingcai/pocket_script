<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
        \App\Console\Commands\Upload::class,
        \App\Console\Commands\TestCommand::class,
        \App\Console\Commands\Vip::class,
        \App\Console\Commands\DayOrder::class,
        \App\Console\Commands\TotalOrder::class,
        \App\Console\Commands\SendDingDing::class,
        \App\Console\Commands\Convert::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
//        $schedule->command('command:vip')->dailyAt("14:18");
//        sleep(10);
//        $schedule->command('command:dayOrder')->dailyAt("14:29");
//        sleep(10);
//        $schedule->command('command:totalOrder')->dailyAt("14:29");
        $schedule->command('command:convert')->dailyAt("08:30");   //生成图片
        $schedule->command('command:sendDingDing')->dailyAt("09:00");   //发送图片
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
