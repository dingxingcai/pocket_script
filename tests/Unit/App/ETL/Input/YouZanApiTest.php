<?php
namespace Tests\Unit\App\ETL\Input;

use App\ETL\Input\YouZanApi;
use Tests\TestCase;

class YouZanApiTest extends TestCase
{
    public function testPullItemsInventoryGet()
    {
        $input = new YouZanApi(
            'youzan.items.inventory.get',
            '3.0.0',
            function ($data){
                return array_get($data, 'items', []);
        });

        $result = $input->pull(1, [
            'order_by' => 'update_time',
            'update_time_start' => strtotime('2017-04-01') * 1000,
            'update_time_end' => strtotime('2018-04-01') * 1000
        ]);

//        print_r($result[0]['item_id']);
        print_r($result[0]);

        $result = $input->pull(1, [
            'order_by' => 'update_time',
            'update_time_start' => strtotime('2017-04-01') * 1000,
            'update_time_end' => strtotime('2018-04-01') * 1000
        ]);

//        print_r($result[0]['item_id']);

        print_r($result[0]);
    }
}