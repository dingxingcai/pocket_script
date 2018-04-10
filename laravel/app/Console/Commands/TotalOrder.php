<?php

namespace App\Console\Commands;

use App\Library\Curl;
use Illuminate\Console\Command;
use Exception;
use Illuminate\Support\Facades\Storage;
use Log;

class TotalOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:totalOrder';

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
        $url1 = 'https://api.cloudconvert.com/process';
        $param1 = [
            'apikey' => config('app.apikey'),
            'inputformat' => 'website',
            'outputformat' => 'jpg'
        ];
        $result1 = Curl::curl($url1, $param1, true, true);
        if ($result1 === false || !isset($result1['url'])) {
            throw new Exception('获取URL错误');
        }

        $url2 = $result1['url'];
        $param2 = [
            'wait' => true,
            'input' => 'url',
            'file' => 'http://md.sylicod.com/chart/#/?code=3',
            'filename' => 'test.website',
            'outputformat' => 'jpg'
        ];

        $result2 = Curl::curl('https:' . $url2, $param2, true, true);
        if ($result2 === false || !isset($result2['output'])) {
            throw new Exception('获取图片地址错误');
        }
        $url3 = $result2['output'];
        $imageUrl = 'https:' . $url3['url'];
        $ext = file_get_contents($imageUrl);
        $fileName = date('YmdHis', time()) . '.jpg';
        Storage::put("market/{$fileName}", $ext);
        $url = Storage::url("market/{$fileName}");
        if (empty($url)) {
            throw new Exception('获取的阿里云图片地址为空');
        }
        $dingdingUrl = config('app.dingdingUrl');
        $dingdingParam = [
            'msgtype' => 'markdown',
            'markdown' => [
                'title' => '一周门店总单数',
                'text' => "![screenshot]({$url})"
            ],
            'at' => [
                'atMobiles' => [''],
                'isAtAll' => false,
            ]


        ];

        $result3 = Curl::curl($dingdingUrl, json_encode($dingdingParam), true, true, true);
        if ($result3['errcode'] != 0) {
            Log::info('vipOrder', ['会员数量发送钉钉失败']);
        }
    }
}
