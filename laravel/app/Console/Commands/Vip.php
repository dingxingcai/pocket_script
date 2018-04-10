<?php

namespace App\Console\Commands;

use App\Library\Curl;
use Illuminate\Console\Command;
use Exception;
use Illuminate\Support\Facades\Storage;
use Log;


class Vip extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:vip';

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
            'apikey' => 'BZ8NOi8NKDxEqVJJEtC3pswwMuO2SQC3rIbFksvzuCUQM4f3KmlXV_j0tfXkYaGw1y3dJ1dnitkX3TzlR4V-kg',
            'inputformat' => 'website',
            'outputformat' => 'jpg'
        ];
        $result1 = Curl::curl($url1, $param1, true, true);
        if ($result1 === false) {
            throw new Exception('获取URL错误');
        }

        $url2 = $result1['url'];
        $param2 = [
            'wait' => true,
            'input' => 'url',
            'file' => 'http://md.sylicod.com/chart/#/?code=2',
            'filename' => 'test.website',
            'outputformat' => 'jpg'
        ];

        $result2 = Curl::curl('https:' . $url2, $param2, true, true);
        if ($result2 === false) {
            throw new Exception('获取图片地址错误');
        }
        $url3 = $result2['output'];
        $imageUrl = 'https:' . $url3['url'];
        $ext = file_get_contents($imageUrl);
        $fileName = date('YmdHis', time()) . '.jpg';
        Storage::put("market/{$fileName}", $ext);
        $url = Storage::url("market/{$fileName}");
        $dingdingUrl = 'https://oapi.dingtalk.com/robot/send?access_token=300b7306d68e52ad00766e3813e21218b9d97aa11ed5bf7eb0ec72408080afc5';
        $dingdingParam = [
            'msgtype' => 'markdown',
            'markdown' => [
                'title' => '一周内新增会员数量',
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
