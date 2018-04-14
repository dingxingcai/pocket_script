<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Library\Curl;
use Cache;


//会员体系群
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
        $images = Cache::pull('dingImage');
        if (empty($images)) {
            \Log::info('从缓存中没有获取到图片');
            exit;
        }
        foreach (json_decode($images, true) as $image) {
            $url = "https://pn-activity.oss-cn-shenzhen.aliyuncs.com/market/" . $image;
            $dingdingUrl = config('app.dingdingUrl');
            $dingdingParam = [
                'msgtype' => 'markdown',
                'markdown' => [
                    'title' => '订单和会员统计',
                    'text' => "![screenshot]({$url})"
                ],
                'at' => [
                    'atMobiles' => [''],
                    'isAtAll' => false,
                ]


            ];

            $result3 = Curl::curl($dingdingUrl, json_encode($dingdingParam), true, true, true);
            \Log::info('测试发送图片' . $image, $result3);
        }
    }
}
