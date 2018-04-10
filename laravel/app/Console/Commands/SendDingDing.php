<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Library\Curl;
use Cache;
class SendDingDing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:sendDingDing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
//        $images = ['201804101410405227.jpg', '201804101419424513.jpg', '201804101420223137.jpg', '201804101430072794.jpg'];
        $images = Cache::get('dingImage');
        foreach (json_decode($images,true) as $image) {
            $url = "https://pn-activity.oss-cn-shenzhen.aliyuncs.com/market/".$image;
            $dingdingUrl = config('app.dingdingUrl');
            $dingdingParam = [
                'msgtype' => 'markdown',
                'markdown' => [
                    'title' => '',
                    'text' => "![screenshot]({$url})"
                ],
                'at' => [
                    'atMobiles' => [''],
                    'isAtAll' => false,
                ]


            ];

            $result3 = Curl::curl($dingdingUrl, json_encode($dingdingParam), true, true, true);
            \Log::info('测试发送图片',$result3);
        }
    }
}
