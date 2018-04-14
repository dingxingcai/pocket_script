<?php

namespace App\Console\Commands;

use App\Library\Curl;
use Illuminate\Console\Command;
use Exception;
use Illuminate\Support\Facades\Storage;
use Log;
use Cache;


//店长群
class DayOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:dayOrder';

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

        $image = Cache::pull('imageDZ');
        if (empty($image)) {
            Log::info('发送店长群图片获取为空', []);
        }

        $url = "https://pn-activity.oss-cn-shenzhen.aliyuncs.com/market/" . $image;
        $dingdingUrl = config('app.dingdingUrlDZ');
        $dingdingParam = [
            'msgtype' => 'markdown',
            'markdown' => [
                'title' => '门店当日销量统计',
                'text' => "![screenshot]({$url})"
            ],
            'at' => [
                'atMobiles' => [''],
                'isAtAll' => false,
            ]


        ];

        $result3 = Curl::curl($dingdingUrl, json_encode($dingdingParam), true, true, true);
        Log::info('发送店长群图片失败' . $image, $result3);
    }
}
