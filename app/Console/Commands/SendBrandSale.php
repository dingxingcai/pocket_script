<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Library\Curl;
use Cache;


//销售额占比
class SendBrandSale extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:sendBrandSale';

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
            \Log::info('从缓存中没有获取到图片', []);
            exit;
        }
        foreach (json_decode($images, true) as $image) {
            $url = "https://pn-activity.oss-cn-shenzhen.aliyuncs.com/market/" . $image;
            $dingdingUrl = config('app.dingBrandSale');
            $dingdingParam = [
                'msgtype' => 'markdown',
                'markdown' => [
                    'title' => '品类销售占比',
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
