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
ROW_NUMBER() OVER (ORDER BY p.typeId asc) AS RowNumber,
p.typeId as 'sku_id',
p.UserCode as 'sku_code',
p.CreateDate as 'ts_created',
p.FullName as 'name',
c.Costom2 as 'category',
c.Costom10 as 'brand',
c.Costom1 as 'spu_name',
c.Costom3 as 'supplier_settlement_mode',
p.leveal 
from ptype p left join Ptype_CustomColumns c
on p.typeId = c.typeid
where p.leveal > 1 and p.CreateDate between :timeBegin and :timeEnd) as  A
where A.RowNumber > (:offset -1);
SQL;

$identity = EtlConstant::FETCH_GOODS_FROM_GJP;

return
    [
        'input' => function () use ($sql) {
            return new PdoWithLaravel('gjp', $sql, 1);
        },
        'output' => function () {
            $dc = \DB::connection('dc')->getPdo();

            return new CompositeSerially([
                'sku' => new MysqlInsertUpdateWithPdo($dc, 'dim_sku',
                    ['sku_code', 'from', 'sku_id', 'ts_created', 'name', 'category', 'brand', 'spu_name', 'supplier_settlement_mode'],
                    ['name', 'supplier_settlement_mode'])
            ], function ($aData) {
                $res = ['sku' => []];
                foreach ($aData as $data) {
                    if (empty($data['supplier_settlement_mode'])) {
                        $data['supplier_settlement_mode'] = 'A';
                    }
                    $data['from'] = 'GJP';
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
                        'timeEnd' => '2018-04-25 12:00:00'
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