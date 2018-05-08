<?php

use \App\EtlRunRecord;
use \App\ETL\ETL;
use \App\ETL\Input\PdoWithLaravel;
use \App\ETL\Output\MysqlInsertUpdateWithPdo;
use \App\ETL\Output\CompositeSerially;
use \App\Utility\EtlConstant;


$sql = <<<SQL
select top :limit * from 
(select 
ROW_NUMBER() OVER (ORDER BY v.VipCardID asc) AS RowNumber,
v.VipCardID,
v.VipCardCode,
v.VipCardTypeID as 'Type',
v.CreateDate,
v.totalMoney,
v.totalCent
from nVipCardSign v
where  v.CreateDate between :timeBegin and :timeEnd) as  A
where A.RowNumber > (:offset -1);
SQL;

$identity = EtlConstant::FETCH_VIP_FROM_GJP;

return
    [
        'input' => function () use ($sql) {
            return new PdoWithLaravel('gjp', $sql, 1);
        },
        'output' => function () {
            $dc = \DB::connection('dc')->getPdo();

            return new CompositeSerially([
                'vip' => new MysqlInsertUpdateWithPdo($dc, 'dim_vip',
                    ['VipCardID', 'VipCardCode', 'Type', 'CreateDate', 'totalMoney', 'totalCent'],
                    ['VipCardCode', 'totalMoney', 'totalCent'])
            ], function ($aData) {
                $res = ['vip' => []];
                foreach ($aData as $data) {
                    $res['vip'][] = $data;
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
                        'timeEnd' => '2018-05-08 13:40:00'
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
                $timeEnd = strtotime('+5 minute', $timeBegin);

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