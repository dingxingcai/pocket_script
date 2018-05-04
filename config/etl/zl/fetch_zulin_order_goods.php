<?php

use \App\EtlRunRecord;
use \App\ETL\ETL;
use \App\ETL\Input\PdoWithLaravel;
use \App\ETL\Output\MysqlInsertUpdateWithPdo;
use \App\ETL\Output\CompositeSerially;
use \App\Utility\EtlConstant;

$sql = <<<SQL
select 
o.orderNo,
o.rentTotal,
g.`code` as 'sku_code',
o.discountAmount 
from `order` o 
left join goods g on g.id = o.goodsId
 where o.updatedAt between :timeBegin and :timeEnd limit :limit offset :offset; 
SQL;

$identity = EtlConstant::FETCH_ZULIN_ORDER_GOODS;

return
    [
        'input' => function () use ($sql) {
            return new PdoWithLaravel('zulin', $sql);
        },
        'output' => function () {
            $dc = \DB::connection('dc')->getPdo();

            return new CompositeSerially([
                'order' => new MysqlInsertUpdateWithPdo($dc, 'fact_order_sku',
                    ['oid', 'business_type', 'sku_code', 'quantity', 'price_actual', 'price_original', 'price_payed'],
                    ['price_actual', 'price_original', 'price_payed']

                ),
            ], function ($aData) {
                $res = ['order' => []];
                foreach ($aData as $data) {
                    $data['oid'] = 'ZL' . $data['orderNo'];
                    $data['business_type'] = 'ZL';
                    $data['quantity'] = 1;
                    $data['price_original'] = $data['rentTotal'];
                    $data['price_actual'] = $data['rentTotal'] - $data['discountAmount'];
                    $data['price_payed'] = $data['rentTotal'] - $data['discountAmount'];

                    $res['order'][] = $data;
                }
                return $res;
            });
        },
        'before' => function (ETL $etl) use ($identity) {
            EtlRunRecord::createOrWake(
                $identity,
                $etl,
                function (EtlRunRecord $record = null, EtlRunRecord $lastRecord = null) {
                    $record->params = [
                        'timeBegin' => '2018-04-01',
                        'timeEnd' => '2018-04-25'
                    ];
                    $record->marker = 0;

                },
                null
            );
        },
        'after' => function (ETL $etl) use ($identity) {
            EtlRunRecord::endOrSleep($identity, $etl, function (EtlRunRecord $record) {
                $record->marker = 0;

                $record->state = EtlRunRecord::STATE_RUNNING;

                $timeBegin = min(time(), strtotime($record->params['timeEnd']));
                $timeEnd = min(time(), strtotime('+1 day', $timeBegin));

                $record->params = [
                    'timeBegin' => date('Y-m-d H:i:s', $timeBegin),
                    'timeEnd' => date('Y-m-d H:i:s', $timeEnd)
                ];
            });
        },
        'fail' => function (ETL $etl, \Exception $e) use ($identity) {
            EtlRunRecord::fail($identity, $etl);
        },
        'limit' => 30,
        'upper' => 30000
    ];