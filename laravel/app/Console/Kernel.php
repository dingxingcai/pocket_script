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
        \App\Console\Commands\BrandSale::class,
        \App\Console\Commands\SendDingDing::class,
        \App\Console\Commands\Convert::class,
        \App\Console\Commands\SendBrandSale::class,
        \App\Console\Commands\ZhenYL::class,
        \App\Console\Commands\SendZhenYL::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('command:convert')->dailyAt("08:35");   //生成图片(会员体系)
        $schedule->command('command:sendDingDing')->dailyAt("09:10");   //发送图片(会员体系群)
        $schedule->command('command:dayOrder')->dailyAt("09:11");   //发送图片(店长群)

        $schedule->command('command:brandSale')->dailyAt("22:55");   //生成图片(销售占比图片)
        $schedule->command('command:sendBrandSale')->dailyAt("23:00");   //发送图片(销售额占比，发销售额占比群)


        $schedule->command('command:TestCommand')->everyMinute();
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
