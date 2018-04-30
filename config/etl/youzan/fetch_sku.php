<?php
use \App\EtlRunRecord;
use \App\ETL\ETL;
use App\ETL\Input\YouZanApi;
use \App\ETL\Output\MysqlInsertUpdateWithPdo;
use \App\Services\YouZanService;

$identity = 'etl.youzan.fetch_sku';

return
    [
        'input' => function() use ($sql){
            return new YouZanApi(
                'youzan.items.inventory.get',
                '3.0.0',
                function ($data){
                    return array_get($data, 'items', []);
                });
        },
        'output' => function(){
            $dc = \DB::connection('dc')->getPdo();
            return new MysqlInsertUpdateWithPdo($dc, 'dim_youzan_sku', null, [
                'sold_num', 'modified', 'properties_name_json'
            ]);
        },
        'before' => function (ETL $etl) use ($identity) {
            EtlRunRecord::createOrWake(
                $identity,
                $etl,
                function (EtlRunRecord $record=null, EtlRunRecord $lastRecord=null){
                    $record->params = [
                        'order_by' => 'update_time',
                        'update_time_start' => strtotime('2017-04-01') * 1000,
                        'update_time_end' => strtotime('2018-04-01') * 1000
                    ];
                    $record->marker = 0;

                },
                null
            );
        },
        'transactions' => function(ETL $etl, $items){
            $skuItems = [];
            foreach ($items as $item){
                $detail = YouZanService::getItem($item['item_id']);
                $skus = array_get($detail, 'skus', []);
                $skuItem = array_only($item, ['item_id','title', 'desc']);
                $skuColumns = ['sku_unique_code', 'price', 'created', 'modified', 'item_no', 'sold_num', 'sku_id', 'properties_name_json'];
                if(!empty($skus)){
                    foreach ($skus as $sku){
                        $skuItem = array_merge($skuItem,array_only($sku, $skuColumns));
                    }
                }else{
                    foreach ($skuColumns as $skuColumn){
                        $skuItem[$skuColumn] = '';
                    }
                    $skuItem['created'] = $skuItem['modified'] = '2000-01-01';
                    $skuItem['price'] = $skuItem['sku_id'] = $skuItem['sold_num'] = 0;
                }

                $skuItems[] = $skuItem;
            }

            return $skuItems;
        },
        'after' => function (ETL $etl) use ($identity){
            EtlRunRecord::endOrSleep($identity, $etl, function (EtlRunRecord $record){
                $record->marker = 0;
                $record->state = EtlRunRecord::STATE_RUNNING;

                $timeBegin = min(time() * 1000, $record->params['update_time_start']);
                $timeEnd = min(time() * 1000, $timeBegin + 86400000);
                $record->params['update_time_start'] = $timeBegin;
                $record->params['update_time_end'] = $timeEnd;
            });
        },
        'fail' => function (ETL $etl, \Exception $e) use ($identity) {
            EtlRunRecord::fail($identity, $etl);
        },
        'limit' => 10,
        'upper' => 20
    ];