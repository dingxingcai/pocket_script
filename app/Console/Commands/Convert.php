<?php

namespace App\Console\Commands;

use App\Library\Curl;
use Illuminate\Console\Command;
use Exception;
use Illuminate\Support\Facades\Storage;
use Log;
use Cache;

class Convert extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:convert';

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

        Cache::pull('dingImage');

        $images = [];
        for ($i = 1; $i < 4; $i++) {
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
                'file' => 'http://md.sylicod.com/chart/#/?code=' . $i,
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
            $fileName = date('YmdHis', time()) . mt_rand(1000, 9999) . '.jpg';
            $images[] = $fileName;
            if ($i === 1) {
                Cache::put('imageDZ', $fileName, 120);
            }
            $return = Storage::put("market/{$fileName}", $ext);
            Log::info('生成图片' . $i, [$return]);
        }

        Cache::put('dingImage', json_encode($images), 120);
    }
}
