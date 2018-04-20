<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Library\Curl;
use Cache;


//销售额占比
class SendZhenYL extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:sendZhenYL';

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
        $images = Cache::pull('saleImage');
        if (empty($images)) {
            $image = '201804171141177565.jpg';
//            \Log::info('从缓存中没有获取到图片', []);
//            exit;
        }
        $url = "https://pn-activity.oss-cn-shenzhen.aliyuncs.com/market/" . $image;
        $dingdingUrl = config('app.dingZhenYL');
        $dingdingParam = [
            'msgtype' => 'markdown',
            'markdown' => [
                'title' => '实时销售额',
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
