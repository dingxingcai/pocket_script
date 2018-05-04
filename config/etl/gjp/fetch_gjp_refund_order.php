<?php

use \App\EtlRunRecord;
use \App\ETL\ETL;
use \App\ETL\Input\PdoWithLaravel;
use \App\ETL\Output\MysqlInsertUpdateWithPdo;
use \App\ETL\Output\CompositeSerially;
use \App\Utility\EtlConstant;

$sql = <<<SQL
select top :limit  *  from 
(select ROW_NUMBER() OVER (ORDER BY b.BillNumberID asc) AS RowNumber,
'GJP_REFUND' as business_type,
b.BillCode as 'business_id',
b.checkTime as 'ts_created',
b.totalmoney as 'price_original',
b.totalinmoney as 'price_payed',
b.ktypeid as 'store_code',
'成功' as 'business_status'
FROM BillIndex b 
WHERE b.BillType = 215 and  b.posttime BETWEEN  :timeBegin and :timeEnd) as A  WHERE 
A.RowNumber > (:offset - 1)
;
SQL;

$identity = EtlConstant::FETCH_GJP_REFUND_ORDER;

return
    [
        'input' => function () use ($sql) {
            return new PdoWithLaravel('gjp', $sql, 1);
        },
        'output' => function () {
            $dc = \DB::connection('dc')->getPdo();

            return new CompositeSerially([
                'order' => new MysqlInsertUpdateWithPdo($dc, 'fact_order',
                    ['oid', 'business_type', 'business_id', 'ts_created', 'business_status','store_code', 'sales_code', 'price_original', 'price_payed'],
                    ['business_status'])
            ], function ($aData) {
                $res = ['order' => []];
                foreach ($aData as $data) {
                    empty($data['sales_code']) && $data['sales_code'] = '';
                    $data['oid'] = 'GJP_REFUND' . $data['business_id'];
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