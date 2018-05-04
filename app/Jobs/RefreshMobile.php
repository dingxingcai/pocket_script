<?php

namespace App\Jobs;

use App\Library\CurlWrapper;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Redis;

class RefreshMobile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */


    private $value;

    public function __construct($value)
    {

        $this->value = $value;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $mobile = $this->value;
        $headers = ["Authorization:APPCODE " . 'cf62d9ad41674e9daaa615bbedbf5f85'];
        $res = CurlWrapper::get(
            ['num' => $mobile],
            'https://ali-mobile.showapi.com/6-1',
            30,
            $headers
        );

        $a = $res;
        $aa = json_decode($a, true);


        $insert = $aa['showapi_res_body'];
//            $insert['mobile'] = $mobile;
//            $writer->push([$insert]);
        \DB::table('mobile_dc_20180427')->where('mobile', '=', $mobile)->update($insert);
    }
}
