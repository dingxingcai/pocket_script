<?php

use \App\EtlRunRecord;
use \App\ETL\ETL;
use \App\ETL\Input\PdoWithLaravel;
use \App\ETL\Output\MysqlInsertUpdateWithPdo;
use \App\ETL\Output\CompositeSerially;
use \App\Utility\EtlConstant;


$sql = <<<SQL
SELECT 
id as 'sku_id',
name,
code as 'sku_code',
brand as 'brand',
category ,
createdAt as 'ts_created'
FROM  goods
where updatedAt BETWEEN :timeBegin AND :timeEnd LIMIT :limit offset :offset
;
SQL;

$identity = EtlConstant::FETCH_GOODS_FROM_ZL;

return
    [
        'input' => function () use ($sql) {
            return new PdoWithLaravel('zulin', $sql, 0);
        },
        'output' => function () {
            $dc = \DB::connection('dc')->getPdo();

            return new CompositeSerially([
                'sku' => new MysqlInsertUpdateWithPdo($dc, 'dim_sku',
                    ['sku_code', 'from', 'sku_id', 'ts_created', 'name', 'category', 'brand', 'spu_name', 'supplier_settlement_mode'],
                    ['name'])
            ], function ($aData) {
                $res = ['sku' => []];
                foreach ($aData as $data) {
                    if(empty($data['spu_name'])){
                        $data['spu_name'] = '';
                    }
                    $data['supplier_settlement_mode'] = 'A';
                    $data['from'] = 'ZL';
                    $res['sku'][] = $data;
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
                        'timeBegin' => '2018-01-01 00:00:00',
                        'timeEnd' => '2018-04-28 12:00:00'
                    ];
                    $record->marker = 1;

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
        'upper' => 300000
    ];